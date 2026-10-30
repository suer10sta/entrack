<?php

namespace App\Http\Controllers;

use App\Enums\SurveyType;

class SurveyD3Controller extends SurveyPublicController
{
    protected SurveyType $type = SurveyType::D3;

    protected string $ui = 'surveys.d3_ui';
}
