<?php

namespace App\Enums;

enum ContactStatus: string
{
    case Pending = 'pending';
    case Replied = 'replied';
    case Closed = 'closed';
}
