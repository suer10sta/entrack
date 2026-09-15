<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionInvoiceSequence extends Model
{
    use HasFactory;

    protected $table = 'subscription_invoice_sequences';

    protected $fillable = [
        'stripe_invoice_number',
    ];
}


