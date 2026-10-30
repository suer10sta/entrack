<?php

namespace App\Services\Surveys;

use App\Enums\SurveyActionType;
use App\Enums\SurveyStatus;
use App\Models\Entreprise;
use App\Models\SurveyResponse;

class D4StageGuard
{
    // Caller holds the Survey row lock in the answer/action transaction.
    public function allows(SurveyResponse $survey): bool
    {
        Entreprise::whereKey($survey->entreprise_id)->lockForUpdate()->firstOrFail();
        if (app(D4Eligibility::class)->stageRelevant($survey->entreprise_id)) {
            return true;
        }
        if (in_array($survey->status, [SurveyStatus::Sent, SurveyStatus::Q1Clicked, SurveyStatus::Relaunched], true)
            || ($survey->completed_at && ! $survey->actions()->where('action_key', D4ActionService::PROCESSED_KEY)->exists())) {
            $survey->update(['status' => SurveyStatus::Cancelled]);
            $survey->actions()->firstOrCreate(['action_key' => 'cancel:stage'], ['action_type' => SurveyActionType::Cancelled, 'detail' => 'D4 no longer relevant after trial or subscription change.', 'triggered_at' => now('UTC')]);
        }

        return false;
    }
}
