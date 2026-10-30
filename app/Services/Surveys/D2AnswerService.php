<?php

namespace App\Services\Surveys;

use App\Enums\SurveyType;

class D2AnswerService extends SurveyAnswerService
{
    protected SurveyType $type = SurveyType::D2;

    protected string $ui = 'surveys.d2_ui';

    protected string $actionService = D2ActionService::class;
}
