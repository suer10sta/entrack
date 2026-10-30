<?php

namespace App\Http\Controllers;

use App\Enums\SurveyType;

class SurveyD4Controller extends SurveyPublicController
{
    protected SurveyType $type = SurveyType::D4;

    protected string $ui = 'surveys.d4_ui';
}
