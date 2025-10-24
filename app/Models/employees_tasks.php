<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class employees_tasks extends Model
{
    use HasFactory;
    protected $fillable = [
        'employee_id',
        'employee_name',
        'task_id',
        'task_name',
        'start_date',
        'end_date',
        'entreprise_id', 
    ];
}
