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
    case ActionsProcessed = 'ACTIONS_PROCESSED';
    case ProjectFollowUp = 'PROJECT_FOLLOW_UP';
    case TrialExtensionRequested = 'TRIAL_EXTENSION_REQUESTED';
    case InsufficientValue = 'INSUFFICIENT_VALUE';
    case CompetitorChosen = 'COMPETITOR_CHOSEN';
    case PurchaseIntent = 'PURCHASE_INTENT';
    case PriceBarrier = 'PRICE_BARRIER';
    case DecisionApproval = 'DECISION_APPROVAL';
    case ValueConfirmed = 'VALUE_CONFIRMED';
}
