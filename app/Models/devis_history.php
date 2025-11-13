<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class devis_history extends Model
{
    use HasFactory;

    protected $table = 'devis_history';

    protected $fillable = [
        'devis_number',
        'client_name',
        'client_id',
        'budget',
        'cost_per_hour',
        'description',
        'emails',
        'entreprise_id',
        'description',
        'tva',
        'hasStartNumber',
        'archived',
        'project_id',
        'Project_name',
        'acceptanceStatus',
        'created_at',
        'updated_at',
        'sent',
        'Existing_project_id',
        'Existing_project_name',
    ];
}

