<?php

namespace App\Services\Surveys;

use App\Enums\SurveyActionType;
use App\Enums\SurveyStatus;
use App\Enums\SurveyType;
use App\Mail\D1SurveyMail;
use App\Models\SurveyResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Throwable;

class D1Lifecycle
{
    public const CYCLE_KEY = 'once';

    public function __construct(private D1Eligibility $eligibility, private SurveyEmailGate $gate) {}

    public function process(int $companyId): string
    {
        return $this->gate->forCompany($companyId, fn () => $this->processLocked($companyId)) ?? 'busy';
    }

    private function processLocked(int $companyId): string
    {
        $survey = SurveyResponse::where('entreprise_id', $companyId)->where('survey_type', SurveyType::D1)
            ->where('cycle_key', self::CYCLE_KEY)->first();
        if ($survey) {
            if (in_array($survey->status, [SurveyStatus::Answered, SurveyStatus::Abandon, SurveyStatus::Cancelled, SurveyStatus::Expired], true)) {
                return 'closed';
            }
            if ($this->eligibility->hasActivity($companyId)) {
                $this->cancel($survey, 'activity');

                return 'cancelled';
            }
            if ($this->eligibility->optedOut($companyId)) {
                return 'opted_out';
            }
            $owner = $this->eligibility->owner($companyId);
            if (! $owner || $survey->user_id !== $owner->id || ! $owner->email_verified_at
                || strcasecmp($survey->email, $owner->email) !== 0) {
                $this->cancel($survey, 'recipient');

                return 'cancelled';
            }
            if ($survey->sent_at && (now('UTC')->gte($survey->sent_at->addDays(15))
                || ($survey->reminder_sent_at && now('UTC')->gte($survey->reminder_sent_at->addDays(10))))) {
                DB::transaction(function () use ($survey) {
                    $fresh = SurveyResponse::lockForUpdate()->findOrFail($survey->id);
                    if (in_array($fresh->status, [SurveyStatus::Sent, SurveyStatus::Q1Clicked, SurveyStatus::Relaunched], true)) {
                        $fresh->update(['status' => SurveyStatus::Expired]);
                    }
                });

                return 'expired';
            }
            if ($survey->sent_at) {
                $reminderBlocked = $survey->actions()->where('action_key', 'reminder:suppressed')->exists();
                if ($reminderBlocked || $survey->reminder_sent_at || now('UTC')->lt($survey->sent_at->addDays(3))
                    || now('UTC')->gt($survey->sent_at->addDays(5))) {
                    return 'waiting';
                }

                return $this->deliver($survey, true);
            }
        }
        if (! $this->eligibility->eligible($companyId)) {
            return 'ineligible';
        }
        if (! $this->gate->allows($companyId)) {
            return 'rate_limited';
        }
        if (! $survey) {
            $owner = $this->eligibility->owner($companyId);
            $survey = SurveyResponse::firstOrCreate([
                'entreprise_id' => $companyId, 'survey_type' => SurveyType::D1, 'cycle_key' => self::CYCLE_KEY,
            ], ['survey_version' => config('surveys.types.D1.current_version'), 'user_id' => $owner->id, 'email' => $owner->email]);
        }

        return $this->deliver($survey, false);
    }

    private function deliver(SurveyResponse $survey, bool $reminder): string
    {
        if (! $this->gate->allows($survey->entreprise_id)) {
            return 'rate_limited';
        }
        $key = 'delivery:'.($reminder ? 'reminder' : 'initial');
        if ($survey->actions()->where('action_key', $key.':attempt')->exists()) {
            return 'already_attempted';
        }
        $locale = $survey->user?->locale;
        $locale = in_array($locale, config('surveys.locales'), true) ? $locale : 'fr';
        $at = now('UTC')->toImmutable();
        $snapshot = clone $survey;
        $snapshot->sent_at ??= $at;
        $mail = new D1SurveyMail($snapshot, $locale, $reminder);
        // Template/configuration failures occur before reserving any external delivery.
        $mail->render();
        $claimed = DB::transaction(function () use ($survey, $key, $at) {
            $fresh = SurveyResponse::lockForUpdate()->findOrFail($survey->id);
            if (in_array($fresh->status, [SurveyStatus::Answered, SurveyStatus::Abandon, SurveyStatus::Cancelled, SurveyStatus::Expired], true)
                || $fresh->actions()->where('action_key', $key.':attempt')->exists()
                || $this->eligibility->hasActivity($fresh->entreprise_id)
                || $this->eligibility->optedOut($fresh->entreprise_id)) {
                return false;
            }
            $fresh->actions()->create(['action_type' => SurveyActionType::EmailAttempted, 'action_key' => $key.':attempt', 'triggered_at' => $at]);

            return true;
        });
        if (! $claimed) {
            return 'closed';
        }
        try {
            // Existing configured mailer; no SMTP under a database transaction.
            $sent = Mail::to($survey->email)->send($mail);
            if ($sent === null && ! (Mail::getFacadeRoot() instanceof \Illuminate\Support\Testing\Fakes\MailFake)) {
                throw new RuntimeException('Survey email delivery was not confirmed.');
            }
        } catch (Throwable $exception) {
            $survey->actions()->create(['action_type' => SurveyActionType::EmailUnconfirmed, 'action_key' => $key.':unconfirmed', 'detail' => 'No automatic retry; delivery requires review.']);
            Log::warning('Survey email delivery unconfirmed.', ['survey_id' => $survey->id, 'exception_class' => $exception::class]);

            return 'unconfirmed';
        }
        DB::transaction(function () use ($survey, $reminder, $key, $at) {
            $fresh = SurveyResponse::lockForUpdate()->findOrFail($survey->id);
            $attributes = [$reminder ? 'reminder_sent_at' : 'sent_at' => $at];
            if ($reminder && in_array($fresh->status, [SurveyStatus::Sent, SurveyStatus::Q1Clicked], true)) {
                $attributes['status'] = SurveyStatus::Relaunched;
            }
            $fresh->update($attributes);
            $fresh->actions()->create(['action_type' => $reminder ? SurveyActionType::ReminderSent : SurveyActionType::Sent,
                'action_key' => $key.':sent', 'triggered_at' => $at]);
        });

        return $reminder ? 'reminded' : 'sent';
    }

    public function suppressReminder(SurveyResponse $survey): void
    {
        // Future D2 calls this while holding the shared company delivery gate.
        DB::transaction(function () use ($survey) {
            $fresh = SurveyResponse::lockForUpdate()->findOrFail($survey->id);
            if ($fresh->survey_type === SurveyType::D1 && ! $fresh->completed_at && ! $fresh->reminder_sent_at) {
                $fresh->actions()->firstOrCreate(['action_key' => 'reminder:suppressed'], [
                    'action_type' => SurveyActionType::Cancelled, 'detail' => 'D1 reminder suppressed by a later questionnaire.',
                ]);
            }
        });
    }

    private function cancel(SurveyResponse $survey, string $reason): void
    {
        DB::transaction(function () use ($survey, $reason) {
            $fresh = SurveyResponse::lockForUpdate()->findOrFail($survey->id);
            if (! in_array($fresh->status, [SurveyStatus::Sent, SurveyStatus::Q1Clicked, SurveyStatus::Relaunched], true)) {
                return;
            }
            $fresh->update(['status' => SurveyStatus::Cancelled]);
            $fresh->actions()->create(['action_type' => SurveyActionType::Cancelled, 'action_key' => 'cancel:'.$reason, 'detail' => $reason]);
        });
    }
}
