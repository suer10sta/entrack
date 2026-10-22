<?php

namespace App\Support;

use App\Models\Entreprise;
use App\Models\invoices_history;
use Carbon\Carbon;

class InvoiceNumbering
{
    public const YEAR_MONTH_NUMBER = 'year_month_number';
    public const NUMBER_ONLY = 'number_only';

    public static function compare(string $a, string $b): int
    {
        $a = ltrim($a, '0') ?: '0';
        $b = ltrim($b, '0') ?: '0';
        return strlen($a) <=> strlen($b) ?: strcmp($a, $b);
    }

    public static function increment(string $number): string
    {
        for ($i = strlen($number) - 1; $i >= 0; $i--) {
            if ($number[$i] !== '9') {
                $number[$i] = (string) ((int) $number[$i] + 1);
                return $number;
            }
            $number[$i] = '0';
        }
        return '1'.$number;
    }

    public static function next(Entreprise $company): string
    {
        // Keep one continuous company sequence, including cancelled/credited invoices.
        $invoices = invoices_history::on($company->getConnectionName())
            ->where('entreprise_id', $company->id)->get(['number', 'created_at', 'invoice_reference']);
        $numbers = $invoices->pluck('number')
            ->map(fn ($number) => (string) $number)
            ->filter(fn ($number) => ctype_digit($number));
        $start = (string) ($company->invoice_start_number ?? '1');
        if (!ctype_digit($start)) $start = '1';
        $next = $start;
        if ($numbers->isNotEmpty()) {
            $last = $numbers->sort(fn ($a, $b) => self::compare($b, $a))->first();
            $next = self::increment($last);
            if (self::compare($start, $next) > 0) $next = $start;
            $next = str_pad($next, max(strlen($last), strlen($start)), '0', STR_PAD_LEFT);
        }

        // A format switch or a manually advanced sequence must not collide with
        // an earlier full reference, including historical year/month references.
        $references = $invoices->map(fn ($invoice) => self::reference($company, $invoice, true));
        while ($references->containsStrict(self::preview($company, $next))) {
            $next = self::increment($next);
        }
        return $next;
    }

    public static function preview($company, $number, $date = null): string
    {
        $prefix = filled($company->invoice_prefix ?? null) ? $company->invoice_prefix : 'F';
        $period = ($company->invoice_number_format ?? self::YEAR_MONTH_NUMBER) === self::NUMBER_ONLY
            ? '' : Carbon::parse($date ?? now())->format('ym');
        return $prefix.'-'.$period.$number;
    }

    /** Scalar = unsaved preview. Historical objects retain the caller's old display convention. */
    public static function reference($company, $invoiceOrNumber, bool $legacyYearMonth = false): string
    {
        if (!is_object($invoiceOrNumber)) return self::preview($company, $invoiceOrNumber);
        if (filled($invoiceOrNumber->invoice_reference ?? null)) return $invoiceOrNumber->invoice_reference;
        $period = $legacyYearMonth ? Carbon::parse($invoiceOrNumber->created_at ?? now())->format('ym') : '';
        return ($company->invoice_prefix ?? '').'-'.$period.$invoiceOrNumber->number;
    }
}
