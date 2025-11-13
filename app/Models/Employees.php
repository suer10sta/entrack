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
        'image',
        'user_id',
        'status',   
        'salary',
        'Cotisation_CNSS',
        'Cotisation_AMO',
        'entreprise_id',
        'workHours_limit',
        'cost_per_hour',
        'archived',
        'contact_email',
        
    ];

    
    protected $casts = [
        'Date_Entree' => 'datetime',
        'Date_Release' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($employee) {
            $employee->Date_Entree = now(); 
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

    public function teams()
    {
        return $this->belongsToMany(Teams::class, 'team_members', 'employee_id', 'team_id');
    }

    public function activeTaskTimer()
    {
        return $this->hasOne(tasks_timer::class, 'employee_id')->where('paused', 0);
    }

    public function timers()
    {
        return $this->hasMany(tasks_timer::class, 'employee_id');
    }
}
