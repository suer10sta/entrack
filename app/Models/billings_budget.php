<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Billings_budget extends Model
{
    use HasFactory;

    protected $table = '_billings_budget';


    protected $fillable = [
        'number',
        'budget',
        'budget_spent',
        'budget_remaining',
        'entreprise_id',
        'sold_out',
    ];
    
}
