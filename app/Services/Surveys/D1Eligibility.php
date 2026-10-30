<?php

namespace App\Services\Surveys;

use App\Enums\SurveyStatus;
use App\Models\Client;
use App\Models\Employees;
use App\Models\Project;
use App\Models\SurveyResponse;
use App\Models\tasks_timer;
use App\Models\User;

class D1Eligibility
{
    public function owner(int $companyId): ?User
    {
        return User::where('entreprise_id', $companyId)->orderBy('id')->first();
    }

    public function hasActivity(int $companyId): bool
    {
        if (Project::where('entreprise_id', (string) $companyId)->exists()
            || Client::where('entreprise_id', $companyId)->exists()
            || tasks_timer::where('entreprise_id', $companyId)->exists()) {
            return true;
        }
        $owner = $this->owner($companyId);
        $seed = $owner && $owner->created_at
            ? Employees::where('entreprise_id', $companyId)->where('user_id', $owner->id)
                ->whereBetween('created_at', [$owner->created_at, $owner->created_at->copy()->addMinute()])
                ->orderBy('id')->first()
            : null;

        return Employees::where('entreprise_id', $companyId)
            ->when($seed, fn ($query) => $query->where('id', '<>', $seed->id))->exists();
    }

    public function optedOut(int $companyId): bool
    {
        return SurveyResponse::where('entreprise_id', $companyId)
            ->where(fn ($query) => $query->where('status', SurveyStatus::Abandon)
                ->orWhereHas('answers', fn ($answers) => $answers->where('question_id', 'q1')->where('value', 'abandon')))
            ->exists();
    }

    public function hasConfirmedPurchase(int $companyId): bool
    {
        $company = \App\Models\Entreprise::find($companyId);

        return $company && ($company->first_paid_at !== null || $company->first_paid_lookup_customer_id !== null);
    }

    public function eligible(int $companyId): bool
    {
        $owner = $this->owner($companyId);

        return $owner && $owner->email_verified_at
            && $owner->email_verified_at->lte(now('UTC')->subDay())
            && filter_var($owner->email, FILTER_VALIDATE_EMAIL)
            && ! $this->optedOut($companyId) && ! $this->hasActivity($companyId) && ! $this->hasConfirmedPurchase($companyId);
    }
}
