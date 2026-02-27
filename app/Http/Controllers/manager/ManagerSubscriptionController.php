<?php

namespace App\Http\Controllers\manager;

use App\Http\Controllers\Controller;
use App\Models\Entreprise;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Laravel\Cashier\Cashier;

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


