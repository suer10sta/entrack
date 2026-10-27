<?php

namespace App\Services;

use App\Enums\ProspectActivityType;
use App\Enums\ProspectStatus;
use App\Models\CrmLifecycleEvent;
use App\Models\Prospect;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Throwable;

class CrmRegistrationSync
{
    private const SOURCE = 'registration';

    private const TYPE = 'account.registered';

    public function __construct(private ProspectStatusService $statuses) {}

    /** Called only after the real Register business writes have succeeded. */
    public function registered(User $user, ?array $referral = null): void
    {
        try {
            $user->getConnection()->transaction(function () use ($user, $referral) {
                $this->useUtcForTransaction($user);
                $user = User::on($user->getConnectionName())->findOrFail($user->id);
                $enterprise = $user->entreprise;
                $email = Prospect::normalizeEmail($user->email);
                $candidates = Prospect::on($user->getConnectionName())->where('email_normalized', $email)->limit(3)->pluck('id')->all();
                // Persist even a no-match registration, but never create a Prospect.
                // Only this Register marker makes a future Verified event eligible.
                CrmLifecycleEvent::on($user->getConnectionName())->insertOrIgnore([
                    'source' => self::SOURCE, 'event_key' => $this->key($user), 'event_type' => self::TYPE,
                    'user_id' => $user->id, 'entreprise_id' => $user->entreprise_id,
                    'occurred_at' => $user->created_at, 'created_at' => now('UTC'), 'updated_at' => now('UTC'),
                    'payload' => json_encode(['state' => 'awaiting_verification', 'email_normalized' => $email,
                        // Unverified provenance only; never use this to choose or link a Prospect.
                        'referral' => $referral,
                        'candidate_ids_at_registration' => $candidates,
                        // The company and its trial are created together by Register.
                        // Do not infer this from verification time or change trial_ends_at.
                        'trial_started_at' => $enterprise?->trial_ends_at ? $enterprise->created_at?->toISOString() : null]),
                ]);
            }, 3);
        } catch (Throwable $error) {
            $this->failure($user, 'register', $error);
        }
    }

    /** Safe to replay for an eligible registration; never backfills old accounts. */
    public function verified(User $user): void
    {
        try {
            $user->getConnection()->transaction(function () use ($user) {
                $this->useUtcForTransaction($user);
                $name = $user->getConnectionName();
                $user = User::on($name)->whereKey($user->id)->lockForUpdate()->firstOrFail();
                if (! $user->hasVerifiedEmail()) {
                    return;
                }
                $event = CrmLifecycleEvent::on($name)->where('source', self::SOURCE)->where('event_key', $this->key($user))->lockForUpdate()->first();
                if (! $event || $event->event_type !== self::TYPE || $event->processed_at !== null) {
                    return;
                }
                $payload = $event->payload;
                $email = Prospect::normalizeEmail($user->email);
                if ($event->user_id !== $user->id || ! $user->entreprise_id || $event->entreprise_id !== $user->entreprise_id
                    || ($payload['email_normalized'] ?? null) !== $email || ! $email) {
                    $this->finish($event, 'conflict', ['reason' => 'account_identity_changed']);

                    return;
                }
                if (! $user->entreprise || empty($payload['trial_started_at'])) {
                    $this->finish($event, 'conflict', ['reason' => 'registration_trial_unavailable']);

                    return;
                }
                // Serialize different new users attempting to claim the same email.
                // Manager-created duplicates remain legal, and are refused below.
                if ($user->getConnection()->getDriverName() === 'pgsql') {
                    $user->getConnection()->select('SELECT pg_advisory_xact_lock(hashtextextended(?, 0))', ['crm.registration:'.$email]);
                }
                $userIds = User::on($name)->whereRaw('LOWER(BTRIM(email, ?)) = ?', [" \t\n\r\x0B", $email])->limit(3)->pluck('id')->all();
                if (count($userIds) !== 1) {
                    $this->finish($event, 'conflict', ['reason' => 'duplicate_user_email', 'conflicting_user_ids' => $userIds]);

                    return;
                }
                // Include linked matches when checking ambiguity: never select the first.
                $prospects = Prospect::on($name)->where('email_normalized', $email)->orderBy('id')->limit(3)->lockForUpdate()->get();
                if ($prospects->isEmpty()) {
                    $this->finish($event, 'no_match');

                    return;
                }
                if ($prospects->count() !== 1) {
                    $this->finish($event, 'conflict', ['reason' => 'duplicate_prospect_email', 'conflicting_prospect_ids' => $prospects->modelKeys()]);

                    return;
                }
                $prospect = $prospects->first();
                $sameAccount = $prospect->linked_user_id === $user->id && $prospect->linked_entreprise_id === $user->entreprise_id;
                if (($prospect->linked_user_id !== null || $prospect->linked_entreprise_id !== null) && ! $sameAccount) {
                    $this->finish($event, 'conflict', ['reason' => 'prospect_already_linked', 'conflicting_prospect_ids' => [$prospect->id]]);

                    return;
                }
                $confirmedAt = $user->email_verified_at;
                $prospect->forceFill([
                    'linked_user_id' => $user->id, 'linked_entreprise_id' => $user->entreprise_id,
                    'linked_at' => $prospect->linked_at ?? $confirmedAt,
                    'trial_started_at' => $prospect->trial_started_at ?? $payload['trial_started_at'],
                ])->saveOrFail();
                // Client and Perdu can be linked, but their status is never changed.
                if ($prospect->status->allowsAutomaticTransitionTo(ProspectStatus::DemoEssai)) {
                    $this->statuses->transition($prospect, ProspectStatus::DemoEssai, self::SOURCE,
                        note: 'Compte DalyTrac créé et email vérifié.', idempotencyKey: 'user:'.$user->id.':demo_essai', occurredAt: $confirmedAt);
                }
                $prospect->activities()->create([
                    'type' => ProspectActivityType::Automatic, 'subject' => 'Compte DalyTrac créé / email vérifié',
                    'body' => 'Rattachement confirmé au compte DalyTrac après vérification de son adresse email.',
                    'occurred_at' => $confirmedAt, 'metadata' => ['source' => self::SOURCE, 'lifecycle_event_id' => $event->id,
                        'user_id' => $user->id, 'entreprise_id' => $user->entreprise_id],
                ]);
                $event->prospect_id = $prospect->id;
                $this->finish($event, 'linked', ['status_preserved' => in_array($prospect->status, [ProspectStatus::Client, ProspectStatus::Perdu], true)]);
            }, 3);
        } catch (Throwable $error) {
            $this->failure($user, 'verification', $error);
        }
    }

    private function key(User $user): string
    {
        return 'user:'.$user->id.':registered';
    }

    private function useUtcForTransaction(User $user): void
    {
        if ($user->getConnection()->getDriverName() === 'pgsql') {
            // Core registration dates have no SQL timezone and are written in UTC;
            // CRM columns are timestamptz. Keep their interpretation consistent,
            // without changing the connection timezone after this transaction.
            $user->getConnection()->statement("SET LOCAL TIME ZONE 'UTC'");
        }
    }

    private function finish(CrmLifecycleEvent $event, string $state, array $details = []): void
    {
        $event->payload = array_merge($event->payload, $details, ['state' => $state]);
        $event->processed_at = now('UTC');
        $event->saveOrFail();
        if ($state === 'conflict') {
            Log::warning('CRM registration matching requires manual resolution.', ['lifecycle_event_id' => $event->id, 'user_id' => $event->user_id] + $details);
        }
    }

    private function failure(User $user, string $stage, Throwable $error): void
    {
        // No CRM exception may invalidate an already created/verified account.
        Log::error('CRM registration synchronization failed.', ['stage' => $stage, 'user_id' => $user->id,
            'event_key' => $this->key($user), 'exception' => $error]);
        try {
            // After rollback, keep a durable diagnostic for an eligible event.
            $user->getConnection()->transaction(function () use ($user, $stage, $error) {
                $this->useUtcForTransaction($user);
                $event = CrmLifecycleEvent::on($user->getConnectionName())->where('source', self::SOURCE)
                    ->where('event_key', $this->key($user))->lockForUpdate()->first();
                // Never overwrite a concurrent successful completion with a stale failure.
                if ($event && $event->processed_at === null) {
                    $event->update(['payload' => array_merge($event->payload, ['state' => 'failed', 'failed_stage' => $stage,
                        'error_class' => get_class($error), 'failed_at' => now('UTC')->toISOString()])]);
                }
            }, 3);
        } catch (Throwable $diagnosticError) {
            Log::error('CRM registration diagnostic could not be persisted.', ['user_id' => $user->id, 'event_key' => $this->key($user), 'exception' => $diagnosticError]);
        }
    }
}
