<?php

namespace App\Services\Surveys;

use App\Enums\SurveyActionType;
use App\Enums\SurveyStatus;
use App\Enums\SurveyType;
use App\Mail\D1AccountWaitingMail;
use App\Models\SurveyResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class D1AbandonMailer
{
    public const ATTEMPT_KEY = 'D1:ACCOUNT_WAITING:ATTEMPT';

    public const SENT_KEY = 'D1:ACCOUNT_WAITING:SENT';

    public function __construct(private SurveyEmailGate $gate) {}

    public function send(SurveyResponse $survey): bool
    {
        return $this->gate->forCompany($survey->entreprise_id, function () use ($survey) {
            $fresh = $survey->fresh();
            if ($fresh->survey_type !== SurveyType::D1 || $fresh->status !== SurveyStatus::Abandon || ! $fresh->completed_at
                || ! $fresh->actions()->where('action_key', D1ActionService::PROCESSED_KEY)->exists()
                || $fresh->answers()->where('question_id', 'q1')->value('value') !== 'abandon'
                || $fresh->answers()->where('question_id', 'q4')->value('value') !== D1ActionService::TIME_REASON) {
                return true;
            }
            if ($fresh->actions()->where('action_key', self::ATTEMPT_KEY)->exists()) {
                return true;
            }
            $locale = $fresh->user?->locale;
            $locale = in_array($locale, ['fr', 'en', 'nl', 'es'], true) ? $locale : 'fr';
            $mail = new D1AccountWaitingMail($locale);
            $mail->render();
            $claimed = DB::transaction(function () use ($fresh) {
                $locked = SurveyResponse::lockForUpdate()->findOrFail($fresh->id);
                if ($locked->actions()->where('action_key', self::ATTEMPT_KEY)->exists()) {
                    return false;
                }
                $locked->actions()->create(['action_type' => SurveyActionType::EmailAttempted,
                    'action_key' => self::ATTEMPT_KEY, 'detail' => 'D1 account-waiting acknowledgement', 'triggered_at' => now('UTC')]);

                return true;
            });
            if (! $claimed) {
                return true;
            }
            try {
                $result = Mail::to($fresh->email)->send($mail);
                if (! $result && ! (Mail::getFacadeRoot() instanceof \Illuminate\Support\Testing\Fakes\MailFake)) {
                    throw new \RuntimeException('D1 acknowledgement not confirmed.');
                }
            } catch (Throwable $exception) {
                $fresh->actions()->create(['action_type' => SurveyActionType::EmailUnconfirmed,
                    'action_key' => 'D1:ACCOUNT_WAITING:UNCONFIRMED', 'detail' => 'No automatic resend; delivery requires review.', 'triggered_at' => now('UTC')]);
                Log::warning('D1 acknowledgement delivery unconfirmed.', ['survey_id' => $fresh->id, 'exception_class' => $exception::class]);

                return false;
            }
            $fresh->actions()->create(['action_type' => SurveyActionType::AutoEmail,
                'action_key' => self::SENT_KEY, 'detail' => 'Account-waiting acknowledgement accepted by mailer.', 'triggered_at' => now('UTC')]);

            return true;
        }) ?? false;
    }
}
