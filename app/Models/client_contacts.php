<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class client_contacts extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'functionality',
        'mobile_fix',
        'email',
        'address',
        'client_id',
        'entreprise_id',
    ];
}
