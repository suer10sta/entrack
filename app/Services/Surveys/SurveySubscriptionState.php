<?php

namespace App\Services\Surveys;

use App\Models\Entreprise;
use Illuminate\Support\Facades\DB;

class SurveySubscriptionState
{
    public function hasRelevantSubscription(Entreprise $company): bool
    {
        return $company->first_paid_at !== null || $company->first_paid_lookup_customer_id !== null || $company->type_compt !== null || DB::table('subscriptions')->where('entreprise_id', $company->id)
            ->whereIn('stripe_status', ['active', 'trialing', 'past_due', 'unpaid'])
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>', now('UTC')))->exists();
    }
}
