<?php

namespace App\Services\Surveys;

class D6Eligibility extends D1Eligibility
{
    public function eligible(int $companyId): bool
    {
        $end = app(SubscriptionChurnState::class)->currentEnd($companyId);
        $owner = $this->owner($companyId);

        return $end && now('UTC')->gte($end['ended_at']->addDays(7)) && now('UTC')->lte($end['ended_at']->addDays(10))
            && $owner && $owner->email_verified_at && filter_var($owner->email, FILTER_VALIDATE_EMAIL) && ! $this->optedOut($companyId);
    }
}
