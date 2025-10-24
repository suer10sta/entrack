<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class note_de_frais extends Model
{
    use HasFactory;

    protected $table = 'note_de_frais'; 

    protected $fillable = [
        'note_number',
        'employee_name',
        'description',
        'budget',
        'entreprise_id'
    ];
}
