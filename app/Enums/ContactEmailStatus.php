<?php

namespace App\Enums;

enum ContactEmailStatus: string
{
    case Pending = 'pending';
    case Sent = 'sent';
    case Failed = 'failed';
    case Uncertain = 'uncertain';
}
