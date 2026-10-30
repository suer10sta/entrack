<?php

namespace App\Services\Surveys;

use App\Enums\SurveyActionType;
use App\Enums\SurveyType;
use App\Mail\D6SurveyMail;
use App\Models\SurveyResponse;
use Illuminate\Support\Facades\DB;

class D6Lifecycle extends SurveyLifecycle
{
    protected SurveyType $type = SurveyType::D6;

    protected string $mailClass = D6SurveyMail::class;

    public function __construct(D6Eligibility $eligibility, SurveyEmailGate $gate)
    {
        parent::__construct($eligibility, $gate);
    }

    protected function cycleKey(int $companyId): ?string
    {
        return app(SubscriptionChurnState::class)->currentEnd($companyId)['cycle_key'] ?? null;
    }

    protected function creationAttributes(int $companyId): array
    {
        return ['subscription_id' => app(SubscriptionChurnState::class)->currentEnd($companyId)['subscription_id']];
    }

    protected function stageChangedForSurvey(SurveyResponse $survey): bool
    {
        return ! app(SubscriptionChurnState::class)->relevant($survey);
    }

    public function reconcile(int $companyId): void
    {
        SurveyResponse::where('entreprise_id', $companyId)->where('survey_type', SurveyType::D6)->each(function ($survey) {
            if (app(SubscriptionChurnState::class)->relevant($survey)) {
                return;
            }
            if (! $survey->completed_at) {
                $this->cancel($survey, 'subscription_changed');

                return;
            }
            if ($survey->recall_due_at) {
                DB::transaction(function () use ($survey) {
                    $fresh = SurveyResponse::lockForUpdate()->findOrFail($survey->id);
                    if (! $fresh->recall_due_at) {
                        return;
                    }
                    $fresh->actions()->firstOrCreate(['action_key' => 'D6:RECALL_OBSOLETE'], ['action_type' => SurveyActionType::RecallObsolete, 'detail' => json_encode(['previous_due_at' => $fresh->recall_due_at->toIso8601String(), 'reason' => 'subscription_changed'], JSON_THROW_ON_ERROR), 'triggered_at' => now('UTC')]);
                    $fresh->update(['recall_due_at' => null]);
                });
            }
        });
    }

    protected function beforeProcessing(int $companyId): void
    {
        $this->reconcile($companyId);
        $end = app(SubscriptionChurnState::class)->currentEnd($companyId);
        if ($end && now('UTC')->gt($end['ended_at']->addDays(10))) {
            SurveyResponse::where('entreprise_id', $companyId)->where('survey_type', SurveyType::D6)->where('cycle_key', $end['cycle_key'])->whereNull('sent_at')->whereNull('completed_at')->each(fn ($s) => $this->cancel($s, 'send_window_elapsed'));
        }
    }
}
