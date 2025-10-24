<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class entrepriseCharges extends Model
{
    use HasFactory;

    protected $fillable = [
        'entreprise_id',
        'charge_name',
        'price',
        'validated',
        'Billings_budget_id',
    ];
}
