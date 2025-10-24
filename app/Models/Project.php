<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'description',
        'budget',
        'cost_per_hour',
        'start_date',
        'end_date',
        'status',
        'team_id',
        'client_id',
        'entreprise_id',
        'estimated_hours',
        'archived'
    ];
}
