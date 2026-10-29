<?php

namespace App\Enums;

enum ContactSource: string
{
    case Contact = 'contact';
    case D1Survey = 'd1-survey';
    case D2Survey = 'd2-survey';
    case D3Survey = 'd3-survey';
    case D4Survey = 'd4-survey';
    case D5Survey = 'd5-survey';
    case D6Survey = 'd6-survey';
}
