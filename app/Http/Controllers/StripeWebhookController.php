<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Http\Controllers\WebhookController as CashierWebhookController;

class StripeWebhookController extends CashierWebhookController
{
    /**
     * Handle successful invoice payments and keep entreprise plan in sync.
     */
    protected function handleInvoicePaymentSucceeded(array $payload): Response
    {
        try {
            $this->syncEntreprisePlanFromPayload($payload);
        } catch (\Exception $e) {
            // Log the error so you can fix it later, but return 200 to Stripe
            Log::error('Stripe Webhook Plan Sync Failed: ' . $e->getMessage());
        }

        return $this->successMethod();
    }

    /**
     * Handle subscription updates (plan changes, quantity updates, etc.).
     */
    protected function handleCustomerSubscriptionUpdated(array $payload): Response
    {
        $status = strtolower($payload['data']['object']['status'] ?? '');

        if ($this->subscriptionIsHealthy($status)) {
            $this->syncEntreprisePlanFromPayload($payload);
        } else {
            $this->markEntrepriseAsPastDue($payload, sprintf('Subscription status "%s"', $status));
        }

        return $this->successMethod();
    }

    /**
     * Handle failed payment events by revoking access until user confirms payment.
     */
    protected function handleInvoicePaymentFailed(array $payload): Response
    {
        $this->markEntrepriseAsPastDue($payload, 'invoice.payment_failed');

        return $this->successMethod();
    }

    /**
     * Handle subscription deletions/cancellations by clearing plan info.
     */
    protected function handleCustomerSubscriptionDeleted(array $payload): Response
    {
        $customer = $this->getUserByStripeId($payload['data']['object']['customer'] ?? null);

        if ($customer && $customer->entreprise) {
            $customer->entreprise->forceFill([
                'type_compt' => null,
            ])->save();
        }

        return parent::handleCustomerSubscriptionDeleted($payload);
    }

    /**
     * Sync entreprise plan & trial info using the price from the payload.
     */
    private function syncEntreprisePlanFromPayload(array $payload): void
    {
        $customer = $this->getUserByStripeId($payload['data']['object']['customer'] ?? null);

        if (!$customer || !$customer->entreprise) {
            return;
        }

        $priceId = $this->extractPriceId($payload) ?? optional($customer->subscription('default'))->stripe_price;

        if (!$priceId) {
            return;
        }

        $plan = $this->mapPriceIdToPlan($priceId);

        if (!$plan) {
            return;
        }

        $customer->entreprise->forceFill([
            'type_compt' => $plan,
            'trial_ends_at' => null,
        ])->save();
    }

    /**
     * Extract Stripe price ID from payload lines.
     */
    private function extractPriceId(array $payload): ?string
    {
        $lines = $payload['data']['object']['lines']['data'] ?? null;

        if (!is_array($lines) || count($lines) === 0) {
            return null;
        }

        return $lines[0]['price']['id'] ?? null;
    }

    /**
     * Map Stripe price IDs to internal plan codes.
     */
    private function mapPriceIdToPlan(string $priceId): ?string
    {
        Log::info('Webhook received Price ID from Stripe: ' . $priceId);
        return match ($priceId) {
            'price_1SVBouB4LVww0Nzz5cvK9elS' => 'solo',
            'price_1SVBpHB4LVww0Nzztgb3hUSH' => 'premium',
            'price_1SVBpSB4LVww0NzzFxjCdtU1' => 'pro',
            default => null,
        };
    }

    /**
     * Is the subscription in a state where access should continue?
     */
    private function subscriptionIsHealthy(?string $status): bool
    {
        return in_array($status, ['active', 'trialing'], true);
    }

    /**
     * Remove entreprise access until payment issues are resolved.
     */
    private function markEntrepriseAsPastDue(array $payload, ?string $reason = null): void
    {
        $customer = $this->getUserByStripeId($payload['data']['object']['customer'] ?? null);

        if (!$customer || !$customer->entreprise) {
            return;
        }

        $customer->entreprise->forceFill([
            'type_compt' => null,
            // Force the trial to appear expired so the UI blocks access
            'trial_ends_at' => now()->subMinute(),
        ])->save();

        Log::warning('Entreprise access revoked until payment is confirmed.', [
            'user_id' => $customer->id,
            'entreprise_id' => $customer->entreprise->id,
            'reason' => $reason,
        ]);
    }
}

