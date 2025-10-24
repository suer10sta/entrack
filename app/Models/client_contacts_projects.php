<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class client_contacts_projects extends Model
{
    use HasFactory;
    protected $fillable = [
        'client_id',
        'contact_name',
        'contact_id',
        'project_name',
        'project_id',
    ];

}
