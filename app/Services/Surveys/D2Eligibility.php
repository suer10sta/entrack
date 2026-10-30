<?php

namespace App\Services\Surveys;

use App\Models\Entreprise;

class D2Eligibility extends D1Eligibility
{
    public function trialRelevant(int $companyId): bool
    {
        $company = Entreprise::find($companyId);
        if (! $company || ! $company->trial_ends_at || ! $company->trial_ends_at->isFuture()
            || $company->type_compt !== null) {
            return false;
        }

        return ! app(SurveySubscriptionState::class)->hasRelevantSubscription($company);
    }

    public function eligible(int $companyId): bool
    {
        $company = Entreprise::find($companyId);
        $owner = $this->owner($companyId);

        return $this->trialRelevant($companyId) && $company->trial_ends_at->copy()->subDays(12)->lte(now('UTC'))
            && $owner && $owner->email_verified_at && filter_var($owner->email, FILTER_VALIDATE_EMAIL)
            && ! $this->optedOut($companyId);
    }
}
