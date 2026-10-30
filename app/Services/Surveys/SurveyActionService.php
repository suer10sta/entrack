<?php

namespace App\Services\Surveys;

use App\Enums\ContactSource;
use App\Enums\SurveyActionType;
use App\Enums\SurveyStatus;
use App\Enums\SurveyType;
use App\Models\Contact;
use App\Models\SurveyResponse;
use App\Services\TicketService;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use LogicException;
use Throwable;

class SurveyActionService
{
    protected SurveyType $type = SurveyType::D1;

    protected ContactSource $source = ContactSource::D1Survey;

    protected string $origin = 'Questionnaire D1 — Démarrage';

    protected function signals(SurveyResponse $survey, array $answers): void {}

    protected function priority(array $answers): \App\Enums\ContactPriority
    {
        return \App\Enums\ContactPriority::Normal;
    }

    public const CONTACT_KEY = 'D1:CREATE_CONTACT';

    public const PROCESSED_KEY = 'D1:ACTIONS_EVALUATED';

    public const TIME_REASON = 'Je manque de temps';

    public function __construct(private TicketService $tickets, private D1Eligibility $activity,
        private SurveyCatalog $catalog, private D1AbandonMailer $abandonMailer) {}

    public function processSafely(SurveyResponse $survey): bool
    {
        try {
            $this->process($survey);

            return true;
        } catch (Throwable $exception) {
            // Answers are already committed. Do not expose tokens, input or transport secrets.
            Log::warning('D1 action processing not confirmed.', ['survey_id' => $survey->id, 'exception_class' => $exception::class]);

            return false;
        }
    }

    public function process(SurveyResponse $survey): ?Contact
    {
        if (DB::transactionLevel() !== 0 || $survey->getConnection()->getName() !== DB::connection()->getName()) {
            throw new LogicException('D1 actions must run after the answer transaction on the ticket connection.');
        }
        [$contact, $sendEmail] = DB::transaction(function () use ($survey) {
            $fresh = SurveyResponse::whereKey($survey->id)->lockForUpdate()->firstOrFail();
            if ($fresh->survey_type !== $this->type || ! $fresh->completed_at
                || ! in_array($fresh->status, [SurveyStatus::Answered, SurveyStatus::Abandon], true)
                || ! $fresh->actions()->where('action_key', 'answered:final')->exists()) {
                return [null, false];
            }
            $receipt = $fresh->actions()->where('action_key', static::PROCESSED_KEY)->first();
            if ($receipt) {
                $detail = json_decode($receipt->detail, true, 16, JSON_THROW_ON_ERROR);

                return [$fresh->contact, $detail['abandon_email'] ?? false];
            }
            // Never infer permission to create a replacement from contact_id becoming NULL.
            if ($fresh->actions()->where('action_key', static::CONTACT_KEY)->exists()) {
                $this->processed($fresh, true, false);

                return [$fresh->contact, false];
            }
            if ($fresh->survey_version !== 1) {
                throw new DomainException('D1 action rules require an explicitly supported survey version.');
            }
            $questions = $this->catalog->definition($this->type, $fresh->survey_version)['questions'];
            $answers = $fresh->answers()->pluck('value', 'question_id')->all();
            $applicable = app(D1AnswerService::class)->applicable($questions, $answers);
            $answers = array_intersect_key($answers, $applicable);
            $abandon = ($answers['q1'] ?? null) === 'abandon';
            $email = $abandon && ($answers['q4'] ?? null) === static::TIME_REASON;
            $rules = $abandon ? [] : $this->rules($answers, $fresh->entreprise_id);
            $contact = null;
            if ($rules) {
                if ($fresh->contact_id !== null) {
                    throw new DomainException('An existing survey contact requires reconciliation before automatic processing.');
                }
                $company = $fresh->entreprise()->firstOrFail();
                $user = $fresh->user;
                $name = trim($user?->name ?? '') ?: (trim($company->name ?? '') ?: 'Utilisateur DalyTrac');
                $contact = $this->tickets->create(
                    name: mb_substr($name, 0, 255), email: $fresh->email,
                    subject: $this->type->value.' — '.reset($rules), message: $this->message($fresh, $questions, $answers, $rules),
                    source: $this->source, ip: null, user: $user, entreprise: $company,
                    metadata: ['survey_type' => $this->type->value, 'survey_id' => $fresh->id,
                        'survey_version' => $fresh->survey_version, 'rules' => array_keys($rules)],
                    priority: $this->priority($answers),
                );
                $fresh->update(['contact_id' => $contact->id]);
                $fresh->actions()->create(['action_type' => SurveyActionType::Ticket,
                    'action_key' => static::CONTACT_KEY, 'detail' => 'Contact '.$contact->ticket_num, 'triggered_at' => now('UTC')]);
            }
            $this->signals($fresh, $answers);
            $this->processed($fresh, (bool) $rules, $email);

            return [$contact, $email];
        }, 3);
        if ($sendEmail) {
            if (! $this->abandonMailer->send($survey)) {
                throw new \RuntimeException('D1 acknowledgement delivery is pending or unconfirmed.');
            }
        }

        return $contact;
    }

    private function processed(SurveyResponse $survey, bool $contactRequired, bool $email): void
    {
        $survey->actions()->create(['action_type' => SurveyActionType::ActionsProcessed, 'action_key' => static::PROCESSED_KEY,
            'detail' => json_encode(['contact_required' => $contactRequired, 'abandon_email' => $email], JSON_THROW_ON_ERROR),
            'triggered_at' => now('UTC')]);
    }

    protected function rules(array $answers, int $companyId): array
    {
        $rules = [];
        if (($answers['q1'] ?? null) === 'connexion') {
            $rules['q1.connexion'] = 'Problème de connexion';
        }
        if (($answers['q1'] ?? null) === 'probleme_technique') {
            $rules['q1.probleme_technique'] = 'Problème technique';
        }
        if (($answers['q2'] ?? null) === 'Un message d’erreur s’affiche') {
            $rules['q2.erreur'] = 'Message d’erreur';
        }
        if (($answers['q3'] ?? null) === 'Une assistance technique') {
            $rules['q3.assistance'] = 'Demande d’assistance';
        }
        if (($answers['q3'] ?? null) === 'Une démonstration') {
            $rules['q3.demonstration'] = 'Demande de démonstration';
        }
        if (($answers['q1'] ?? null) === 'reussi' && ! $this->activity->hasActivity($companyId)) {
            $rules['q1.reussi_sans_activite'] = 'Démarrage déclaré non constaté';
        }

        return $rules;
    }

    private function message(SurveyResponse $survey, array $questions, array $answers, array $rules): string
    {
        $lines = ['Origine : '.$this->origin, 'Version : '.$survey->survey_version, '', 'Besoins identifiés :'];
        foreach ($rules as $need) {
            $lines[] = '- '.$need;
        }
        $lines[] = '';
        foreach ($questions as $question) {
            $id = $question['id'];
            if (! isset($answers[$id])) {
                continue;
            }
            $value = $answers[$id];
            if ($question['type'] !== 'text') {
                $choice = collect($question['choices'])->firstWhere('value', $value);
                if ($choice) {
                    $value = $this->catalog->label($choice['label_key'], 'fr');
                }
            }
            $lines[] = $this->catalog->label($question['label_key'], 'fr');
            $lines[] = $value;
            $lines[] = '';
        }

        return str_replace($survey->token, '[lien personnel masqué]', implode("\n", $lines));
    }

    public function retryCompany(int $companyId): int
    {
        $failures = 0;
        SurveyResponse::where('entreprise_id', $companyId)->where('survey_type', $this->type)
            ->whereNotNull('completed_at')->whereIn('status', [SurveyStatus::Answered, SurveyStatus::Abandon])
            ->whereHas('actions', fn ($query) => $query->where('action_key', 'answered:final'))
            ->where(function ($query) {
                $query->whereDoesntHave('actions', fn ($actions) => $actions->where('action_key', static::PROCESSED_KEY))
                    ->orWhere(function ($email) {
                        $email->where('status', SurveyStatus::Abandon)
                            ->whereHas('answers', fn ($answers) => $answers->where('question_id', 'q4')->where('value', static::TIME_REASON))
                            ->whereDoesntHave('actions', fn ($actions) => $actions->where('action_key', D1AbandonMailer::ATTEMPT_KEY));
                    });
            })->eachById(function ($survey) use (&$failures) {
                if (! $this->processSafely($survey)) {
                    $failures++;
                }
            }, 100);

        return $failures;
    }
}
