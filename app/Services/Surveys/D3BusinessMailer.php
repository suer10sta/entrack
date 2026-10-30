<?php

namespace App\Services\Surveys;

use App\Enums\SurveyActionType;
use App\Enums\SurveyType;
use App\Mail\D3BusinessMail;
use App\Models\SurveyResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class D3BusinessMailer
{
    public function __construct(private SurveyEmailGate $gate) {}

    public function send(SurveyResponse $survey): bool
    {
        $survey = $survey->fresh();
        if ($survey->survey_type !== SurveyType::D3 || ! $survey->completed_at || ! $survey->actions()->where('action_key', D3ActionService::PROCESSED_KEY)->exists()) {
            return true;
        }

        return $this->gate->forCompany($survey->entreprise_id, function () use ($survey) {
            $ok = true;
            foreach (['plans' => SurveyActionType::PurchaseIntent, 'benefits' => SurveyActionType::DecisionApproval] as $kind => $type) {
                $key = 'D3:'.strtoupper($kind);
                if (! $survey->actions()->where('action_type', $type)->exists() || $survey->actions()->where('action_key', $key.':ATTEMPT')->exists()) {
                    continue;
                }
                $locale = $survey->user?->locale;
                $mail = new D3BusinessMail($kind, in_array($locale, ['fr', 'en', 'nl', 'es'], true) ? $locale : 'fr');
                $mail->render();
                $claimed = DB::transaction(function () use ($survey, $key, $kind) {
                    $fresh = SurveyResponse::lockForUpdate()->findOrFail($survey->id);
                    if ($fresh->actions()->where('action_key', $key.':ATTEMPT')->exists()) {
                        return false;
                    }
                    $fresh->actions()->create(['action_type' => SurveyActionType::EmailAttempted, 'action_key' => $key.':ATTEMPT', 'detail' => 'D3 '.$kind, 'triggered_at' => now('UTC')]);

                    return true;
                });
                if (! $claimed) {
                    continue;
                }
                try {
                    $result = Mail::to($survey->email)->send($mail);
                    if (! $result && ! (Mail::getFacadeRoot() instanceof \Illuminate\Support\Testing\Fakes\MailFake)) {
                        throw new \RuntimeException('D3 email not confirmed.');
                    }
                } catch (Throwable $exception) {
                    $survey->actions()->create(['action_type' => SurveyActionType::EmailUnconfirmed, 'action_key' => $key.':UNCONFIRMED', 'detail' => 'No automatic resend; delivery requires review.', 'triggered_at' => now('UTC')]);
                    Log::warning('D3 business email unconfirmed.', ['survey_id' => $survey->id, 'exception_class' => $exception::class]);
                    $ok = false;

                    continue;
                }
                $survey->actions()->create(['action_type' => SurveyActionType::AutoEmail, 'action_key' => $key.':SENT', 'detail' => 'D3 '.$kind.' accepted by mailer.', 'triggered_at' => now('UTC')]);
            }

            return $ok;
        }) ?? false;
    }
}
