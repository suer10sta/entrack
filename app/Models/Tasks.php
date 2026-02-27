<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tasks extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'start',
        'end',
        'description',
        'entreprise_id',
        'user_id',
        'project_id',
        'project_name',
        'status',
        'estimated_hours',
        'consumed_hours',
    ];
}
