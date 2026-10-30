<?php

namespace App\Http\Controllers;

use App\Enums\SurveyType;
use App\Services\Surveys\D1AnswerService;
use App\Services\Surveys\SurveyCatalog;
use App\Services\Surveys\SurveyLink;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class SurveyPublicController extends Controller
{
    protected SurveyType $type = SurveyType::D1;

    protected string $ui = 'surveys.ui';

    public function __invoke(Request $request, SurveyLink $links, SurveyCatalog $catalog, \App\Services\Surveys\SurveyAnswerService $answers)
    {
        $answers = app($this->type === SurveyType::D1 ? D1AnswerService::class : \App\Services\Surveys\D2AnswerService::class);
        view()->share(['surveyType' => $this->type->value, 'surveyUi' => $this->ui]);
        $headers = ['Cache-Control' => 'no-store, private', 'Referrer-Policy' => 'no-referrer', 'X-Robots-Tag' => 'noindex, nofollow'];
        try {
            $survey = $links->resolve($request, $request->route('survey'), $request->route('token'), $this->type);
            $links->assertOpen($survey);
            $questions = $catalog->definition($this->type, $survey->survey_version)['questions'];
            $input = $request->isMethod('POST') ? $request->input('answers', []) : [];
            if (! is_array($input)) {
                throw ValidationException::withMessages(['answers' => __($this->ui.'.invalid_answer')]);
            }
            $errors = [];
            if ($request->isMethod('POST') && $request->input('intent') !== 'preview') {
                try {
                    $survey = $answers->submit($survey, $input, $request->input('intent') === 'confirm_q1');
                    if ($survey->completed_at) {
                        return response()->view('surveys.d1', ['finished' => true], 200, $headers);
                    }
                } catch (ValidationException $exception) {
                    $errors = $exception->errors();
                }
            }
            $persisted = $survey->answers()->pluck('value', 'question_id')->all();
            $q1Values = array_column(collect($questions)->firstWhere('id', 'q1')['choices'], 'value');
            $selected = $request->query('q1');
            $prefill = is_string($selected) && in_array($selected, $q1Values, true) ? ['q1' => $selected] : [];
            $values = array_replace($prefill, $input, $persisted);
            // Never put unvalidated array/object inputs back into HTML controls.
            $values = array_filter($values, 'is_string');

            return response()->view('surveys.d1', [
                'questions' => $questions, 'catalog' => $catalog, 'values' => $values,
                'visible' => array_keys($answers->applicable($questions, $values)), 'formErrors' => $errors,
                'locked' => isset($persisted['q1']), 'action' => $request->fullUrl(),
                'lockedNotice' => isset($persisted['q1']) && $selected !== null && $selected !== $persisted['q1'],
            ], $errors ? 422 : 200, $headers);
        } catch (ValidationException $exception) {
            return response()->view('surveys.d1', ['failure' => 'invalid_answer'], 422, $headers);
        } catch (HttpException $exception) {
            return response()->view('surveys.d1', ['failure' => $exception->getMessage()], $exception->getStatusCode(), $headers);
        }
    }
}
