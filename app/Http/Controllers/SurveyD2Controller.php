<?php

namespace App\Http\Controllers;

use App\Enums\SurveyType;

class SurveyD2Controller extends SurveyPublicController
{
    protected SurveyType $type = SurveyType::D2;

    protected string $ui = 'surveys.d2_ui';
}
