<?php

namespace App\Http\Controllers;

use Laravel\Cashier\Http\Controllers\WebhookController as CashierWebhookController;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;
use App\Models\Entreprise;

class StripeWebhookController extends CashierWebhookController
{
    /**
     * Payment succeeded -> keep account active.
     */
    protected function handleInvoicePaymentSucceeded(array $payload): Response
    {
        try {
            $customerId = $payload['data']['object']['customer'] ?? null;
            Log::info('Stripe invoice.succeeded customer: '.$customerId);

            $entreprise = $customerId
                ? Entreprise::where('stripe_id', $customerId)->first()
                : null;

            if ($entreprise) {
                $entreprise->forceFill([
                    'account_status' => 'active',
                ])->save();
            } else {
                Log::warning('Stripe invoice.succeeded: no entreprise found for customer '.$customerId);
            }
        } catch (\Exception $e) {
            Log::error('Stripe invoice succeeded handler failed: '.$e->getMessage());
        }

        return $this->successMethod();
    }

    /**
     * Payment failed -> mark account inactive (blocked until fixed).
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
                $entreprise->forceFill([
                    'account_status' => 'inactive',
                ])->save();
            } else {
                Log::warning('Stripe invoice.failed: no entreprise found for customer '.$customerId);
            }
        } catch (\Exception $e) {
            Log::error('Stripe invoice failed handler failed: '.$e->getMessage());
        }

        return $this->successMethod();
    }

    /**
     * Subscription cancelled/deleted -> also mark account inactive.
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
