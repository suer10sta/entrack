<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class credit_note extends Model
{
    use HasFactory;

    protected $table = 'credit_note';
    protected $fillable = [
        'project_name',
        'number',
        'link',
        'client_id',
        'project_id',
        'due_date',
        'end_date',
        'start_date',
        'type',
        'Payment_status',
        'sentReminder',
        'sentInvoice',
        'duration',
        'total_htva',
        'total_tva',
        'total_ttc',
        'credit_note_type',
        'entreprise_id',
        'credit_note_number',
        'invoice_id',
        'emailsent',
    ];
}
