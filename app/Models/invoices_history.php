<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class invoices_history extends Model
{
    use HasFactory;

    protected $table = 'invoices_history';
    protected $fillable = [
        'entreprise_id',
        'client_name',
        'link',
        'project_name',
        'start_date',
        'end_date',
        'number',
        'payment_status',
        'first_reminder_date',
        'second_reminder_date',
        'third_reminder_date',
        'archived',
    ];

}
