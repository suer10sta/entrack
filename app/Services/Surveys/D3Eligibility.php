<?php

namespace App\Services\Surveys;

use App\Enums\SurveyType;
use App\Models\Entreprise;
use App\Models\SurveyResponse;

class D3Eligibility extends D2Eligibility
{
    public function usageEstablished(int $companyId): bool
    {
        return $this->hasActivity($companyId) || SurveyResponse::where('entreprise_id', $companyId)
            ->where('survey_type', SurveyType::D2)->whereHas('answers', fn ($q) => $q->where('question_id', 'q1')->where('value', 'usage_reel'))->exists();
    }

    public function eligible(int $companyId): bool
    {
        $company = Entreprise::find($companyId);
        $owner = $this->owner($companyId);

        return $this->trialRelevant($companyId) && $company->trial_ends_at->copy()->subDays(5)->lte(now('UTC'))
            && $owner && $owner->email_verified_at && filter_var($owner->email, FILTER_VALIDATE_EMAIL)
            && ! $this->optedOut($companyId) && $this->usageEstablished($companyId);
    }
}
