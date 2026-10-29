<?php

namespace App\Enums;

enum SurveyStatus: string
{
    case Sent = 'sent';
    case Q1Clicked = 'q1_clicked';
    case Answered = 'answered';
    case Relaunched = 'relaunched';
    case Expired = 'expired';
    case Cancelled = 'cancelled';
    case Abandon = 'abandon';
}
