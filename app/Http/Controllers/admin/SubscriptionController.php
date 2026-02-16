<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Cashier\Cashier;

class SubscriptionController extends Controller
{
    /**
     * Persist renewal preference for the current entreprise.
     */
    public function updateRenewalPreference(Request $request)
    {
        $user = Auth::user();
        $entreprise = $user?->entreprise;

        if (!$entreprise) {
            return response()->json([
                'message' => 'Entreprise introuvable.',
            ], 404);
        }

        $renewalPreference = $request->input('renewal', 'auto');
        if (!in_array($renewalPreference, ['auto', 'manual'], true)) {
            return response()->json([
                'message' => 'Préférence de renouvellement invalide.',
            ], 422);
        }

        $entreprise->forceFill([
            'auto_renew' => $renewalPreference === 'auto',
        ])->save();

        $subscription = $entreprise->currentSubscription();
        if ($subscription) {
            if ($renewalPreference === 'manual') {
                $subscription->cancelAtEndOfPeriod();
            } elseif ($subscription->onGracePeriod()) {
                $subscription->resume();
            }
        } else {
            $stripeSubscription = $entreprise->latestStripeSubscriptionFromApi();
            if ($stripeSubscription) {
                try {
                    Cashier::stripe()->subscriptions->update((string) $stripeSubscription->id, [
                        'cancel_at_period_end' => $renewalPreference === 'manual',
                    ]);
                } catch (\Throwable $e) {
                    // Keep request successful even if Stripe API is temporarily unavailable.
                }
            }
        }

        return response()->json([
            'message' => 'Préférence de renouvellement enregistrée.',
            'renewal' => $renewalPreference,
            'stripe_sync' => $entreprise->fresh()->renewalSyncStatus(),
        ]);
    }

    /**
     * Start a new subscription for the given plan.
     * The request may contain a "renewal" query parameter: "auto" (default) or "manual".
     */
    public function choosePlan(Request $request, string $planName)
    {
        $validPlans = ['solo', 'premium', 'pro'];
        if (!in_array($planName, $validPlans, true)) {
            return redirect()->back()->with('alertmessage', 'Plan invalide.');
        }

        $entreprise = Auth::user()->entreprise;

        if ($entreprise->subscribed('default')) {
            return redirect()
                ->route('dashboard')
                ->with('alertmessage', 'Votre entreprise est déjà abonnée !');
        }

        $priceId = match ($planName) {

            //solo plan price 19€/month 'solo'    => env('STRIPE_PRICE_SOLO', 'price_1Sl59xB4LVww0NzzGLakC6gJ'),
            //solo plan price 1€/day
            'solo'    => env('STRIPE_PRICE_SOLO', 'price_1SzeGWB4LVww0NzztR9cOrna'),
            'premium' => env('STRIPE_PRICE_PREMIUM', 'price_1Sl5B5B4LVww0NzzuRKyJNtf'),
            'pro'     => env('STRIPE_PRICE_PRO', 'price_1Sl5BcB4LVww0Nzzwstbphvv'),
        };

        $renewalPreference = $request->query('renewal', 'auto');
        if (!in_array($renewalPreference, ['auto', 'manual'], true)) {
            $renewalPreference = 'auto';
        }

        $successUrl = route('subscription.success', ['plan' => $planName])
            . '?session_id={CHECKOUT_SESSION_ID}'
            . '&renewal=' . $renewalPreference;

        return $entreprise->newSubscription('default', $priceId)
            ->checkout([
                'success_url' => $successUrl,
                'cancel_url' => url()->previous(),
                'metadata' => [
                    'plan_name' => $planName,
                    'entreprise_id' => $entreprise->id,
                    'user_id' => Auth::id(),
                    'renewal_preference' => $renewalPreference,
                ],
            ]);
    }

    /**
     * Handle redirect after a successful payment.
     * We update the entreprise record and, if renewal is manual, cancel at the end of the period.
     */
    public function paymentSuccess(Request $request, string $planName)
    {
        $user = Auth::user();
        $entreprise = $user->entreprise;

        $renewalPreference = $request->query('renewal', 'auto');
        if (!in_array($renewalPreference, ['auto', 'manual'], true)) {
            $renewalPreference = 'auto';
        }

        // If webhook has already updated everything, avoid double processing.
        if ($entreprise->subscribed('default') && $entreprise->type_compt === $planName) {
            return redirect()
                ->route('dashboard')
                ->with('success', 'Abonnement déjà activé !');
        }

        $entreprise->forceFill([
            'type_compt' => $planName,
            'trial_ends_at' => null,
            'auto_renew' => $renewalPreference === 'auto',
        ])->save();

        $subscription = $entreprise->currentSubscription();
        if ($subscription && $renewalPreference === 'manual') {
            $subscription->cancelAtEndOfPeriod();
        } elseif (!$subscription && $renewalPreference === 'manual') {
            $stripeSubscription = $entreprise->latestStripeSubscriptionFromApi();
            if ($stripeSubscription) {
                try {
                    Cashier::stripe()->subscriptions->update((string) $stripeSubscription->id, [
                        'cancel_at_period_end' => true,
                    ]);
                } catch (\Throwable $e) {
                    // Keep success flow resilient if direct Stripe update fails.
                }
            }
        }

        return redirect()
            ->route('dashboard')
            ->with('success', 'Paiement réussi ! Plan ' . ucfirst($planName) . ' activé.');
    }
}


