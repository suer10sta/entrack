<?php

namespace App\Services\Surveys;

use App\Enums\SurveyType;
use App\Mail\D2SurveyMail;
use App\Models\SurveyResponse;

class D2Lifecycle extends SurveyLifecycle
{
    protected SurveyType $type = SurveyType::D2;

    protected string $mailClass = D2SurveyMail::class;

    public function __construct(D2Eligibility $eligibility, SurveyEmailGate $gate)
    {
        parent::__construct($eligibility, $gate);
    }

    protected function stageChanged(int $companyId): bool
    {
        return ! $this->eligibility->trialRelevant($companyId);
    }

    protected function initialClaimed(SurveyResponse $survey): void
    {
        SurveyResponse::where('entreprise_id', $survey->entreprise_id)->where('survey_type', SurveyType::D1)
            ->whereNull('completed_at')->each(fn ($d1) => app(D1Lifecycle::class)->suppressReminder($d1));
    }
}
