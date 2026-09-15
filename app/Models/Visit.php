<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Visit extends Model
{
    use HasFactory;

    protected $fillable = [
        'ip',
        'country',
        'country_code',
        'city',
        'url',
        'user_agent',
        'referrer',
        'utm_source',
        'device_type',
        'is_proxy',
        'is_hosting',
        'last_activity_at',
        'ended_at',
        'duration_seconds',
    ];
}


