<?php

namespace App\Http\Controllers;

use App\Enums\SurveyType;

class SurveyD6Controller extends SurveyPublicController
{
    protected SurveyType $type = SurveyType::D6;

    protected string $ui = 'surveys.d6_ui';
}
