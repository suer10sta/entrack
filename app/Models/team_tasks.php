<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Teams;

class team_tasks extends Model
{
    use HasFactory;
    protected $fillable = [
        'task_name',
        'team_id',
        'task_id',
        'entreprise_id'
    ];
    public function team()
    {
        return $this->belongsTo(Teams::class, 'team_id');
    }
}
