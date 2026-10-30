<?php

namespace App\Services\Surveys;

use App\Models\Entreprise;

class D4Eligibility extends D1Eligibility
{
    public function stageRelevant(int $companyId): bool
    {
        $company = Entreprise::find($companyId);

        return $company && ! $company->first_paid_at && $company->trial_ends_at && $company->trial_ends_at->copy()->addHours(24)->lte(now('UTC'))
            && ! app(SurveySubscriptionState::class)->hasRelevantSubscription($company);
    }

    public function eligible(int $companyId): bool
    {
        $owner = $this->owner($companyId);

        return $this->stageRelevant($companyId) && $owner && $owner->email_verified_at
            && filter_var($owner->email, FILTER_VALIDATE_EMAIL) && ! $this->optedOut($companyId);
    }
}
