<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employees extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phoneNumber',
        'age',
        'cnss',
        'bankAccountNumber',
        'Date_Entree',
        'Date_Release',
        'cost_per_hour',
        'workHours_limit',
        'Cotisation_CNSS',
        'Cotisation_AMO',
        'salary',
        'user_id',
        'entreprise_id',
        'role_id',
        'image',
    ];

    protected static function boot()
    {
        parent::boot();

        // Set default values before creating a new record
        static::creating(function ($employee) {
            $employee->Date_Entree = now(); // Set the current date and time
        });
    }

    public function enterprise()
    {
        return $this->belongsTo(Entreprise::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
