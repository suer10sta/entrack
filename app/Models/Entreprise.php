<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Entreprise extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'image', 'is_activated', 'duration',
    ];
    public function user()
    {
        return $this->hasMany(User::class, 'entreprise_id');
    }
}

