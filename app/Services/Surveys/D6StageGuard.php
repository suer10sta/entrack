<?php

namespace App\Services\Surveys;

use App\Enums\SurveyActionType;
use App\Enums\SurveyStatus;
use App\Models\Entreprise;
use App\Models\SurveyResponse;
use Illuminate\Support\Facades\DB;

class D6StageGuard
{
    public function allows(SurveyResponse $survey): bool
    {
        Entreprise::whereKey($survey->entreprise_id)->lockForUpdate()->firstOrFail();
        DB::table('subscriptions')->where('entreprise_id', $survey->entreprise_id)->lockForUpdate()->get();
        if (app(SubscriptionChurnState::class)->relevant($survey)) {
            return true;
        }
        if (in_array($survey->status, [SurveyStatus::Sent, SurveyStatus::Q1Clicked, SurveyStatus::Relaunched], true) || ($survey->completed_at && ! $survey->actions()->where('action_key', D6ActionService::PROCESSED_KEY)->exists())) {
            $survey->update(['status' => SurveyStatus::Cancelled]);
            $survey->actions()->firstOrCreate(['action_key' => 'cancel:subscription_changed'], ['action_type' => SurveyActionType::Cancelled, 'detail' => 'D6 subscription cycle is obsolete.', 'triggered_at' => now('UTC')]);
        }

        return false;
    }
}
