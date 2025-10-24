<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class project_teams extends Model
{
    use HasFactory;
    protected $fillable = [
        'team_name',
        'team_id',
        'project_id',
        'entreprise_id'
    ];
}
