<?php

namespace App\Services;

use App\Models\Entreprise;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Mail\InvoicesEmail;

class SubscriptionInvoiceSender
{
    public function __construct(
        protected SubscriptionInvoiceService $invoiceService,
    ) {
    }

    /**
     * Automatically send a subscription invoice after a successful Stripe payment.
     *
     * - Non‑Belgian entreprises: PDF is emailed directly to the entreprise email address.
     * - Belgian entreprises: invoice is sent via Peppol/Billit (no email attachment).
     */
    public function sendAutomatic(Entreprise $entreprise, array $stripeInvoicePayload, ?string $relativePdfPath = null): void
    {
        try {
            // Ensure we have a PDF path (re‑use the generator if needed).
            if (! $relativePdfPath) {
                $relativePdfPath = $this->invoiceService->createFromStripeInvoice($entreprise, $stripeInvoicePayload);
            }

            if (! $relativePdfPath || ! Storage::disk('public')->exists($relativePdfPath)) {
                Log::warning('SubscriptionInvoiceSender: PDF path missing or file not found.', [
                    'entreprise_id' => $entreprise->id,
                    'path' => $relativePdfPath,
                ]);
                return;
            }

            // Decide the automatic sending channel using the TVA number instead of the
            // "country" text field. If the TVA starts with "BE", consider it Belgian
            // for Peppol; otherwise, send by email.
            $tvaNumber = strtoupper(trim((string) ($entreprise->tva ?? '')));
            $isBelgianVat = str_starts_with($tvaNumber, 'BE');

            if ($isBelgianVat) {
                $this->sendViaPeppol($entreprise, $stripeInvoicePayload, $relativePdfPath);
            } else {
                $this->sendViaEmail($entreprise, $stripeInvoicePayload, $relativePdfPath);
            }
        } catch (\Throwable $e) {
            Log::error('SubscriptionInvoiceSender failed', [
                'message' => $e->getMessage(),
                'entreprise_id' => $entreprise->id ?? null,
            ]);
        }
    }

    protected function sendViaEmail(Entreprise $entreprise, array $stripeInvoicePayload, string $relativePdfPath): void
    {
        $recipient = (string) $entreprise->email;

        // Allow overriding the subscription recipient email via the
        // TEST_SUBSCRIPTION_EMAIL environment variable (e.g. for
        // production tests). Make sure to remove it after testing.
        $testRecipient = '';
        if ($testRecipient !== '') {
            Log::info('SubscriptionInvoiceSender: overriding recipient for test.', [
                'entreprise_id' => $entreprise->id,
                'original_recipient' => $recipient,
                'test_recipient' => $testRecipient,
            ]);
            $recipient = $testRecipient;
        }

        if ($recipient === '') {
            Log::warning('SubscriptionInvoiceSender: entreprise has no email, skipping email send.', [
                'entreprise_id' => $entreprise->id,
            ]);
            return;
        }

        $invoiceNumber = (string) ($stripeInvoicePayload['number'] ?? $stripeInvoicePayload['id'] ?? '');
        if ($invoiceNumber === '') {
            Log::warning('SubscriptionInvoiceSender: unable to resolve invoice number for email.', [
                'entreprise_id' => $entreprise->id,
            ]);
            return;
        }

        // Use the display invoice number with suffix (e.g. "INV-001 - 1") for subject and attachment name.
        $displayInvoiceNumber = $this->invoiceService->getDisplayInvoiceNumberForStripe($invoiceNumber) ?: $invoiceNumber;

        $subject = 'Subscription invoice ' . $displayInvoiceNumber;
        $body = "Dear {$entreprise->name},\n\n"
            . "Please find your subscription invoice attached.\n\n"
            . "Best regards,\nDalyTrac";
        $bodyHtml = nl2br(e($body));

        Log::info('SubscriptionInvoiceSender: preparing subscription invoice email.', [
            'entreprise_id' => $entreprise->id,
            'recipient' => $recipient,
            'invoice_number' => $invoiceNumber,
            'display_invoice_number' => $displayInvoiceNumber,
            'pdf_path' => $relativePdfPath,
        ]);

        try {
            // Mirror the normal invoice sending logic: send the mailable and attach the PDF via `InvoicesEmail`.
            // We send separately to each recipient to keep delivery/reporting consistent.
            $copyRecipient = 'Dessin@bureaumercier.com';
            $recipients = array_values(array_unique([$recipient, $copyRecipient]));

            $failedRecipients = [];
            foreach ($recipients as $to) {
                try {
                    Mail::to($to)->send(new InvoicesEmail(
                        $subject,
                        $bodyHtml,
                        null,
                        [$relativePdfPath],
                        null
                    ));
                } catch (\Throwable $e) {
                    $failedRecipients[] = $to;
                    Log::error('SubscriptionInvoiceSender: failed to send subscription invoice email.', [
                        'entreprise_id' => $entreprise->id,
                        'recipient' => $to,
                        'invoice_number' => $invoiceNumber,
                        'display_invoice_number' => $displayInvoiceNumber,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            Log::info('SubscriptionInvoiceSender: subscription invoice email sent successfully.', [
                'entreprise_id' => $entreprise->id,
                'recipient' => $recipient,
                'invoice_number' => $invoiceNumber,
                'display_invoice_number' => $displayInvoiceNumber,
                'failed_recipients' => $failedRecipients,
            ]);
        } catch (\Throwable $e) {
            Log::error('SubscriptionInvoiceSender: failed to send subscription invoice email.', [
                'entreprise_id' => $entreprise->id,
                'recipient' => $recipient,
                'invoice_number' => $invoiceNumber,
                'display_invoice_number' => $displayInvoiceNumber,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function sendViaPeppol(Entreprise $entreprise, array $stripeInvoicePayload, ?string $relativePdfPath = null): void
    {
        if (empty($entreprise->tva)) {
            Log::warning('SubscriptionInvoiceSender: entreprise has no TVA, skipping Peppol send.', [
                'entreprise_id' => $entreprise->id,
            ]);
            return;
        }

        $invoiceNumber = (string) ($stripeInvoicePayload['number'] ?? $stripeInvoicePayload['id'] ?? '');
        if ($invoiceNumber === '') {
            Log::warning('SubscriptionInvoiceSender: unable to resolve invoice number for Peppol.', [
                'entreprise_id' => $entreprise->id,
            ]);
            return;
        }
 
        $createdTs = $stripeInvoicePayload['created'] ?? null;
        $createdAt = $createdTs
            ? Carbon::createFromTimestamp((int) $createdTs, 'UTC')->setTimezone(config('app.timezone'))
            : Carbon::now();

        $invoiceDate = $createdAt->format('Y-m-d');
        // Billit requires an ExpiryDate (due date). For subscriptions, we set it equal to the invoice date.
        $expiryDate = $invoiceDate;

        $customerData = [
            'Name' => $entreprise->name,
            'VATNumber' => (string) ($entreprise->tva ?? ''),
            'PartyType' => 'Customer',
            'Email' => (string) ($entreprise->email ?? ''),
            'Addresses' => [
                [
                    'AddressType' => 'InvoiceAddress',
                    'Name' => $entreprise->name,
                    'Street' => (string) ($entreprise->Address ?? ''),
                    'City' => (string) ($entreprise->city ?? ''),
                    'CountryCode' => 'BE',
                    'PostalCode' => (string) ($entreprise->zip_code ?? ''),
                ],
            ],
        ];

        $amountPaid = (int) ($stripeInvoicePayload['amount_paid'] ?? $stripeInvoicePayload['amount_due'] ?? 0);
        if ($amountPaid <= 0) {
            Log::warning('SubscriptionInvoiceSender: amount_paid is zero, skipping Peppol send.', [
                'entreprise_id' => $entreprise->id,
            ]);
            return;
        }

        $totalTtc = $amountPaid / 100;

        // Use the display invoice number with suffix (e.g. "INV-001 - 1") for attached PDF.
        $displayInvoiceNumber = $this->invoiceService->getDisplayInvoiceNumberForStripe($invoiceNumber) ?: $invoiceNumber;
        [$vatRate, $isZero] = $this->invoiceService->resolveVatRateForEntrepriseForPeppol($entreprise);
        if ($isZero) {
            $vatRate = 0.0;
        }

        $unitPriceExcl = $totalTtc;
        if ($vatRate > 0) {
            $unitPriceExcl = round($totalTtc / (1 + ($vatRate / 100)), 2);
        }

        $orderLines = [
            [
                'Quantity' => 1,
                'UnitPriceExcl' => $unitPriceExcl,
                'Description' => 'Subscription fee',
                'VATPercentage' => $vatRate,
            ],
        ];

        $payload = [
            'OrderType' => 'Invoice',
            'OrderDirection' => 'Income',
            'OrderNumber' => 'SUB-' . $invoiceNumber,
            'OrderDate' => $invoiceDate,
            'ExpiryDate' => $expiryDate,
            'Customer' => $customerData,
            'OrderLines' => $orderLines,
        ];

        // If we have a generated PDF on disk, attach it as OrderPDF so Billit uses it
        // instead of generating its own default PDF.
        try {
            if ($relativePdfPath && Storage::disk('public')->exists($relativePdfPath)) {
                $pdfBinary = Storage::disk('public')->get($relativePdfPath);
                if (is_string($pdfBinary) && strlen($pdfBinary) > 100 && strncmp($pdfBinary, "%PDF-", 5) === 0) {
                    $fileName = "subscription-{$displayInvoiceNumber}.pdf";
                    $payload['OrderPDF'] = [
                        'FileName' => $fileName,
                        'FileContent' => base64_encode($pdfBinary),
                    ];
                }
            }
        } catch (\Throwable $e) {
            Log::warning('SubscriptionInvoiceSender: could not attach PDF for Peppol payload.', [
                'entreprise_id' => $entreprise->id ?? null,
                'relative_path' => $relativePdfPath,
                'error' => $e->getMessage(),
            ]);
        }

        $billit = BillitService::forEntreprise($entreprise);
        if (! $billit->isConfigured()) {
            Log::warning('SubscriptionInvoiceSender: Billit not configured, skipping Peppol send.', [
                'entreprise_id' => $entreprise->id,
            ]);
            return;
        }

        $billit->sendInvoiceViaPeppol($payload, 'Peppol');
    }
}


