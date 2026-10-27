<?php

namespace App\Enums;

enum ProspectActivityType: string
{
    case Note = 'note';
    case Contact = 'manual_contact';
    case Call = 'call';
    case Email = 'email';
    case Campaign = 'campaign';
    case Demo = 'demo';
    case FollowUp = 'follow_up';
    case Automatic = 'automatic';
}
