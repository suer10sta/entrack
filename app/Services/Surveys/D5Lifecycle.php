<?php

namespace App\Services\Surveys;

use App\Enums\SurveyType;
use App\Mail\D5SurveyMail;
use App\Models\SurveyResponse;

class D5Lifecycle extends SurveyLifecycle
{
    protected SurveyType $type = SurveyType::D5;

    protected string $mailClass = D5SurveyMail::class;

    public function __construct(D5Eligibility $eligibility, SurveyEmailGate $gate)
    {
        parent::__construct($eligibility, $gate);
    }

    protected function stageChanged(int $companyId): bool
    {
        return ! $this->eligibility->purchased($companyId);
    }

    public function neutralizeTrialSurveys(int $companyId): void
    {
        if (! $this->eligibility->hasConfirmedPurchase($companyId)) {
            return;
        }
        SurveyResponse::where('entreprise_id', $companyId)->whereIn('survey_type', [SurveyType::D1, SurveyType::D2, SurveyType::D3, SurveyType::D4])->whereNull('completed_at')
            ->each(fn ($survey) => $this->cancel($survey, 'first_payment'));
    }

    protected function beforeProcessing(int $companyId): void
    {
        $this->neutralizeTrialSurveys($companyId);
    }
}
