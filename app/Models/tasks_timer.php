<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class tasks_timer extends Model
{
    use HasFactory;
    protected $table = 'tasks_timer';
    protected $fillable = [
        'task_employee_id',
        'start_date',
        'end_date',
        'pause_date',
        'resume_date',
        'entreprise_id',
        'project_id',
        'task_id',
        'employee_id',
        'duration',
        'cost',
        'task_name',
        'comment',
        'paused'
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }
}
