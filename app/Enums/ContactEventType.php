<?php

namespace App\Enums;

enum ContactEventType: string
{
    case Created = 'created';
    case Replied = 'replied';
    case Closed = 'closed';
    case Reopened = 'reopened';
    case EmailFailed = 'email_failed';
}
