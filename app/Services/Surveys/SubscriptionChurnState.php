<?php

namespace App\Services\Surveys;

use App\Models\Entreprise;
use App\Models\SurveyResponse;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class SubscriptionChurnState
{
    public function currentEnd(int $companyId): ?array
    {
        $company = Entreprise::find($companyId);
        if (! $company || ! $company->first_paid_at || ! $company->first_paid_invoice_id) {
            return null;
        }
        $candidate = null;
        foreach (DB::table('subscriptions')->where('entreprise_id', $companyId)->get() as $s) {
            $end = in_array($s->stripe_status, ['canceled', 'unpaid'], true) ? ($s->survey_ended_at ?? $s->ends_at) : $s->ends_at;
            $end = $end ? CarbonImmutable::parse($end)->utc() : null;
            if (in_array($s->stripe_status, ['active', 'trialing', 'past_due', 'unpaid', 'incomplete', 'paused'], true) && (! $end || $end->gt(now('UTC')))) {
                return null;
            }
            if (! $end && $s->stripe_status === 'canceled') {
                return null;
            }
            if (! $end || $end->gt(now('UTC')) || $end->lt($company->first_paid_at) || $s->stripe_status === 'incomplete_expired') {
                continue;
            }
            if (! in_array($s->stripe_status, ['active', 'trialing', 'past_due', 'unpaid', 'canceled'], true)) {
                continue;
            }
            if (! $candidate || $end->gt($candidate['ended_at']) || ($end->eq($candidate['ended_at']) && $s->id > $candidate['subscription_id'])) {
                $candidate = ['subscription_id' => (int) $s->id, 'ended_at' => $end, 'cycle_key' => 'churn:'.$s->id.':'.$end->timestamp];
            }
        }
        if ($candidate && $company->survey_resumed_at && CarbonImmutable::parse($company->survey_resumed_at)->gte($candidate['ended_at'])) {
            return null;
        }

        return $candidate;
    }

    public function relevant(SurveyResponse $survey): bool
    {
        $end = $this->currentEnd($survey->entreprise_id);

        return $end && $survey->subscription_id === $end['subscription_id'] && $survey->cycle_key === $end['cycle_key'];
    }

    public function recallApplicable(SurveyResponse $survey): bool
    {
        return $survey->completed_at && $survey->recall_due_at && $this->relevant($survey)
            && ! app(D1Eligibility::class)->optedOut($survey->entreprise_id)
            && ! $survey->actions()->whereIn('action_key', ['D6:STOP_FOLLOW_UP', 'D6:RECALL_OBSOLETE'])->exists();
    }
}
