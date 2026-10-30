<?php

namespace App\Services\Surveys;

use App\Models\Entreprise;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class SubscriptionChurnSync
{
    public function event(array $payload, string $kind): void
    {
        try {
            $data = $payload['data']['object'] ?? [];
            $at = $payload['created'] ?? null;
            if (! is_int($at) || $at <= 0 || $at > now('UTC')->timestamp || ! is_string($data['id'] ?? null)) {
                return;
            }
            $company = Entreprise::where('stripe_id', $data['customer'] ?? null)->first();
            if (! $company) {
                return;
            }
            DB::transaction(function () use ($company, $data, $at, $kind) {
                $company = Entreprise::whereKey($company->id)->lockForUpdate()->firstOrFail();
                $s = DB::table('subscriptions')->where('entreprise_id', $company->id)->where('stripe_id', $data['id'])->lockForUpdate()->first();
                if (! $s || ($s->survey_event_at && CarbonImmutable::parse($s->survey_event_at)->timestamp > $at)) {
                    return;
                }
                $end = $data['ended_at'] ?? null;
                if (! is_int($end) || $end <= 0 || $end > now('UTC')->timestamp) {
                    $end = null;
                }
                if ($kind === 'deleted' && $end === null && $s->ends_at && CarbonImmutable::parse($s->ends_at)->lte(now('UTC'))) {
                    $end = CarbonImmutable::parse($s->ends_at)->timestamp;
                }
                $sameTerminalCycle = $s->survey_ended_at && in_array($s->stripe_status, ['canceled', 'unpaid'], true)
                    && (! $company->survey_resumed_at || CarbonImmutable::parse($company->survey_resumed_at)->lte(CarbonImmutable::parse($s->survey_ended_at)));
                if ($sameTerminalCycle && ($kind === 'deleted' || in_array($data['status'] ?? null, ['canceled', 'unpaid'], true))) {
                    $end = CarbonImmutable::parse($s->survey_ended_at)->timestamp;
                } elseif (($data['status'] ?? null) === 'unpaid' && $end === null && $company->account_status === 'inactive' && $company->failed_payment_count >= 3) {
                    $end = $at;
                }
                $updates = ['survey_event_at' => CarbonImmutable::createFromTimestampUTC($at)->toIso8601String()];
                if ($end !== null) {
                    $updates['survey_ended_at'] = CarbonImmutable::createFromTimestampUTC($end)->toIso8601String();
                    $updates['ends_at'] = $updates['survey_ended_at'];
                    $updates['stripe_status'] = $kind === 'deleted' ? 'canceled' : $data['status'];
                } elseif (in_array($data['status'] ?? null, ['active', 'trialing'], true)) {
                    $updates['survey_ended_at'] = null;
                    DB::table('entreprises')->where('id', $company->id)->where(fn ($q) => $q->whereNull('survey_resumed_at')->orWhere('survey_resumed_at', '<', CarbonImmutable::createFromTimestampUTC($at)->toIso8601String()))->update(['survey_resumed_at' => CarbonImmutable::createFromTimestampUTC($at)->toIso8601String()]);
                }
                DB::table('subscriptions')->where('id', $s->id)->update($updates);
                DB::afterCommit(fn () => app(D6Lifecycle::class)->reconcile($company->id));
            }, 3);
        } catch (Throwable $e) {
            Log::warning('D6 subscription evidence sync not confirmed.', ['exception_class' => $e::class]);
        }
    }

    public function checkout(Entreprise $company, $session): void
    {
        if (($session->payment_status ?? null) === 'paid' && ($session->mode ?? null) === 'subscription' && ($session->amount_total ?? 0) > 0
            && ($session->customer ?? null) === $company->stripe_id && (string) ($session->metadata['entreprise_id'] ?? '') === (string) $company->id) {
            $this->resumed($company, now('UTC')->timestamp);
        }
    }

    public function invoice(Entreprise $company, array $invoice, bool $healthy): void
    {
        $at = $invoice['status_transitions']['paid_at'] ?? null;
        if ($healthy && ($invoice['status'] ?? null) === 'paid' && ($invoice['amount_paid'] ?? 0) > 0 && ($invoice['customer'] ?? null) === $company->stripe_id
            && ($invoice['subscription'] ?? $invoice['parent']['subscription_details']['subscription'] ?? null) && is_int($at) && $at > 0 && $at <= now('UTC')->timestamp) {
            $this->resumed($company, $at);
        }
    }

    private function resumed(Entreprise $company, int $at): void
    {
        try {
            $date = CarbonImmutable::createFromTimestampUTC($at)->toIso8601String();
            DB::table('entreprises')->where('id', $company->id)->where(fn ($q) => $q->whereNull('survey_resumed_at')->orWhere('survey_resumed_at', '<', $date))->update(['survey_resumed_at' => $date]);
            DB::afterCommit(fn () => app(D6Lifecycle::class)->reconcile($company->id));
        } catch (Throwable $e) {
            Log::warning('D6 resumption evidence sync not confirmed.', ['entreprise_id' => $company->id, 'exception_class' => $e::class]);
        }
    }
}
