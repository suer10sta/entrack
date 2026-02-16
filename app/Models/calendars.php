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
        'is_finished',
        'start_date',
        'end_date',
        'entreprise_id',
        'user_id',
        'client_id',
        'project_id',
        'task_id',
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

    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function employees()
    {
        return $this->belongsToMany(Employees::class, 'calendar_employee', 'calendar_id', 'employee_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function task()
    {
        return $this->belongsTo(Tasks::class, 'task_id');
    }
}
