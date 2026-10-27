<?php

namespace App\Services;

use App\Enums\ProspectActivityType;
use App\Enums\ProspectStatus;
use App\Models\CrmLifecycleEvent;
use App\Models\Entreprise;
use App\Models\Prospect;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Throwable;

class CrmSubscriptionSync
{
    public function __construct(private ProspectStatusService $statuses) {}

    /** Only called with the server-retrieved Checkout Session after enterprise activation. */
    public function paid(Entreprise $entreprise, User $user, $session): void
    {
        $event = null;
        try {
            $sessionId = $session->id ?? null;
            $metadata = $session->metadata ?? [];
            // Defence in depth: a URL parameter or an activated local plan alone is never proof.
            if (! is_string($sessionId) || ! preg_match('/^cs_[a-zA-Z0-9_]+$/', $sessionId)
                || ($session->payment_status ?? null) !== 'paid'
                || ! ($metadata instanceof \ArrayAccess || is_array($metadata))
                || (string) ($metadata['entreprise_id'] ?? '') !== (string) $entreprise->id
                || (string) ($metadata['plan_name'] ?? '') !== (string) $entreprise->type_compt
                || (int) $user->entreprise_id !== (int) $entreprise->id) {
                Log::warning('CRM ignored an unconfirmed or inconsistent checkout.', ['entreprise_id' => $entreprise->id]);

                return;
            }
            $name = $entreprise->getConnectionName();
            $key = 'checkout:'.$sessionId;
            $payload = ['state' => 'pending', 'checkout_session_id' => $sessionId,
                'subscription_id' => $this->stripeId($session->subscription ?? null),
                'invoice_id' => $this->stripeId($session->invoice ?? null),
                'plan' => $entreprise->type_compt, 'amount_total' => $session->amount_total ?? null,
                'currency' => $session->currency ?? null];
            // Persist the receipt before the business transaction so failures remain diagnosable/retryable.
            CrmLifecycleEvent::on($name)->insertOrIgnore(['source' => 'stripe', 'event_key' => $key,
                'event_type' => 'subscription_paid', 'user_id' => $user->id, 'entreprise_id' => $entreprise->id,
                'payload' => json_encode($payload), 'occurred_at' => now('UTC')->toISOString(), 'created_at' => now('UTC')->toISOString(), 'updated_at' => now('UTC')->toISOString()]);
            $event = CrmLifecycleEvent::on($name)->where('source', 'stripe')->where('event_key', $key)->firstOrFail();
            $entreprise->getConnection()->transaction(function () use ($entreprise, $user, $metadata, $name, $event) {
                if ($entreprise->getConnection()->getDriverName() === 'pgsql') {
                    $entreprise->getConnection()->statement("SET LOCAL TIME ZONE 'UTC'");
                }
                // Serialize all payments for this enterprise, including different Checkout Sessions.
                Entreprise::on($name)->whereKey($entreprise->id)->lockForUpdate()->firstOrFail();
                $event = CrmLifecycleEvent::on($name)->whereKey($event->id)->lockForUpdate()->firstOrFail();
                if ((int) $event->entreprise_id !== (int) $entreprise->id || $event->event_type !== 'subscription_paid') {
                    Log::warning('CRM checkout receipt identity conflict.', ['lifecycle_event_id' => $event->id]);

                    return;
                }
                if ($event->processed_at !== null) {
                    return;
                }
                $account = User::on($name)->find($user->id);
                if (! $account || (int) $account->entreprise_id !== (int) $entreprise->id
                    || (isset($metadata['user_id']) && (string) $metadata['user_id'] !== (string) $user->id)) {
                    $this->finish($event, 'conflict', ['reason' => 'checkout_user_mismatch']);

                    return;
                }
                $prospects = Prospect::on($name)->where('linked_entreprise_id', $entreprise->id)
                    ->orderBy('id')->limit(3)->lockForUpdate()->get();
                if ($prospects->isEmpty()) {
                    $this->finish($event, 'no_match');

                    return;
                }
                if ($prospects->count() !== 1) {
                    $this->finish($event, 'conflict', ['reason' => 'multiple_linked_prospects', 'prospect_ids' => $prospects->modelKeys()]);

                    return;
                }
                $prospect = $prospects->first();
                $event->prospect_id = $prospect->id;
                if ($prospect->status === ProspectStatus::Client || $prospect->status === ProspectStatus::Perdu) {
                    $this->finish($event, $prospect->status === ProspectStatus::Client ? 'already_client' : 'lost_preserved');

                    return;
                }
                if (! $prospect->status->allowsAutomaticTransitionTo(ProspectStatus::Client)) {
                    $this->finish($event, 'conflict', ['reason' => 'incompatible_status', 'status' => $prospect->status->value]);

                    return;
                }
                $from = $prospect->status;
                $at = $event->occurred_at;
                $this->statuses->transition($prospect, ProspectStatus::Client, 'stripe',
                    note: 'Abonnement DalyTrac payé — paiement Stripe confirmé côté serveur.',
                    idempotencyKey: $event->event_key.':client', occurredAt: $at);
                $prospect->forceFill(['first_paid_at' => $prospect->first_paid_at ?? $at])->saveOrFail();
                $prospect->activities()->create(['type' => ProspectActivityType::Automatic, 'subject' => 'Abonnement DalyTrac payé',
                    'body' => 'Premier passage en Client après confirmation serveur du paiement Stripe.', 'occurred_at' => $at,
                    'metadata' => ['source' => 'stripe', 'lifecycle_event_id' => $event->id, 'checkout_session_id' => $event->payload['checkout_session_id']]]);
                $this->finish($event, 'converted', ['from_status' => $from->value, 'historical_status' => $from !== ProspectStatus::DemoEssai]);
                if ($from !== ProspectStatus::DemoEssai) {
                    Log::warning('CRM paid conversion from an earlier pipeline status allowed by central rules.',
                        ['lifecycle_event_id' => $event->id, 'prospect_id' => $prospect->id, 'from_status' => $from->value]);
                }
            }, 3);
        } catch (Throwable $error) {
            Log::error('CRM paid subscription synchronization failed; payment remains activated.',
                ['entreprise_id' => $entreprise->id, 'user_id' => $user->id, 'lifecycle_event_id' => $event?->id, 'exception' => $error]);
            if ($event) {
                try {
                    $event->getConnection()->transaction(function () use ($event, $error) {
                        $receipt = CrmLifecycleEvent::on($event->getConnectionName())->whereKey($event->id)->lockForUpdate()->first();
                        if ($receipt && ! $receipt->processed_at) {
                            $receipt->update(['payload' => array_merge($receipt->payload, ['state' => 'failed', 'error_class' => get_class($error)])]);
                        }
                    });
                } catch (Throwable $diagnosticError) {
                    Log::error('CRM payment diagnostic persistence failed.', ['entreprise_id' => $entreprise->id, 'exception' => $diagnosticError]);
                }
            }
        }
    }

    private function stripeId($value): ?string
    {
        return is_string($value) ? $value : ($value->id ?? null);
    }

    private function finish(CrmLifecycleEvent $event, string $state, array $details = []): void
    {
        $event->payload = array_merge($event->payload, $details, ['state' => $state]);
        $event->processed_at = now('UTC');
        $event->saveOrFail();
        if ($state === 'conflict') {
            Log::warning('CRM paid subscription requires manual resolution.', ['lifecycle_event_id' => $event->id, 'entreprise_id' => $event->entreprise_id] + $details);
        }
    }
}
