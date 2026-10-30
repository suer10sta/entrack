<?php

namespace App\Services\Surveys;

use App\Models\Entreprise;

class D5Eligibility extends D1Eligibility
{
    public function purchased(int $companyId): bool
    {
        $company = Entreprise::find($companyId);

        return $company && $company->first_paid_at && $company->first_paid_invoice_id && $company->first_paid_at->lte(now('UTC'));
    }

    public function eligible(int $companyId): bool
    {
        $company = Entreprise::find($companyId);
        $owner = $this->owner($companyId);

        return $this->purchased($companyId) && $company->first_paid_at->addDays(7)->lte(now('UTC'))
            && $owner && $owner->email_verified_at && filter_var($owner->email, FILTER_VALIDATE_EMAIL) && ! $this->optedOut($companyId);
    }
}
