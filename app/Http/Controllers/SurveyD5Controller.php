<?php

namespace App\Http\Controllers;

use App\Enums\SurveyType;

class SurveyD5Controller extends SurveyPublicController
{
    protected SurveyType $type = SurveyType::D5;

    protected string $ui = 'surveys.d5_ui';
}
