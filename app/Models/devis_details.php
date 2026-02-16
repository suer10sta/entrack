<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class devis_details extends Model
{
    use HasFactory;

    protected $fillable = [
        'devis_id',
        'task_details',
        'hours',
        'cost_per_hour',
        'task_budget',
        'entreprise_id',
    ];
}
