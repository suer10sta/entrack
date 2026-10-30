<?php

namespace App\Services\Surveys;

use App\Enums\SurveyType;

class D5AnswerService extends SurveyAnswerService
{
    protected SurveyType $type = SurveyType::D5;

    protected string $ui = 'surveys.d5_ui';

    protected string $actionService = D5ActionService::class;
}
