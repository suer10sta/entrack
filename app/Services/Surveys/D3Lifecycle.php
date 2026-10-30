<?php

namespace App\Services\Surveys;

use App\Enums\SurveyType;
use App\Mail\D3SurveyMail;

class D3Lifecycle extends SurveyLifecycle
{
    protected SurveyType $type = SurveyType::D3;

    protected string $mailClass = D3SurveyMail::class;

    public function __construct(D3Eligibility $eligibility, SurveyEmailGate $gate)
    {
        parent::__construct($eligibility, $gate);
    }

    protected function stageChanged(int $companyId): bool
    {
        return ! $this->eligibility->trialRelevant($companyId);
    }
}
