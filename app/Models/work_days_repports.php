<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class work_days_repports extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_days_per_month',
        'days_worked',
        'sick_days',
        'holidays',
        'days_of_unpaid_absence',
        'remaining_days_of_paid_leave',
        'number_of_days_of_paid_leave_acquired',
        'number_of_days_of_paid_leave_taken',
        'holiday_balance',
        'days_of_holidays',
        'remarque',
        'month',
        'year',
        'employee_id',
        'entreprise_id',
    ];
}
