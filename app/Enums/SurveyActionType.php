<?php

namespace App\Enums;

enum SurveyActionType: string
{
    case Sent = 'SENT';
    case ReminderSent = 'REMINDER_SENT';
    case Q1Clicked = 'Q1_CLICKED';
    case Answered = 'ANSWERED';
    case Ticket = 'TICKET';
    case AutoEmail = 'AUTO_EMAIL';
    case Upsell = 'UPSELL';
    case StopCommercial = 'STOP_COMMERCIAL';
    case RecallDone = 'RECALL_DONE';
    case Cancelled = 'CANCELLED';
    case EmailAttempted = 'EMAIL_ATTEMPTED';
    case EmailUnconfirmed = 'EMAIL_UNCONFIRMED';
}
