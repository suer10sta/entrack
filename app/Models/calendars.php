<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class calendars extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'color',
        'start_date',
        'end_date',
        'entreprise_id'
    ];

    // Cast dates to Carbon instances
    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    protected $dates = [
        'start_date',
        'end_date'
    ];
}
