<?php

namespace App\Services\Surveys;

use App\Enums\SurveyType;
use App\Models\SurveyResponse;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;

class D6AnswerService extends SurveyAnswerService
{
    protected SurveyType $type = SurveyType::D6;

    protected string $ui = 'surveys.d6_ui';

    protected string $actionService = D6ActionService::class;

    public function submit(SurveyResponse $survey, array $input, bool $confirmOnly): SurveyResponse
    {
        $result = DB::transaction(function () use ($survey, $input, $confirmOnly) {
            $fresh = SurveyResponse::lockForUpdate()->findOrFail($survey->id);
            if ($fresh->survey_type !== SurveyType::D6) {
                throw new HttpException(403, 'invalid_link');
            }
            if (! app(D6StageGuard::class)->allows($fresh)) {
                return null;
            }

            return parent::submit($fresh, $input, $confirmOnly);
        });
        // Commit cancellation before returning the closed response, without saving answers.
        if (! $result) {
            throw new HttpException(410, 'closed');
        }

        return $result;
    }
}
