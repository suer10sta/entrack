<?php

namespace App\Services\Surveys;

class D1Lifecycle extends SurveyLifecycle
{
    protected function stageChanged(int $companyId): bool
    {
        return parent::stageChanged($companyId) || $this->eligibility->hasConfirmedPurchase($companyId);
    }
}
