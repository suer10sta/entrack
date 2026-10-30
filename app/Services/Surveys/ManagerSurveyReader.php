<?php

namespace App\Services\Surveys;

use App\Enums\SurveyStatus;
use App\Enums\SurveyType;
use App\Models\SurveyResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ManagerSurveyReader
{
    public function __construct(private SurveyCatalog $catalog, private SurveyAnswerService $answers) {}

    public function definition(SurveyType $type, ?int $version = null): array
    {
        return $this->catalog->definition($type, $version);
    }

    public function name(SurveyType $type): string
    {
        return __('manager-surveys.questionnaire').' '.substr($type->value, 1);
    }

    public function label(string $key): string
    {
        $locale = app()->getLocale();

        return $this->catalog->label($key, in_array($locale, config('surveys.locales'), true) ? $locale : 'fr');
    }

    public function filters(Request $request): array
    {
        $request->validate(['type' => ['sometimes', 'required', 'string', Rule::enum(SurveyType::class)]]);
        $type = SurveyType::tryFrom($request->input('type', 'D1'));
        abort_unless($type, 422);
        $questions = $this->definition($type)['questions'];
        $rules = ['type' => ['nullable', Rule::enum(SurveyType::class)], 'q' => ['nullable', 'string', 'max:255'], 'status' => ['nullable', Rule::enum(SurveyStatus::class)], 'per_page' => ['nullable', Rule::in([25, 50, 100])], 'unread' => ['nullable', 'boolean'], 'answers' => ['nullable', 'array:'.implode(',', array_column($questions, 'id'))]];
        foreach ($questions as $question) {
            $rules['answers.'.$question['id']] = $question['type'] === 'single_choice'
                ? ['nullable', 'string', Rule::in(array_column($question['choices'], 'value'))]
                : ['nullable', 'string', 'max:2000'];
        }

        return $request->validate($rules) + ['type' => $type->value];
    }

    public function query(array $filters, ?string $seen = null)
    {
        $query = SurveyResponse::query()->where('survey_type', $filters['type']);
        if ($search = $filters['q'] ?? null) {
            $pattern = '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], mb_strtolower($search)).'%';
            $query->where(fn ($q) => $q->whereRaw("LOWER(email) LIKE ? ESCAPE '!'", [$pattern])->orWhereHas('entreprise', fn ($company) => $company->whereRaw("LOWER(name) LIKE ? ESCAPE '!'", [$pattern])));
        }
        if ($status = $filters['status'] ?? null) {
            $query->where('status', $status);
        }
        $questions = collect($this->definition(SurveyType::from($filters['type']))['questions'])->keyBy('id');
        foreach ($filters['answers'] ?? [] as $id => $value) {
            if ($value !== null && $value !== '') {
                $query->whereHas('answers', function ($q) use ($id, $value, $questions) {
                    $q->where('question_id', $id);
                    if ($questions[$id]['type'] === 'single_choice') {
                        $q->where('value', $value);
                    } else {
                        $q->whereRaw("LOWER(value) LIKE ? ESCAPE '!'", ['%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], mb_strtolower($value)).'%']);
                    }
                });
            }
        }
        if ($filters['unread'] ?? false) {
            $query->whereNotNull('completed_at');
            if ($seen) {
                $query->where('completed_at', '>', $seen);
            }
        }

        return $query;
    }

    public function load($query)
    {
        return $query->select(array_map(fn ($name) => 'survey_responses.'.$name, ['id', 'survey_type', 'survey_version', 'entreprise_id', 'user_id', 'email', 'status', 'sent_at', 'reminder_sent_at', 'clicked_at', 'completed_at', 'recall_due_at', 'contact_id', 'created_at']))
            ->with(['answers', 'entreprise:id,name', 'user:id,name,email,locale', 'contact:id,ticket_num']);
    }

    public function emailPreview(SurveyResponse $survey, bool $reminder = false): array
    {
        $locale = $survey->user?->locale;
        $locale = in_array($locale, config('surveys.locales'), true) ? $locale : 'fr';
        $ui = $survey->survey_type === SurveyType::D1 ? 'surveys.ui' : 'surveys.'.strtolower($survey->survey_type->value).'_ui';
        $question = collect($this->definition($survey->survey_type, $survey->survey_version)['questions'])->firstWhere('id', 'q1');
        $choices = array_map(fn ($choice) => [
            'label' => $this->catalog->label($choice['label_key'], $locale),
            'url' => '#',
        ], $question['choices']);
        $previousLocale = app()->getLocale();
        try {
            app()->setLocale($locale);
            $html = view('emails.survey_d1', [
                'surveyLocale' => $locale, 'surveyUi' => $ui, 'reminder' => $reminder,
                'question' => $this->catalog->label($question['label_key'], $locale), 'choices' => $choices,
            ])->render();
            $subject = __($ui.'.'.($reminder ? 'reminder_subject' : 'email_subject'));
        } finally {
            app()->setLocale($previousLocale);
        }

        return ['html' => $html, 'subject' => $subject, 'locale' => $locale];
    }

    public function responses(SurveyResponse $survey): array
    {
        try {
            $questions = $this->definition($survey->survey_type, $survey->survey_version)['questions'];
        } catch (\InvalidArgumentException) {
            return $survey->answers->mapWithKeys(fn ($answer) => [$answer->question_id => ['label' => $answer->question_id, 'value' => $answer->value]])->all();
        }
        $values = $survey->answers->pluck('value', 'question_id')->all();
        $visible = $this->answers->applicable($questions, $values);
        $result = [];
        foreach ($visible as $id => $question) {
            $value = $values[$id] ?? null;
            $choice = collect($question['choices'] ?? [])->firstWhere('value', $value);
            $result[$id] = ['label' => $this->label($question['label_key']), 'value' => $choice ? $this->label($choice['label_key']) : $value];
        }

        return $result;
    }

    public function condition(array $question, array $questions): ?string
    {
        if (! $condition = $question['condition'] ?? null) {
            return null;
        }
        $parent = collect($questions)->firstWhere('id', $condition['question_id']);
        $labels = collect($parent['choices'])->whereIn('value', $condition['values'])->map(fn ($choice) => $this->label($choice['label_key']))->implode(' / ');

        return strtoupper($condition['question_id']).($condition['operator'] === 'not_in' ? ' ≠ ' : ' : ').$labels;
    }
}
