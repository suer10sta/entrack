<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class invoices_history extends Model
{
    use HasFactory;

    protected $table = 'invoices_history';

    public function save(array $options = [])
    {
        if ($this->exists || !$this->entreprise_id) return parent::save($options);

        // Allocate and insert under the same company lock: parallel creations
        // must not both consume the same next number.
        return $this->getConnection()->transaction(function () use ($options) {
            $company = Entreprise::on($this->getConnectionName())
                ->whereKey($this->entreprise_id)->lockForUpdate()->firstOrFail();
            $this->number = \App\Support\InvoiceNumbering::next($company);
            $this->invoice_reference = \App\Support\InvoiceNumbering::preview($company, $this->number, $this->created_at);
            $saved = parent::save($options);
            if ($saved) {
                $company->invoice_start_number = \App\Support\InvoiceNumbering::increment($this->number);
                $company->save();
            }
            return $saved;
        });
    }
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
