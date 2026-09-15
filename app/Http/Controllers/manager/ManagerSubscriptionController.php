<?php

namespace App\Http\Controllers\manager;

use App\Http\Controllers\Controller;
use App\Models\Entreprise;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Cashier;
use App\Services\SubscriptionInvoiceService;
use App\Services\BillitService;
use App\Mail\InvoicesEmail;
use Illuminate\Support\Facades\File;
use setasign\Fpdi\Fpdi;

class ManagerSubscriptionController extends Controller
{
    public function index(Request $request)
    {
        $allowedPlans = ['solo', 'premium', 'pro'];
        $activePlan = $request->query('plan');
        $activePlan = in_array($activePlan, $allowedPlans, true) ? $activePlan : null;
        $status = $request->query('status');
        $activeStatus = $status === 'active' ? 'active' : null;

        // Eager-load subscriptions to avoid N+1 queries when showing plan end dates in the table.
        $entreprisesQuery = Entreprise::with(['user', 'subscriptions']);
        if ($activePlan) {
            $entreprisesQuery->where('type_compt', $activePlan);
        }

        if ($activeStatus === 'active') {
            // Restrict to entreprises that have an active subscription (database-level approximation)
            $activeStatuses = ['active', 'trialing', 'past_due', 'unpaid'];

            $entreprisesQuery
                ->whereNotNull('type_compt')
                ->where(function ($query) {
                    $query->whereNull('account_status')
                        ->orWhere('account_status', '!=', 'inactive');
                })
                ->whereHas('subscriptions', function ($subQuery) use ($activeStatuses) {
                    $subQuery->whereIn('stripe_status', $activeStatuses);
                });
        }

        $entreprises = $entreprisesQuery
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $subscriptionStatusByEntrepriseId = [];
        $planEndsAtByEntrepriseId = [];
        $now = now();

        foreach ($entreprises as $entreprise) {
            $subscription = $entreprise->subscription('default');
            if (!$subscription && $entreprise->relationLoaded('subscriptions')) {
                // Fallback for legacy records where subscription type is not "default".
                $subscription = $entreprise->subscriptions
                    ->sortByDesc(fn ($item) => optional($item->created_at)->timestamp ?? 0)
                    ->first();
            }

            $stripeSubscription = null;
            $periodEndAt = null;
            $isActive = false;

            if ($subscription) {
                try {
                    $stripeSubscription = $subscription->asStripeSubscription();
                    $periodEndAt = $this->resolveStripePeriodEnd($stripeSubscription);
                } catch (\Throwable $e) {
                    // Keep null and try Stripe customer lookup fallback below.
                }
            }

            if (!$stripeSubscription && $entreprise->stripe_id) {
                try {
                    $stripeSubscription = $this->findLatestStripeSubscription($entreprise->stripe_id);
                } catch (\Throwable $e) {
                    // Keep UI resilient if Stripe API is unavailable.
                }
            }

            if ($stripeSubscription) {
                $periodEndAt = $this->resolveStripePeriodEnd($stripeSubscription);
            }

            if ($stripeSubscription) {
                $isActive = in_array((string) $stripeSubscription->status, [
                    'active',
                    'trialing',
                    'past_due',
                    'unpaid',
                ], true) || ($periodEndAt && $now->lessThanOrEqualTo($periodEndAt));
            }

            if (method_exists($entreprise, 'isBlockingExempt') && $entreprise->isBlockingExempt()) {
                $isActive = true;
            }

            $subscriptionStatusByEntrepriseId[$entreprise->id] = $isActive;
            $planEndsAtByEntrepriseId[$entreprise->id] = $periodEndAt;
        }

        $planCounts = [
            'solo' => Entreprise::where('type_compt', 'solo')->count(),
            'premium' => Entreprise::where('type_compt', 'premium')->count(),
            'pro' => Entreprise::where('type_compt', 'pro')->count(),
        ];

        $estimatedMonthlyRevenue = 0.0;
        try {
            $estimatedMonthlyRevenue = $this->estimateMonthlyRevenueFromStripe();
        } catch (\Throwable $e) {
            // Keep manager page usable if Stripe is unavailable.
            $estimatedMonthlyRevenue = 0.0;
        }

        return view('manager.subscriptions.index', [
            'entreprises' => $entreprises,
            'planCounts' => $planCounts,
            'estimatedMonthlyRevenue' => $estimatedMonthlyRevenue,
            'activePlan' => $activePlan,
            'activeStatus' => $activeStatus,
            'subscriptionStatusByEntrepriseId' => $subscriptionStatusByEntrepriseId,
            'planEndsAtByEntrepriseId' => $planEndsAtByEntrepriseId,
        ]);
    }

    public function show(Entreprise $entreprise)
    {
        // Uses Cashier helpers to fetch invoices from Stripe
        $invoices = [];
        if ($entreprise->stripe_id) {
            try {
                $invoices = $entreprise->invoicesIncludingPending();
            } catch (\Throwable $e) {
                // Keep UI working even if Stripe is misconfigured
                $invoices = [];
            }
        }

        $subscription = $entreprise->subscription('default');
        $stripePlanEndsAt = null;
        $stripeAccountIsActive = false;
        $hasCurrentStripeSubscription = false;
        $stripeSubscriptionId = null;
        $stripeSubscriptionStatus = null;
        $stripeSubscriptionCreatedAt = null;

        try {
            $stripeSubscription = $subscription
                ? $subscription->asStripeSubscription()
                : ($entreprise->stripe_id ? $this->findLatestStripeSubscription($entreprise->stripe_id) : null);

            if ($stripeSubscription) {
                $hasCurrentStripeSubscription = true;
                $stripeSubscriptionId = (string) ($stripeSubscription->id ?? '');
                $stripeSubscriptionStatus = (string) ($stripeSubscription->status ?? '');

                if (!empty($stripeSubscription->created)) {
                    $stripeSubscriptionCreatedAt = Carbon::createFromTimestamp(
                        (int) $stripeSubscription->created,
                        'UTC'
                    )->setTimezone(config('app.timezone'));
                }

                $stripePlanEndsAt = $this->resolveStripePeriodEnd($stripeSubscription);

                $stripeAccountIsActive = in_array((string) $stripeSubscription->status, [
                    'active',
                    'trialing',
                    'past_due',
                    'unpaid',
                ], true) || ($stripePlanEndsAt && now()->lessThanOrEqualTo($stripePlanEndsAt));
            }
        } catch (\Throwable $e) {
            // Keep detail page working even if Stripe call fails.
        }

        if (method_exists($entreprise, 'isBlockingExempt') && $entreprise->isBlockingExempt()) {
            $stripeAccountIsActive = true;
        }

        return view('manager.subscriptions.show', [
            'entreprise' => $entreprise,
            'subscription' => $subscription,
            'invoices' => $invoices,
            'stripePlanEndsAt' => $stripePlanEndsAt,
            'stripeAccountIsActive' => $stripeAccountIsActive,
            'hasCurrentStripeSubscription' => $hasCurrentStripeSubscription,
            'stripeSubscriptionId' => $stripeSubscriptionId,
            'stripeSubscriptionStatus' => $stripeSubscriptionStatus,
            'stripeSubscriptionCreatedAt' => $stripeSubscriptionCreatedAt,
            // Used by the "Send via Email" modal recipient selection
            'subscriptionInvoiceCopyRecipient' => 'Dessin@bureaumercier.com',
        ]);
    }

    /**
     * Download a custom PDF subscription invoice generated on Stripe invoice payment.
     */
    public function downloadInvoice(Entreprise $entreprise, string $invoiceId)
    {
        // Ensure the entreprise actually belongs to the Stripe customer we expect
        if (! $entreprise->stripe_id) {
            abort(404);
        }

        try {
            $stripe = Cashier::stripe();
            $stripeInvoice = $stripe->invoices->retrieve($invoiceId, []);
        } catch (\Throwable $e) {
            abort(404);
        }

        // Use the invoice number if available, otherwise fall back to the ID
        $invoiceNumber = (string) ($stripeInvoice->number ?? $stripeInvoice->id ?? '');
        if ($invoiceNumber === '') {
            abort(404);
        }

        // Use the invoice creation date to reconstruct the storage path
        $createdAt = isset($stripeInvoice->created)
            ? Carbon::createFromTimestamp((int) $stripeInvoice->created, 'UTC')->setTimezone(config('app.timezone'))
            : now();

        $year = $createdAt->format('Y');
        $month = $createdAt->format('m');

        $relativePath = "stripe_invoices/{$entreprise->id}/{$year}/{$month}/subscription-{$invoiceNumber}.pdf";

        // If the PDF does not exist yet, try to generate it on the fly using the same
        // service that is used by the sendInvoice() action.
        if (! Storage::disk('public')->exists($relativePath)) {
            try {
                if (method_exists($stripeInvoice, 'toArray')) {
                    $payload = $stripeInvoice->toArray();
                } else {
                    $payload = json_decode(json_encode($stripeInvoice), true) ?? [];
                }

                app(SubscriptionInvoiceService::class)->createFromStripeInvoice($entreprise, $payload);
            } catch (\Throwable $e) {
                // If regeneration fails we keep the 404 behaviour below.
            }
        }

        if (! Storage::disk('public')->exists($relativePath)) {
            abort(404);
        }

        // Build the download file name using the Stripe invoice number plus
        // its global suffix, e.g. "OITHAPDZ-0006-1.pdf".
        $invoiceSequence = app(SubscriptionInvoiceService::class)
            ->getInvoiceSequenceForStripe($invoiceNumber);
        $downloadInvoiceNumber = trim($invoiceNumber . '-' . $invoiceSequence);
        $fileName = "{$downloadInvoiceNumber}.pdf";

        return Storage::disk('public')->download($relativePath, $fileName);
    }

    /**
     * Send a subscription invoice to the entreprise via email or Peppol.
     */
    public function sendInvoice(Request $request, Entreprise $entreprise, string $invoiceId)
    {
        $channel = (string) $request->input('channel', '');

        if (! in_array($channel, ['email', 'peppol'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid send channel.',
            ], 422);
        }

        if (! $entreprise->stripe_id) {
            return response()->json([
                'success' => false,
                'message' => 'No Stripe customer configured for this entreprise.',
            ], 404);
        }

        try {
            $stripe = Cashier::stripe();
            $stripeInvoice = $stripe->invoices->retrieve($invoiceId, []);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to retrieve Stripe invoice.',
            ], 404);
        }

        // Basic ownership check – ensure the invoice belongs to this entreprise's Stripe customer.
        if (! isset($stripeInvoice->customer) || (string) $stripeInvoice->customer !== (string) $entreprise->stripe_id) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice does not belong to this entreprise.',
            ], 403);
        }

        // Rebuild the PDF path exactly like downloadInvoice().
        $invoiceNumber = (string) ($stripeInvoice->number ?? $stripeInvoice->id ?? '');
        if ($invoiceNumber === '') {
            return response()->json([
                'success' => false,
                'message' => 'Unable to determine invoice number.',
            ], 404);
        }

        $createdAt = isset($stripeInvoice->created)
            ? Carbon::createFromTimestamp((int) $stripeInvoice->created, 'UTC')->setTimezone(config('app.timezone'))
            : now();

        $year = $createdAt->format('Y');
        $month = $createdAt->format('m');

        $relativePath = "stripe_invoices/{$entreprise->id}/{$year}/{$month}/subscription-{$invoiceNumber}.pdf";

        // If the PDF does not exist yet, try to generate it on the fly.
        if (! Storage::disk('public')->exists($relativePath)) {
            try {
                if (method_exists($stripeInvoice, 'toArray')) {
                    $payload = $stripeInvoice->toArray();
                } else {
                    $payload = json_decode(json_encode($stripeInvoice), true) ?? [];
                }

                app(SubscriptionInvoiceService::class)->createFromStripeInvoice($entreprise, $payload);
            } catch (\Throwable $e) {
                // Fall through and check again below.
            }
        }

        if (! Storage::disk('public')->exists($relativePath)) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice PDF not found on server.',
            ], 404);
        }

        if ($channel === 'peppol') {
            // Peppol is available only for entreprises that have a Belgian TVA number.
            $tvaNumber = strtoupper(trim((string) ($entreprise->tva ?? '')));
            if (! str_starts_with($tvaNumber, 'BE')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Peppol sending is only available for entreprises with a Belgian TVA number.',
                ], 422);
            }

            if (empty($entreprise->tva)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Entreprise does not have a TVA number configured for Peppol sending.',
                ], 422);
            }

            // Map the subscription invoice to a minimal Billit order payload.
            $invoiceNumber = (string) ($stripeInvoice->number ?? $stripeInvoice->id ?? '');
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

            // Derive VAT percentage from our generated PDF data if possible.
            $vatRate = 0.0;
            try {
                // Rebuild payload to reuse VAT logic from SubscriptionInvoiceService.
                if (method_exists($stripeInvoice, 'toArray')) {
                    $raw = $stripeInvoice->toArray();
                } else {
                    $raw = json_decode(json_encode($stripeInvoice), true) ?? [];
                }

                $amountPaid = (int) ($raw['amount_paid'] ?? $raw['amount_due'] ?? 0);
                if ($amountPaid > 0) {
                    $totalTtc = $amountPaid / 100;
                    [$vatRate, $isZero] = app(SubscriptionInvoiceService::class)
                        ->resolveVatRateForEntrepriseForPeppol($entreprise ?? null);
                    if ($isZero) {
                        $vatRate = 0.0;
                    }
                }
            } catch (\Throwable $e) {
                $vatRate = 0.0;
            }

            $orderLines = [
                [
                    'Quantity' => 1,
                    'UnitPriceExcl' => isset($totalTtc) && $vatRate > 0
                        ? round($totalTtc / (1 + ($vatRate / 100)), 2)
                        : (isset($totalTtc) ? $totalTtc : 0.0),
                    'Description' => 'Subscription fee',
                    'VATPercentage' => $vatRate,
                ],
            ];

            // OrderNumber must be "Stripe invoice number - suffix", e.g. "OITHAPDZ-0006-1".
            $invoiceSequence = app(SubscriptionInvoiceService::class)
                ->getInvoiceSequenceForStripe($invoiceNumber);
            $orderNumber = trim($invoiceNumber . '-' . $invoiceSequence);

            // We still use the human-visible invoice number with spaces for email/PDF naming.
            $displayInvoiceNumber = app(SubscriptionInvoiceService::class)
                ->getDisplayInvoiceNumberForStripe($invoiceNumber) ?: $invoiceNumber;

            $payload = [
                'OrderType' => 'Invoice',
                'OrderDirection' => 'Income',
                'OrderNumber' => $orderNumber,
                'OrderDate' => $invoiceDate,
                'ExpiryDate' => $expiryDate,
                'Customer' => $customerData,
                'OrderLines' => $orderLines,
            ];

            // Attach the already-generated subscription PDF so Billit uses it as the invoice PDF.
            try {
                if (Storage::disk('public')->exists($relativePath)) {
                    $pdfBinary = Storage::disk('public')->get($relativePath);
                    if (is_string($pdfBinary) && strlen($pdfBinary) > 100 && strncmp($pdfBinary, "%PDF-", 5) === 0) {
                        $fileName = "subscription-{$displayInvoiceNumber}.pdf";
                        $payload['OrderPDF'] = [
                            'FileName' => $fileName,
                            'FileContent' => base64_encode($pdfBinary),
                        ];
                    }
                }
            } catch (\Throwable $e) {
                \Log::warning('ManagerSubscriptionController: could not attach PDF to Peppol payload.', [
                    'entreprise_id' => $entreprise->id ?? null,
                    'invoice_number' => $invoiceNumber ?? null,
                    'relative_path' => $relativePath,
                    'error' => $e->getMessage(),
                ]);
            }

            $billit = BillitService::fromPlatformConfig();

            try {
                $billitResponse = $billit->sendInvoiceViaPeppol($payload, 'Peppol');
            } catch (\Throwable $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to send subscription invoice via Peppol.',
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Subscription invoice sent successfully via Peppol.',
                'billit' => $billitResponse,
            ]);
        }

        $recipient = (string) $entreprise->email;
        $copyRecipient = 'Dessin@bureaumercier.com';

        // Recipients can be selected from the UI modal. We only allow the known
        // entreprise email + the fixed copy recipient to prevent abuse.
        $rawSelectedRecipients = $request->input('recipients', []);
        if (is_string($rawSelectedRecipients)) {
            $decoded = json_decode($rawSelectedRecipients, true);
            if (is_array($decoded)) {
                $rawSelectedRecipients = $decoded;
            }
        }

        $selectedRecipients = [];
        if (is_array($rawSelectedRecipients)) {
            foreach ($rawSelectedRecipients as $value) {
                if (!is_string($value)) {
                    continue;
                }
                $email = trim($value);
                if ($email === '') {
                    continue;
                }
                if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
                    continue;
                }
                $selectedRecipients[] = $email;
            }
        }

        $selectedRecipients = array_values(array_unique($selectedRecipients));

        $allowedRecipients = array_values(array_unique(array_filter([
            $recipient,
            $copyRecipient,
        ], fn ($value) => is_string($value) && $value !== '')));

        foreach ($selectedRecipients as $email) {
            if (!in_array($email, $allowedRecipients, true)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid recipient selected.',
                ], 422);
            }
        }

        // Allow overriding the subscription recipient email via the
        // TEST_SUBSCRIPTION_EMAIL environment variable (e.g. for
        // production tests). Make sure to remove it after testing.
        $testRecipient = '';
        if ($testRecipient !== '') {
            Log::info('ManagerSubscriptionController: overriding recipient for test.', [
                'entreprise_id' => $entreprise->id,
                'original_recipient' => $recipient,
                'test_recipient' => $testRecipient,
            ]);
            $recipient = $testRecipient;
        }

        // If we override the main recipient in tests, also adjust allowed list.
        if ($testRecipient !== '') {
            $allowedRecipients = array_values(array_unique(array_filter([
                $recipient,
                $copyRecipient,
            ], fn ($value) => is_string($value) && $value !== '')));
        }

        if (empty($allowedRecipients)) {
            return response()->json([
                'success' => false,
                'message' => 'No valid recipient email addresses are available for this entreprise.',
            ], 422);
        }

        try {
            // Use the display invoice number with suffix (e.g. "INV-001 - 1") for subject and attachment name.
            $displayInvoiceNumber = app(\App\Services\SubscriptionInvoiceService::class)
                ->getDisplayInvoiceNumberForStripe($invoiceNumber) ?: $invoiceNumber;

            $subject = 'Subscription invoice '.$displayInvoiceNumber;
            $body = "Dear {$entreprise->name},\n\nPlease find your subscription invoice attached.\n\nBest regards,\nDalytrac";

            $bodyHtml = nl2br(e($body));

            // Send only to the recipients selected in the modal.
            // If UI didn't send anything (older frontend), default to both (filtered).
            $defaultRecipients = array_values(array_filter(
                array_unique([$recipient, $copyRecipient]),
                fn ($value) => is_string($value) && $value !== ''
            ));

            $recipients = !empty($selectedRecipients) ? $selectedRecipients : $defaultRecipients;

            if (empty($recipients)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please select at least one recipient.',
                ], 422);
            }

            Log::info('ManagerSubscriptionController: preparing subscription invoice email.', [
                'entreprise_id' => $entreprise->id ?? null,
                'recipient' => $recipient,
                'copy_recipient' => $copyRecipient,
                'selected_recipients' => $recipients,
                'invoice_number' => $invoiceNumber ?? null,
                'display_invoice_number' => $displayInvoiceNumber ?? null,
                'pdf_path' => $relativePath,
            ]);

            // Debug (safe): log effective mail transport config used at runtime.
            Log::info('ManagerSubscriptionController: mail runtime config snapshot.', [
                'mail_default' => config('mail.default'),
                'smtp_host' => config('mail.mailers.smtp.host'),
                'smtp_port' => config('mail.mailers.smtp.port'),
                'smtp_encryption' => config('mail.mailers.smtp.encryption'),
                'from_address' => config('mail.from.address'),
                'from_name' => config('mail.from.name'),
            ]);

            if (! Storage::disk('public')->exists($relativePath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invoice PDF not found',
                ], 404);
            }

            $tempDir = storage_path('app/temp/');
            if (! File::exists($tempDir)) {
                File::makeDirectory($tempDir, 0755, true);
            }

            $mainInvoicePath = $tempDir . 'main_subscription_invoice_' . $invoiceNumber . '.pdf';
            file_put_contents($mainInvoicePath, Storage::disk('public')->get($relativePath));

            $mergedPdfPathRelative = null;
            $failedRecipients = [];

            try {
                // Merge main PDF with optional uploaded PDFs (if provided).
                $pdf = new Fpdi();

                $pageCount = $pdf->setSourceFile($mainInvoicePath);
                for ($i = 1; $i <= $pageCount; $i++) {
                    $template = $pdf->importPage($i);
                    $pdf->AddPage();
                    $pdf->useTemplate($template);
                }

                if ($request->hasFile('file')) {
                    foreach ($request->file('file') as $file) {
                        if ($file->getClientMimeType() !== 'application/pdf') {
                            continue;
                        }

                        $tempPath = $tempDir . $file->getClientOriginalName();
                        file_put_contents($tempPath, file_get_contents($file->getPathname()));

                        $pageCount = $pdf->setSourceFile($tempPath);
                        for ($i = 1; $i <= $pageCount; $i++) {
                            $template = $pdf->importPage($i);
                            $pdf->AddPage();
                            $pdf->useTemplate($template);
                        }

                        unlink($tempPath);
                    }
                }

                // Keep attachment name consistent with previous behavior.
                $mergedFileName = str_replace(['/', '\\'], '_', $displayInvoiceNumber) . '.pdf';
                $mergedPdfPathRelative = "stripe_invoices/{$entreprise->id}/{$year}/{$mergedFileName}";
                Storage::disk('public')->put($mergedPdfPathRelative, $pdf->Output('S'));

                // Send to each recipient, like the main invoice sending logic.
                foreach ($recipients as $to) {
                    try {
                        Mail::to($to)->send(new InvoicesEmail(
                            $subject,
                            $bodyHtml,
                            null,
                            [$mergedPdfPathRelative],
                            null
                        ));
                    } catch (\Throwable $e) {
                        $failedRecipients[] = $to;
                        Log::error('ManagerSubscriptionController: failed to send subscription invoice email.', [
                            'entreprise_id' => $entreprise->id ?? null,
                            'recipient' => $to,
                            'invoice_number' => $invoiceNumber ?? null,
                            'display_invoice_number' => $displayInvoiceNumber ?? null,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }

                if (count($failedRecipients) < count($recipients)) {
                    if (! empty($failedRecipients)) {
                        return response()->json([
                            'success' => true,
                            'message' => 'Subscription invoice sent but failed for some recipients: ' . implode(', ', $failedRecipients),
                        ]);
                    }

                    return response()->json([
                        'success' => true,
                        'message' => 'Subscription invoice sent successfully to all recipients.',
                    ]);
                }

                return response()->json([
                    'success' => false,
                    'message' => 'Failed to send subscription invoice to any recipient.',
                ], 500);
            } finally {
                if (is_string($mergedPdfPathRelative) && Storage::disk('public')->exists($mergedPdfPathRelative)) {
                    Storage::disk('public')->delete($mergedPdfPathRelative);
                }
                if (is_string($mainInvoicePath) && File::exists($mainInvoicePath)) {
                    @unlink($mainInvoicePath);
                }
            }
        } catch (\Throwable $e) {
            \Log::error('ManagerSubscriptionController: failed to send subscription invoice email.', [
                'entreprise_id' => $entreprise->id ?? null,
                'invoice_number' => $invoiceNumber ?? null,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to send email for this invoice.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Subscription invoice sent successfully.',
        ]);
    }

    /**
     * Permanently delete an entreprise and all its related data.
     */
    public function destroy(Entreprise $entreprise)
    {
        if (method_exists($entreprise, 'isBlockingExempt') && $entreprise->isBlockingExempt()) {
            return redirect()
                ->back()
                ->with('error', 'This entreprise is protected and cannot be deleted.');
        }

        $entreprise->deleteWithData();

        return redirect()
            ->route('manager.subscriptions.index')
            ->with('success', 'Entreprise and all related data were deleted successfully.');
    }

    private function findLatestStripeSubscription(string $stripeCustomerId): ?object
    {
        $stripeSubscriptions = Cashier::stripe()->subscriptions->all([
            'customer' => $stripeCustomerId,
            'status' => 'all',
            'limit' => 10,
        ]);

        $subscriptions = collect($stripeSubscriptions->data ?? []);
        if ($subscriptions->isEmpty()) {
            return null;
        }

        $activeStatuses = ['active', 'trialing', 'past_due', 'unpaid'];
        $activeSubscription = $subscriptions
            ->first(fn ($sub) => in_array((string) ($sub->status ?? ''), $activeStatuses, true));

        if ($activeSubscription) {
            return $activeSubscription;
        }

        return $subscriptions
            ->sortByDesc(fn ($sub) => (int) ($sub->created ?? 0))
            ->first();
    }

    private function resolveStripePeriodEnd(object $stripeSubscription): ?Carbon
    {
        if (!empty($stripeSubscription->current_period_end)) {
            return Carbon::createFromTimestamp(
                (int) $stripeSubscription->current_period_end,
                'UTC'
            )->setTimezone(config('app.timezone'));
        }

        // Fallback: estimate end as period start/created + recurring interval.
        $interval = data_get($stripeSubscription, 'items.data.0.price.recurring.interval');
        $intervalCount = (int) (data_get($stripeSubscription, 'items.data.0.price.recurring.interval_count') ?? 1);
        $intervalCount = $intervalCount > 0 ? $intervalCount : 1;

        if (!in_array($interval, ['day', 'week', 'month', 'year'], true)) {
            return null;
        }

        $startTimestamp = (int) ($stripeSubscription->current_period_start ?? $stripeSubscription->created ?? 0);
        if ($startTimestamp <= 0) {
            return null;
        }

        $start = Carbon::createFromTimestamp($startTimestamp, 'UTC')->setTimezone(config('app.timezone'));

        return match ($interval) {
            'day' => $start->copy()->addDays($intervalCount),
            'week' => $start->copy()->addWeeks($intervalCount),
            'month' => $start->copy()->addMonths($intervalCount),
            'year' => $start->copy()->addYears($intervalCount),
        };
    }

    private function estimateMonthlyRevenueFromStripe(): float
    {
        $estimatedMonthlyRevenue = 0.0;
        $startingAfter = null;
        $allowedPlanPriceIds = $this->getAllowedPlanStripePriceIds();

        if (empty($allowedPlanPriceIds)) {
            return 0.0;
        }

        do {
            $params = [
                'status' => 'all',
                'limit' => 100,
            ];

            if ($startingAfter) {
                $params['starting_after'] = $startingAfter;
            }

            $response = Cashier::stripe()->subscriptions->all($params);
            $subscriptions = $response->data ?? [];

            foreach ($subscriptions as $stripeSubscription) {
                $status = (string) ($stripeSubscription->status ?? '');
                if ($status !== 'active') {
                    continue;
                }

                $estimatedMonthlyRevenue += $this->calculateMonthlyAmountFromStripeSubscription(
                    $stripeSubscription,
                    $allowedPlanPriceIds
                );
            }

            $hasMore = (bool) ($response->has_more ?? false);
            $lastSubscription = !empty($subscriptions) ? end($subscriptions) : null;
            $startingAfter = ($hasMore && !empty($lastSubscription->id)) ? (string) $lastSubscription->id : null;
        } while ($startingAfter);

        return $estimatedMonthlyRevenue;
    }

    private function calculateMonthlyAmountFromStripeSubscription(object $stripeSubscription, array $allowedPlanPriceIds): float
    {
        $monthlyAmount = 0.0;
        $subscriptionItems = data_get($stripeSubscription, 'items.data', []);

        foreach ($subscriptionItems as $item) {
            $price = $item->price ?? null;
            if (!$price) {
                continue;
            }

            $priceId = (string) data_get($price, 'id', '');
            if ($priceId === '' || !in_array($priceId, $allowedPlanPriceIds, true)) {
                continue;
            }

            $interval = (string) data_get($price, 'recurring.interval', '');
            $intervalCount = (int) (data_get($price, 'recurring.interval_count', 1));
            $intervalCount = $intervalCount > 0 ? $intervalCount : 1;

            if (!in_array($interval, ['day', 'week', 'month', 'year'], true)) {
                continue;
            }

            $unitAmount = data_get($price, 'unit_amount_decimal');
            if ($unitAmount === null) {
                $unitAmount = data_get($price, 'unit_amount');
            }

            if ($unitAmount === null) {
                continue;
            }

            $quantity = (int) ($item->quantity ?? 1);
            $quantity = $quantity > 0 ? $quantity : 1;

            $periodAmount = ((float) $unitAmount / 100) * $quantity;
            $monthlyAmount += $this->normalizeRecurringAmountToMonthly($periodAmount, $interval, $intervalCount);
        }

        return $monthlyAmount;
    }

    private function normalizeRecurringAmountToMonthly(float $periodAmount, string $interval, int $intervalCount): float
    {
        return match ($interval) {
            'day' => $periodAmount * (30.4375 / $intervalCount),
            'week' => $periodAmount * ((365.25 / 7 / 12) / $intervalCount),
            'month' => $periodAmount / $intervalCount,
            'year' => $periodAmount / (12 * $intervalCount),
            default => 0.0,
        };
    }

    private function getAllowedPlanStripePriceIds(): array
    {
        $priceIds = [
            env('STRIPE_PRICE_SOLO', 'price_1SzeGWB4LVww0NzztR9cOrna'),
            env('STRIPE_PRICE_PREMIUM', 'price_1Sl5B5B4LVww0NzzuRKyJNtf'),
            env('STRIPE_PRICE_PRO', 'price_1Sl5BcB4LVww0Nzzwstbphvv'),
        ];

        return array_values(array_filter(array_unique($priceIds), fn ($value) => is_string($value) && $value !== ''));
    }
}


