<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;
    protected $fillable = [
        'parent_id',
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
        'tva',
        'regie'
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

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function subprojects()
    {
        return $this->hasMany(self::class, 'parent_id');
    }
}
