<?php

namespace App\Services;

use App\Models\Entreprise;
use App\Models\SubscriptionInvoiceSequence;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SubscriptionInvoiceService
{
    /**
     * Generate and store a custom PDF invoice for a successful Stripe invoice.
     *
     * @param  Entreprise  $entreprise
     * @param  array       $stripeInvoicePayload  The `invoice` object from the Stripe webhook payload.
     * @return string|null The relative storage path to the generated PDF or null on failure.
     */
    public function createFromStripeInvoice(Entreprise $entreprise, array $stripeInvoicePayload): ?string
    {
        try {
            $amountPaid = (int) ($stripeInvoicePayload['amount_paid'] ?? $stripeInvoicePayload['amount_due'] ?? 0);
            $currency = strtoupper($stripeInvoicePayload['currency'] ?? 'EUR');
            $invoiceNumber = (string) ($stripeInvoicePayload['number'] ?? $stripeInvoicePayload['id'] ?? '');

            if ($amountPaid <= 0 || $invoiceNumber === '') {
                return null;
            }

            $totalTtc = $amountPaid / 100;

            [$vatRate, $isZero] = $this->resolveVatRateForEntreprise($entreprise);

            if ($isZero) {
                $vatRate = 0.0;
            }

            if ($vatRate > 0) {
                $amountHt = round($totalTtc / (1 + ($vatRate / 100)), 2);
                $amountVat = round($totalTtc - $amountHt, 2);
            } else {
                $amountHt = $totalTtc;
                $amountVat = 0.0;
            }

            // Compute the display invoice number with global sequential suffix (e.g. "INV-001 - 1").
            $displayInvoiceNumber = $this->getDisplayInvoiceNumberForStripe($invoiceNumber);

            $periodStartTs = $stripeInvoicePayload['lines']['data'][0]['period']['start'] ?? $stripeInvoicePayload['created'] ?? null;
            $periodEndTs = $stripeInvoicePayload['lines']['data'][0]['period']['end'] ?? $stripeInvoicePayload['created'] ?? null;

            $periodStart = $periodStartTs
                ? Carbon::createFromTimestamp((int) $periodStartTs, 'UTC')->setTimezone(config('app.timezone'))
                : null;
            $periodEnd = $periodEndTs
                ? Carbon::createFromTimestamp((int) $periodEndTs, 'UTC')->setTimezone(config('app.timezone'))
                : null;

            // Use the Stripe invoice creation time as the reference date for storage paths
            $createdTs = $stripeInvoicePayload['created'] ?? null;
            $createdAt = $createdTs
                ? Carbon::createFromTimestamp((int) $createdTs, 'UTC')->setTimezone(config('app.timezone'))
                : Carbon::now();

            $appName = config('app.name', 'Ferracad');

            $data = [
                'entreprise'   => $entreprise,
                'invoiceNumber'=> $invoiceNumber,
                // Human-visible invoice number with suffix: "INV-001 - 1", "INV-001 - 2", ...
                'displayInvoiceNumber' => $displayInvoiceNumber,
                'currency'     => $currency,
                'amountHt'     => $amountHt,
                'amountVat'    => $amountVat,
                'amountTtc'    => $totalTtc,
                'vatRate'      => $vatRate,
                'isZeroVat'    => $isZero,
                'periodStart'  => $periodStart,
                'periodEnd'    => $periodEnd,
                'appName'      => $appName,
                'customerVat'  => $entreprise->tva,
                'customerCountry' => $entreprise->country,
                'createdAt'    => $createdAt,
            ];

            // Use the manager-specific invoice template (identical content, different namespace)
            $pdf = Pdf::loadView('manager.subscriptions.invoice', $data);

            $year = $createdAt->format('Y');
            $month = $createdAt->format('m');

            $directory = "stripe_invoices/{$entreprise->id}/{$year}/{$month}";
            $fileName = "subscription-{$invoiceNumber}.pdf";
            $relativePath = "{$directory}/{$fileName}";

            Storage::disk('public')->put($relativePath, $pdf->output());

            return $relativePath;
        } catch (\Throwable $e) {
            Log::error('Failed to create subscription invoice PDF: ' . $e->getMessage(), [
                'entreprise_id' => $entreprise->id ?? null,
            ]);

            return null;
        }
    }

    /**
     * Compute or retrieve the persistent global sequential suffix for
     * a given Stripe invoice number.
     *
     * Example: "OITHAPDZ-0006" -> 1
     */
    public function getInvoiceSequenceForStripe(string $invoiceNumber): int
    {
        $invoiceNumber = trim((string) $invoiceNumber);
        if ($invoiceNumber === '') {
            return 1;
        }

        $invoiceSequence = 1;

        try {
            DB::beginTransaction();

            $sequenceRow = SubscriptionInvoiceSequence::where('stripe_invoice_number', $invoiceNumber)
                ->lockForUpdate()
                ->first();

            if (! $sequenceRow) {
                $sequenceRow = SubscriptionInvoiceSequence::create([
                    'stripe_invoice_number' => $invoiceNumber,
                ]);
            }

            $invoiceSequence = (int) $sequenceRow->id;

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            // Fallback: keep default sequence = 1 if anything goes wrong.
        }

        return $invoiceSequence;
    }

    /**
     * Compute or retrieve the human-visible invoice number with a persistent
     * global sequential suffix for a given Stripe invoice number.
     *
     * Example: "OITHAPDZ-0006" -> "OITHAPDZ-0006 - 1"
     */
    public function getDisplayInvoiceNumberForStripe(string $invoiceNumber): string
    {
        $invoiceNumber = trim((string) $invoiceNumber);
        if ($invoiceNumber === '') {
            return '';
        }

        $invoiceSequence = $this->getInvoiceSequenceForStripe($invoiceNumber);

        return trim($invoiceNumber . ' - ' . $invoiceSequence);
    }

    /**
     * Resolve the applicable VAT rate for a subscription invoice.
     *
     * We (BMe, Belgium) are the seller, so the VAT rate is always the Belgian
     * standard rate (21%), regardless of the customer's country. The customer's
     * country is only used to determine the reverse-charge (autoliquidation)
     * flag, which only applies to customers that hold a valid VAT number.
     *
     * Returns [rate, isZeroFlag].
     */
    public function resolveVatRateForEntreprise(Entreprise $entreprise): array
    {
        $countries = config('vat_countries', []);

        $rate = 21.0;
        $isZero = false;

        // Autoliquidation (reverse charge) only applies to customers that
        // have a VAT number.
        $tvaNumber = (string) ($entreprise->tva ?? '');
        $tvaNumberUpper = strtoupper(trim($tvaNumber));

        if ($tvaNumberUpper === '') {
            return [$rate, $isZero];
        }

        // 1) Prefer the country code embedded in the VAT number
        //    (e.g. "BE", "FR", "DE", ...).
        $tvaCountryCode = substr($tvaNumberUpper, 0, 2);
        if (ctype_alpha($tvaCountryCode)) {
            foreach ($countries as $country) {
                if (strtoupper($country['code'] ?? '') === $tvaCountryCode) {
                    $isZero = (bool) ($country['isZero'] ?? false);

                    return [$rate, $isZero];
                }
            }
        }

        // 2) Fallback: resolve the country from the free-text "country" field.
        $countryValue = (string) ($entreprise->country ?? '');
        $countryValueUpper = strtoupper($countryValue);
        $countryValueLower = mb_strtolower($countryValue);

        // Try by ISO code first (if the stored country looks like a code).
        if (strlen($countryValueUpper) === 2) {
            foreach ($countries as $country) {
                if (strtoupper($country['code'] ?? '') === $countryValueUpper) {
                    $isZero = (bool) ($country['isZero'] ?? false);

                    return [$rate, $isZero];
                }
            }
        }

        // Fallback: match by localized country name (as stored in your JSON).
        foreach ($countries as $country) {
            $configuredName = (string) ($country['name'] ?? '');
            if (mb_strtolower($configuredName) === $countryValueLower) {
                $isZero = (bool) ($country['isZero'] ?? false);

                return [$rate, $isZero];
            }
        }

        // Unknown country: keep the Belgian rate, no reverse charge.
        return [$rate, $isZero];
    }

    /**
     * Public helper specifically for Peppol-related flows that need VAT rate.
     */
    public function resolveVatRateForEntrepriseForPeppol(?Entreprise $entreprise): array
    {
        if (! $entreprise) {
            return [0.0, false];
        }

        return $this->resolveVatRateForEntreprise($entreprise);
    }
}

