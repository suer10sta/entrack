<?php

namespace App\Http\Controllers;

use Laravel\Cashier\Http\Controllers\WebhookController as CashierWebhookController;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

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
            Log::error('Stripe Webhook Plan Sync Failed: ' . $e->getMessage());
        }

        // Cashier's base controller does not implement this handler, so we simply acknowledge the webhook.
        return $this->successMethod();
    }

    /**
     * Handle subscription updates (plan changes, quantity updates, etc.).
     */
    protected function handleCustomerSubscriptionUpdated(array $payload): Response
    {
        try {
            $this->syncEntreprisePlanFromPayload($payload);
        } catch (\Exception $e) {
            Log::error('Stripe Webhook Update Failed: ' . $e->getMessage());
        }

        return parent::handleCustomerSubscriptionUpdated($payload);
    }

    /**
     * Handle subscription deletions/cancellations by clearing plan info.
     */
    protected function handleCustomerSubscriptionDeleted(array $payload): Response
    {
        // Cashier finds the Entreprise using the Stripe Customer ID
        $entreprise = $this->getUserByStripeId($payload['data']['object']['customer'] ?? null);

        if ($entreprise) {
            // [FIXED] We operate directly on $entreprise, not $entreprise->entreprise
            $entreprise->forceFill([
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
        // 1. Find the Entreprise directly
        $entreprise = $this->getUserByStripeId($payload['data']['object']['customer'] ?? null);

        // [FIXED] Removed check for ->entreprise
        if (!$entreprise) {
            return;
        }

        $priceId = $this->extractPriceId($payload) ?? optional($entreprise->subscription('default'))->stripe_price;

        if (!$priceId) {
            return;
        }

        $plan = $this->mapPriceIdToPlan($priceId);

        if (!$plan) {
            return;
        }

        // [FIXED] Called forceFill on $entreprise directly
        $entreprise->forceFill([
            'type_compt' => $plan,
            'trial_ends_at' => null,
        ])->save();
    }

    private function extractPriceId(array $payload): ?string
    {
        $lines = $payload['data']['object']['lines']['data'] ?? null;

        if (!is_array($lines) || count($lines) === 0) {
            return null;
        }

        return $lines[0]['price']['id'] ?? null;
    }

    private function mapPriceIdToPlan(string $priceId): ?string
    {
        // Check your laravel.log to see if IDs match!
        Log::info('Webhook received Price ID: ' . $priceId);

        return match ($priceId) {
            // REPLACE THESE WITH YOUR EXACT STRIPE PRICE IDs
            'price_1SVBouB4LVww0Nzz5cvK9elS' => 'solo',
            'price_1SVBpHB4LVww0Nzztgb3hUSH' => 'premium',
            'price_1SVBpSB4LVww0NzzFxjCdtU1' => 'pro',
            default => null,
        };
    }

    /**
     * Gère les échecs de paiement (le début de la période de grâce de 14 jours).
     */
    protected function handleInvoicePaymentFailed(array $payload): Response
    {
        // 1. Trouver l'Entreprise
        $entreprise = $this->getUserByStripeId($payload['data']['object']['customer'] ?? null);

        if ($entreprise) {
            // Loguer l'erreur
            Log::warning("Paiement échoué pour l'entreprise ID: {$entreprise->id}. Début de la période de grâce de 14 jours.");

            // TODO: Envoyez un e-mail à l'utilisateur ici pour lui demander de mettre à jour sa carte.
            // Mail::to($entreprise->email)->send(new PaymentFailedWarning($entreprise));
        }

        // Toujours retourner 200 OK pour que Stripe arrête d'envoyer cet événement.
        return $this->successMethod();
    }
}