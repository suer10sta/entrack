<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class invoices_history extends Model
{
    use HasFactory;

    protected $table = 'invoices_history';
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
        'payment_status',
        'sentreminder',
        'sentInvoice',
        'entreprise_id',
        'duration',
        'total_htva',
        'total_tva',
        'total_ttc',
        'hasCreditNote',
        'comment',
        'percentage',
        'partial',
        'first_reminder_date',
        'second_reminder_date',
        'third_reminder_date',
        'archived',
        'hours',
        'details',
        'peppol_status',
        'peppol_message_id',
        'peppol_error',
        'peppol_sent_at',
        'devis_id',
        'payment_date',
    ];

}
