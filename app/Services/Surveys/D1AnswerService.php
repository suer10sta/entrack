<?php

namespace App\Services\Surveys;

use App\Enums\SurveyActionType;
use App\Enums\SurveyStatus;
use App\Enums\SurveyType;
use App\Models\SurveyResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpKernel\Exception\HttpException;

class D1AnswerService
{
    public function __construct(private SurveyCatalog $catalog, private SurveyLink $links) {}

    public function applicable(array $questions, array $answers): array
    {
        $visible = [];
        foreach ($questions as $question) {
            $condition = $question['condition'];
            if ($condition) {
                $parent = $condition['question_id'];
                if (! isset($visible[$parent], $answers[$parent]) || ! is_string($answers[$parent]) || $answers[$parent] === '') {
                    continue;
                }
                $matches = in_array($answers[$parent], $condition['values'], true);
                if (($condition['operator'] === 'in' && ! $matches) || ($condition['operator'] === 'not_in' && $matches)) {
                    continue;
                }
            }
            $visible[$question['id']] = $question;
        }

        return $visible;
    }

    public function submit(SurveyResponse $survey, array $input, bool $confirmOnly): SurveyResponse
    {
        return DB::transaction(function () use ($survey, $input, $confirmOnly) {
            $fresh = SurveyResponse::lockForUpdate()->findOrFail($survey->id);
            $this->links->assertOpen($fresh);
            if ($fresh->survey_type !== SurveyType::D1) {
                throw new HttpException(403, 'invalid_link');
            }
            $questions = $this->catalog->definition(SurveyType::D1, $fresh->survey_version)['questions'];
            $q1 = collect($questions)->firstWhere('id', 'q1');
            $rules = ['q1' => ['required', 'string', \Illuminate\Validation\Rule::in(array_column($q1['choices'], 'value'))]];
            $messages = [
                'required' => __('surveys.ui.required'), 'string' => __('surveys.ui.invalid_answer'),
                'in' => __('surveys.ui.invalid_answer'), 'max' => __('surveys.ui.too_long'),
            ];
            Validator::make($input, $rules, $messages)->validate();
            $first = $fresh->answers()->where('question_id', 'q1')->first();
            if ($first && $first->value !== $input['q1']) {
                throw new HttpException(409, 'q1_locked');
            }
            $answers = ['q1' => $input['q1']];
            if (! $confirmOnly) {
                foreach ($this->applicable($questions, $input) as $id => $question) {
                    if ($id === 'q1') {
                        continue;
                    }
                    $rule = ['nullable', 'string'];
                    $rule[] = $question['type'] === 'text'
                        ? 'max:2000' : \Illuminate\Validation\Rule::in(array_column($question['choices'], 'value'));
                    Validator::make([$id => $input[$id] ?? null], [$id => $rule], $messages)->validate();
                    if (isset($input[$id]) && $input[$id] !== '') {
                        $answers[$id] = $input[$id];
                    }
                }
            }
            if (! $first) {
                $fresh->answers()->create(['question_id' => 'q1', 'value' => $answers['q1']]);
                $fresh->clicked_at = now('UTC');
                if ($fresh->status !== SurveyStatus::Relaunched) {
                    $fresh->status = SurveyStatus::Q1Clicked;
                }
                $fresh->actions()->create(['action_type' => SurveyActionType::Q1Clicked, 'action_key' => 'q1:first']);
            }
            if (! $confirmOnly) {
                foreach ($answers as $id => $value) {
                    if ($id !== 'q1') {
                        $fresh->answers()->create(['question_id' => $id, 'value' => $value]);
                    }
                }
                $fresh->status = $answers['q1'] === 'abandon' ? SurveyStatus::Abandon : SurveyStatus::Answered;
                $fresh->completed_at = now('UTC');
                $fresh->actions()->create(['action_type' => SurveyActionType::Answered,
                    'action_key' => 'answered:final', 'detail' => 'status='.$fresh->status->value]);
            }
            $fresh->save();

            return $fresh;
        });
    }
}
