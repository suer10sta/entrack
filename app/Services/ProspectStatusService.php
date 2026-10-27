<?php

namespace App\Services;

use App\Enums\ProspectStatus;
use App\Models\CrmLifecycleEvent;
use App\Models\Manager;
use App\Models\Prospect;
use App\Models\ProspectStatusHistory;
use DateTimeInterface;
use DomainException;
use Illuminate\Support\Carbon;
use InvalidArgumentException;

class ProspectStatusService
{
    public function transition(Prospect $prospect, ProspectStatus $target, string $source = 'manual', ?Manager $manager = null,
        ?string $note = null, ?string $idempotencyKey = null, ?DateTimeInterface $occurredAt = null): Prospect
    {
        if (! $prospect->exists || ! preg_match('/^[a-z][a-z0-9_.-]{0,63}$/', $source)) {
            throw new InvalidArgumentException('A persisted prospect and valid source are required.');
        }
        if ($source === 'manual' && (! $manager?->exists || ! $manager->getKey())) {
            throw new InvalidArgumentException('A persisted Manager is required for manual changes.');
        }
        if ($idempotencyKey !== null && (trim($idempotencyKey) === '' || mb_strlen($idempotencyKey) > 191)) {
            throw new InvalidArgumentException('Invalid idempotency key.');
        }

        $connection = $prospect->getConnection();

        return $connection->transaction(function () use ($prospect, $target, $source, $manager, $note, $idempotencyKey, $occurredAt) {
            $name = $prospect->getConnectionName();
            $locked = Prospect::on($name)->whereKey($prospect->getKey())->lockForUpdate()->firstOrFail();
            $at = $occurredAt ? Carbon::instance($occurredAt)->utc() : now('UTC');
            $receipt = null;
            if ($idempotencyKey !== null) {
                // A durable receipt also covers no-op transitions; replay must never reapply an old status.
                CrmLifecycleEvent::on($name)->insertOrIgnore(['source' => $source, 'event_key' => $idempotencyKey,
                    'event_type' => 'prospect.status_transition', 'prospect_id' => $locked->id,
                    'payload' => json_encode(['target_status' => $target->value]), 'occurred_at' => $at, 'created_at' => now(), 'updated_at' => now()]);
                $receipt = CrmLifecycleEvent::on($name)->where('source', $source)->where('event_key', $idempotencyKey)->lockForUpdate()->firstOrFail();
                if ((int) $receipt->prospect_id !== (int) $locked->id || $receipt->event_type !== 'prospect.status_transition'
                    || ($receipt->payload['target_status'] ?? null) !== $target->value) {
                    throw new DomainException('Idempotency key already used for a different transition.');
                }
                if ($receipt->processed_at !== null) {
                    return $locked;
                }
            }
            $from = $locked->status;
            if ($source !== 'manual' && ! $from->allowsAutomaticTransitionTo($target)) {
                throw new DomainException('Automatic status regression, loss or reopening is forbidden.');
            }
            if ($from !== $target) {
                $locked->applyStatusTransition($target, $at);
                ProspectStatusHistory::on($name)->create(['prospect_id' => $locked->id, 'from_status' => $from,
                    'to_status' => $target, 'occurred_at' => $at, 'source' => $source, 'manager_id' => $manager?->getKey(),
                    'note' => $note, 'idempotency_key' => $idempotencyKey, 'lifecycle_event_id' => $receipt?->id]);
            }
            if ($receipt) {
                $receipt->update(['processed_at' => now('UTC')]);
            }

            return $locked;
        }, 3);
    }
}
