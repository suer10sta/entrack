<?php

namespace App\Services\Surveys;

use App\Models\Entreprise;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Cashier;
use Throwable;

class FirstSubscriptionPayment
{
    public function fromCheckout(Entreprise $company, $session): void
    {
        $customer = $this->id($session->customer ?? null);
        if (($session->payment_status ?? null) !== 'paid' || ($session->mode ?? null) !== 'subscription'
            || ($session->amount_total ?? 0) <= 0 || ! $customer || $customer !== $company->stripe_id
            || (string) ($session->metadata['entreprise_id'] ?? '') !== (string) $company->id) {
            return;
        }
        $this->capture($company);
    }

    public function fromInvoice(Entreprise $company, array $invoice): void
    {
        if ($this->paidAt($invoice, $company->stripe_id) === null) {
            return;
        }
        $this->capture($company);
    }

    public function retryPending(Entreprise $company): bool
    {
        if (! $company->first_paid_at && $company->first_paid_lookup_customer_id) {
            $this->capture($company);
        }

        return $company->fresh()->first_paid_at !== null;
    }

    private function id($value): ?string
    {
        return is_string($value) ? $value : (is_array($value) ? ($value['id'] ?? null) : ($value->id ?? null));
    }

    private function paidAt(array $invoice, ?string $customer): ?int
    {
        $subscription = $invoice['subscription'] ?? $invoice['parent']['subscription_details']['subscription'] ?? null;
        $at = $invoice['status_transitions']['paid_at'] ?? null;

        return $customer && $this->id($invoice['customer'] ?? null) === $customer && $this->id($subscription)
            && ($invoice['status'] ?? null) === 'paid' && ($invoice['amount_paid'] ?? 0) > 0
            && is_int($at) && $at > 0 && $at <= now('UTC')->timestamp && is_string($invoice['id'] ?? null)
            ? $at : null;
    }

    private function capture(Entreprise $company): void
    {
        try {
            if (! $company->stripe_id || $company->getConnection()->table('entreprises')->where('id', $company->id)->value('first_paid_at')) {
                return;
            }
            $requested = $company->getConnection()->table('entreprises')->where('id', $company->id)->whereNull('first_paid_at')->whereNull('first_paid_lookup_customer_id')->update(['first_paid_lookup_customer_id' => $company->stripe_id]);
            if ($requested) {
                $company->getConnection()->afterCommit(fn () => app(D5Lifecycle::class)->neutralizeTrialSurveys($company->id));
            }
            $pendingCustomer = $company->getConnection()->table('entreprises')->where('id', $company->id)->value('first_paid_lookup_customer_id');
            if ($pendingCustomer !== $company->stripe_id) {
                throw new \RuntimeException('Pending payment identity requires review.');
            }
            // Only on a confirmed payment event or its persisted pending receipt.
            // Scan all paid subscription invoices, including previous subscriptions and delayed webhooks.
            $invoices = Cashier::stripe()->invoices->all(['customer' => $company->stripe_id, 'status' => 'paid', 'limit' => 100]);
            $first = null;
            $at = null;
            foreach ($invoices->autoPagingIterator() as $invoice) {
                $data = $invoice->toArray();
                $paidAt = $this->paidAt($data, $company->stripe_id);
                if ($paidAt !== null && ($at === null || $paidAt < $at)) {
                    $at = $paidAt;
                    $first = $data['id'];
                }
            }
            if ($at === null) {
                return;
            } // No guessed dates, including zero-value invoices.
            $changed = $company->getConnection()->table('entreprises')->where('id', $company->id)->whereNull('first_paid_at')
                ->update(['first_paid_at' => CarbonImmutable::createFromTimestampUTC($at)->toIso8601String(), 'first_paid_invoice_id' => $first, 'first_paid_lookup_customer_id' => null]);
            if ($changed) {
                $company->getConnection()->afterCommit(fn () => app(D5Lifecycle::class)->neutralizeTrialSurveys($company->id));
            }
        } catch (Throwable $e) {
            // Payment activation must succeed even if a migration/API is temporarily unavailable.
            Log::warning('First subscription payment could not be recorded.', ['entreprise_id' => $company->id, 'exception_class' => $e::class]);
        }
    }
}
