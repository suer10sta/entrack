<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class client_contacts_devis extends Model
{
    use HasFactory;

    protected $table = 'client_contacts_devis';

    protected $fillable = [
        'devis_id',
        'contact_id',
        'client_id',
        'devis_number',
        'entreprise_id',
    ];
}
