<?php

namespace App\Http\Controllers;

use Laravel\Cashier\Http\Controllers\WebhookController as CashierWebhookController;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;
use App\Models\Entreprise;
use App\Services\SubscriptionInvoiceService;
use App\Services\SubscriptionInvoiceSender;

class StripeWebhookController extends CashierWebhookController
{
    /**
     * Payment succeeded -> keep account active, reset failure counter.
     */
    protected function handleInvoicePaymentSucceeded(array $payload): Response
    {
        try {
            $customerId = $payload['data']['object']['customer'] ?? null;
            $invoice = $payload['data']['object'] ?? null;
            Log::info('Stripe invoice.succeeded customer: '.$customerId);

            $entreprise = $customerId
                ? Entreprise::where('stripe_id', $customerId)->first()
                : null;

            if ($entreprise) {
                $entreprise->forceFill([
                    'account_status' => 'active',
                ])->save();

                $entreprise->resetFailedPaymentCount();

                if (is_array($invoice)) {
                    try {
                        $relativePath = app(SubscriptionInvoiceService::class)->createFromStripeInvoice($entreprise, $invoice);
                        app(SubscriptionInvoiceSender::class)->sendAutomatic($entreprise, $invoice, $relativePath);
                    } catch (\Throwable $e) {
                        Log::error('Failed to generate/send custom subscription invoice: '.$e->getMessage());
                    }
                }
            } else {
                Log::warning('Stripe invoice.succeeded: no entreprise found for customer '.$customerId);
            }
        } catch (\Exception $e) {
            Log::error('Stripe invoice succeeded handler failed: '.$e->getMessage());
        }

        return $this->successMethod();
    }

    /**
     * Payment failed -> increment counter, block only after 3 consecutive failures.
     */
    protected function handleInvoicePaymentFailed(array $payload): Response
    {
        try {
            $customerId = $payload['data']['object']['customer'] ?? null;
            Log::info('Stripe invoice.failed customer: '.$customerId);

            $entreprise = $customerId
                ? Entreprise::where('stripe_id', $customerId)->first()
                : null;

            if ($entreprise) {
                $entreprise->recordFailedPayment();

                if ($entreprise->isBlockedByFailedPayments()) {
                    $entreprise->forceFill([
                        'account_status' => 'inactive',
                    ])->save();
                    Log::warning('Stripe: entreprise '.$entreprise->id.' blocked after 3 failed payments');
                } else {
                    Log::info('Stripe: entreprise '.$entreprise->id.' failed payment count: '.$entreprise->failed_payment_count);
                }
            } else {
                Log::warning('Stripe invoice.failed: no entreprise found for customer '.$customerId);
            }
        } catch (\Exception $e) {
            Log::error('Stripe invoice failed handler failed: '.$e->getMessage());
        }

        return $this->successMethod();
    }

    /**
     * Subscription cancelled -> mark account inactive.
     */
    protected function handleCustomerSubscriptionDeleted(array $payload): Response
    {
        try {
            $customerId = $payload['data']['object']['customer'] ?? null;
            Log::info('Stripe subscription.deleted customer: '.$customerId);

            $entreprise = $customerId
                ? Entreprise::where('stripe_id', $customerId)->first()
                : null;

            if ($entreprise) {
                $entreprise->forceFill([
                    'account_status' => 'inactive',
                ])->save();
            } else {
                Log::warning('Stripe subscription.deleted: no entreprise found for customer '.$customerId);
            }
        } catch (\Exception $e) {
            Log::error('Stripe subscription deleted handler failed: '.$e->getMessage());
        }

        return $this->successMethod();
    }
}
