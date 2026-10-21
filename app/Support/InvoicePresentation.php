<?php

namespace App\Support;

class InvoicePresentation
{
    public static function showReverseChargeNotice($company): bool
    {
        // Business rule: display the notice only for companies based in Belgium.
        return in_array(mb_strtolower(trim((string) $company->country)), [
            'be', 'bel', 'belgium', 'belgique', 'belgië', 'belgie', 'bélgica', 'belgica',
        ], true);
    }
}
