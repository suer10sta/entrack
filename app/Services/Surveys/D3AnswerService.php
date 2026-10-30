<?php

namespace App\Services\Surveys;

use App\Enums\SurveyType;

class D3AnswerService extends SurveyAnswerService
{
    protected SurveyType $type = SurveyType::D3;

    protected string $ui = 'surveys.d3_ui';

    protected string $actionService = D3ActionService::class;
}
