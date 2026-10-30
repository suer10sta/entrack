<?php

namespace App\Services\Surveys;

use App\Enums\SurveyType;
use App\Mail\D4SurveyMail;
use App\Models\SurveyResponse;

class D4Lifecycle extends SurveyLifecycle
{
    protected SurveyType $type = SurveyType::D4;

    protected string $mailClass = D4SurveyMail::class;

    public function __construct(D4Eligibility $eligibility, SurveyEmailGate $gate)
    {
        parent::__construct($eligibility, $gate);
    }

    protected function stageChanged(int $companyId): bool
    {
        return ! $this->eligibility->stageRelevant($companyId);
    }

    protected function beforeProcessing(int $companyId): void
    {
        if (! $this->eligibility->stageRelevant($companyId)) {
            return;
        }
        SurveyResponse::where('entreprise_id', $companyId)->where('survey_type', SurveyType::D3)->whereNull('completed_at')
            ->each(fn ($survey) => $this->cancel($survey, 'trial_ended'));
    }
}
