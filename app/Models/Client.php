<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'client_number',
        'TVA',
        'due_date',
        'billing_street',
        'billing_city',
        'billing_country',
        'billing_zip',        
        'email',
        'phone',
        'address',
        'city',
        'zip',
        'country',
        'entreprise_id',
    ];
}
