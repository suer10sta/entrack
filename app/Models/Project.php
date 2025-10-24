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
        'archived',
        'tva'
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function teams()
    {
        return $this->belongsToMany(Teams::class, 'project_teams', 'project_id', 'team_id');
    }

    public function timers()
    {
        return $this->hasMany(tasks_timer::class, 'project_id');
    }
}
