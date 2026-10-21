<?php

namespace App\Support;

use App\Models\Entreprise;
use App\Models\invoices_history;

class InvoiceNumbering
{
    public static function next(Entreprise $company): string
    {
        // Keep one continuous company sequence, including cancelled/credited invoices.
        $numbers = invoices_history::on($company->getConnectionName())
            ->where('entreprise_id', $company->id)->pluck('number')
            ->map(fn ($number) => (string) $number)
            ->filter(fn ($number) => ctype_digit($number));
        $start = (string) ($company->invoice_start_number ?? '1');
        if (!ctype_digit($start)) $start = '1';
        if ($numbers->isEmpty()) return $start;

        $last = $numbers->sortByDesc(fn ($number) => (int) $number)->first();
        return str_pad((string) ((int) $last + 1), strlen($last), '0', STR_PAD_LEFT);
    }
}
