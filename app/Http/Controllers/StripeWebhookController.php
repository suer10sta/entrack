<?php

namespace App\Http\Controllers;

use App\Models\Entreprise;
use App\Services\SubscriptionInvoiceSender;
use App\Services\SubscriptionInvoiceService;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Cashier;
use Laravel\Cashier\Http\Controllers\WebhookController as CashierWebhookController;
use Symfony\Component\HttpFoundation\Response;

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
                // Do NOT blindly force account_status = 'active': an invoice
                // succeeds while the subscription is still past_due/unpaid,
                // so verify the real subscription status first. Only
                // active/trialing subscriptions keep the account active.
                $subscriptionStatus = $this->fetchSubscriptionStatus($invoice, $entreprise);

                // When we cannot retrieve the subscription, keep the previous
                // behaviour (payment succeeded -> active).
                $healthy = $subscriptionStatus === null
                    || in_array($subscriptionStatus, ['active', 'trialing'], true);

                $entreprise->forceFill([
                    'account_status' => $healthy ? 'active' : 'inactive',
                ])->save();

                $entreprise->resetFailedPaymentCount();

                app(\App\Services\Surveys\FirstSubscriptionPayment::class)->fromInvoice($entreprise, $invoice ?? []);
                app(\App\Services\Surveys\SubscriptionChurnSync::class)->invoice($entreprise, $invoice ?? [], $healthy);

                // Converge local plan state from the paid invoice so a customer
                // who never returns to the success URL still gets their plan.
                // Only fills/aligns type_compt, never clears it.
                $paidPlan = $this->planFromInvoice($invoice);
                app(\App\Services\AnalyticsBusinessEvents::class)->payment((int) $entreprise->id, $invoice ?? [], $paidPlan ?: $entreprise->type_compt);
                if ($paidPlan && $entreprise->type_compt !== $paidPlan) {
                    $entreprise->forceFill([
                        'type_compt' => $paidPlan,
                        'trial_ends_at' => null,
                    ])->save();
                    Log::info('Stripe invoice.succeeded: plan converged to '.$paidPlan, ['entreprise_id' => $entreprise->id]);
                }

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
     * Fetch the Stripe status of the subscription referenced by an invoice.
     *
     * @return string|null null when the subscription id is missing or unreachable.
     */
    private function fetchSubscriptionStatus(?array $invoice, Entreprise $entreprise): ?string
    {
        $subscriptionId = $invoice['subscription'] ?? null;

        if (! $subscriptionId) {
            return null;
        }

        try {
            $subscription = Cashier::stripe()->subscriptions->retrieve($subscriptionId);

            return $subscription->status ?? null;
        } catch (\Throwable $e) {
            Log::warning('Stripe invoice.succeeded: unable to retrieve subscription '.$subscriptionId.' - '.$e->getMessage());

            return null;
        }
    }

    /**
     * Map a Stripe invoice payload back to a DalyTrac plan key via the
     * subscription line item's Price ID. Null when not determinable.
     */
    private function planFromInvoice(?array $invoice): ?string
    {
        $price = $invoice['lines']['data'][0]['price'] ?? null;
        if (is_array($price)) {
            $price = $price['id'] ?? null;
        }

        return Entreprise::planForStripePrice(is_string($price) ? $price : null);
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

        app(\App\Services\Surveys\SubscriptionChurnSync::class)->event($payload, 'deleted');

        return $this->successMethod();
    }

    protected function handleCustomerSubscriptionUpdated(array $payload)
    {
        $response = parent::handleCustomerSubscriptionUpdated($payload);
        app(\App\Services\Surveys\SubscriptionChurnSync::class)->event($payload, 'updated');

        return $response;
    }

    protected function handleCustomerSubscriptionCreated(array $payload)
    {
        $response = parent::handleCustomerSubscriptionCreated($payload);
        app(\App\Services\Surveys\SubscriptionChurnSync::class)->event($payload, 'created');

        return $response;
    }
}
