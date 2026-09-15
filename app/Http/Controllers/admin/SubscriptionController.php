<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Laravel\Cashier\Cashier;
use App\Mail\AdminActivityAlertMail;
use App\Services\SubscriptionInvoiceService;

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

        // Capture previous renewal mode so we can detect real cancellations
        $previousRenewalMode = $entreprise->auto_renew ? 'auto' : 'manual';

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

        // If the user switched from auto to manual, treat it as a cancel-at-period-end
        if ($previousRenewalMode === 'auto' && $renewalPreference === 'manual') {
            try {
                Mail::to('dessin@bureaumercier.com')->send(new AdminActivityAlertMail(
                    'Subscription cancellation requested',
                    'A user has requested to cancel their subscription at the end of the current period.',
                    [
                        'entreprise_id' => $entreprise->id,
                        'entreprise_name' => $entreprise->name,
                        'user_id' => $user->id,
                        'user_email' => $user->email,
                        'previous_renewal' => $previousRenewalMode,
                        'new_renewal' => $renewalPreference,
                        'current_plan' => $entreprise->type_compt,
                    ]
                ));
            } catch (\Throwable $alertException) {
                // Do not block API response on alert failures
            }
        }

        $payload = [
            'message' => 'Préférence de renouvellement enregistrée.',
            'renewal' => $renewalPreference,
            'stripe_sync' => $entreprise->fresh()->renewalSyncStatus(),
        ];

        // Support both JSON (AJAX) and regular form submissions.
        if ($request->expectsJson() || $request->wantsJson() || $request->isJson()) {
            return response()->json($payload);
        }

        return redirect()
            ->back()
            ->with('success', $payload['message'])
            ->with('renewal', $payload['renewal']);
    }

    /**
     * Start a new subscription for the given plan.
     * The request may contain a "renewal" query parameter: "auto" (default) or "manual".
     */
    public function choosePlan(Request $request, string $planName)
    {
        $validPlans = ['solo', 'premium', 'pro'];
        if (!in_array($planName, $validPlans, true)) {
            return redirect()
                ->route('profile.upgrade')
                ->with('error', 'Plan invalide.');
        }

        $entreprise = Auth::user()->entreprise;

        if (! $entreprise) {
            return redirect()
                ->route('profile.upgrade')
                ->with('error', 'Aucune entreprise associée à cet utilisateur.');
        }

        // Before starting payment, ensure the entreprise has a country configured.
        if (empty(trim((string) $entreprise->country))) {
            return redirect()
                ->route('profile.upgrade')
                ->with('error', 'Veuillez d\'abord renseigner le pays de votre entreprise dans les informations d\'entreprise avant de souscrire à un plan.');
        }

        if ($entreprise->subscribed('default')) {
            return redirect()
                ->route('dashboard')
                ->with('error', 'Votre entreprise est déjà abonnée !');
        }

        $priceId = match ($planName) {

            //solo plan price 19€/month 
            'solo'    => env('STRIPE_PRICE_SOLO', 'price_1Sl59xB4LVww0NzzGLakC6gJ'),
            //solo plan price 1€/day
            //'solo'    => env('STRIPE_PRICE_SOLO', 'price_1SzeGWB4LVww0NzztR9cOrna'),
            'premium' => env('STRIPE_PRICE_PREMIUM', 'price_1Sl5B5B4LVww0NzzuRKyJNtf'),
            'pro'     => env('STRIPE_PRICE_PRO', 'price_1Sl5BcB4LVww0Nzzwstbphvv'),
            // Pro plan price 99€/month (test mode)
            //'pro'     => env('STRIPE_PRICE_PRO', 'price_1SVBpSB4LVww0NzzFxjCdtU1'),
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

        // For local/test usage: try to generate a custom PDF subscription invoice
        // even if the Stripe webhook is not configured/reachable.
        if ($entreprise->stripe_id) {
            try {
                $invoices = $entreprise->invoicesIncludingPending();
                $latestInvoice = $invoices[0] ?? null;

                if ($latestInvoice) {
                    $stripeInvoice = $latestInvoice->asStripeInvoice();
                    if (method_exists($stripeInvoice, 'toArray')) {
                        $payload = $stripeInvoice->toArray();
                    } else {
                        // Fallback: rely on public properties
                        $payload = json_decode(json_encode($stripeInvoice), true) ?? [];
                    }

                    app(SubscriptionInvoiceService::class)->createFromStripeInvoice($entreprise, $payload);
                }
            } catch (\Throwable $e) {
                // Do not block user flow if PDF generation fails
            }
        }

        // Notify admin that a subscription payment has succeeded
        try {
            Mail::to('dessin@bureaumercier.com')->send(new AdminActivityAlertMail(
                'New subscription payment',
                'A subscription payment has been completed successfully.',
                [
                    'entreprise_id' => $entreprise->id,
                    'entreprise_name' => $entreprise->name,
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                    'plan' => $planName,
                    'renewal' => $renewalPreference,
                ]
            ));
        } catch (\Throwable $alertException) {
            // Do not block user redirect on alert failures
        }

        return redirect()
            ->route('dashboard')
            ->with('success', 'Paiement réussi ! Plan ' . ucfirst($planName) . ' activé.');
    }

    /**
     * Allow the owner to cancel the current Stripe subscription
     * without deleting the entreprise or any associated data.
     */
    public function cancelSubscription(Request $request)
    {
        $user = Auth::user();
        $entreprise = $user?->entreprise;

        if (!$entreprise) {
            return redirect()
                ->route('profile')
                ->with('alertmessage', 'Entreprise introuvable.');
        }

        $subscription = $entreprise->currentSubscription();

        if (!$subscription) {
            // Fallback: try resolving directly from Stripe in case of desync
            $stripeSubscription = $entreprise->latestStripeSubscriptionFromApi();
            if (!$stripeSubscription) {
                return redirect()
                    ->route('profile')
                    ->with('alertmessage', 'Aucun abonnement actif à annuler.');
            }

            try {
                Cashier::stripe()->subscriptions->cancel((string) $stripeSubscription->id);
            } catch (\Throwable $e) {
                return redirect()
                    ->route('profile')
                    ->with('alertmessage', 'Impossible d’annuler votre abonnement pour le moment. Veuillez réessayer plus tard.');
            }
        } else {
            try {
                $subscription->cancelNow();
            } catch (\Throwable $e) {
                return redirect()
                    ->route('profile')
                    ->with('alertmessage', 'Impossible d’annuler votre abonnement pour le moment. Veuillez réessayer plus tard.');
            }
        }

        // Inform platform admin that the owner has canceled their subscription.
        try {
            Mail::to('dessin@bureaumercier.com')->send(new AdminActivityAlertMail(
                'Subscription canceled by owner',
                'A user has canceled their active subscription from the profile page.',
                [
                    'entreprise_id' => $entreprise->id,
                    'entreprise_name' => $entreprise->name,
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                ]
            ));
        } catch (\Throwable $alertException) {
            // Do not block user flow on alert failures.
        }

        return redirect()
            ->route('profile')
            ->with('success', 'Votre abonnement a été annulé. Vous pouvez toujours accéder à vos données, mais vous n’êtes plus facturé.');
    }

    /**
     * Cancel the active subscription (if any) and permanently delete
     * the entreprise and all its data. This is triggered by the owner
     * from their profile page.
     */
    public function cancelAndDelete(Request $request)
    {
        $user = Auth::user();
        $entreprise = $user?->entreprise;

        if (!$entreprise) {
            return redirect()
                ->route('profile')
                ->with('alertmessage', 'Entreprise introuvable.');
        }

        // Certain entreprises (like internal test accounts) must never be deleted.
        if (method_exists($entreprise, 'isBlockingExempt') && $entreprise->isBlockingExempt()) {
            return redirect()
                ->route('profile')
                ->with('alertmessage', 'Cette entreprise ne peut pas être supprimée depuis l’interface.');
        }

        // Server-side confirmation check for the destructive action.
        $confirmationName = (string) $request->input('confirmation_name', '');
        if ($confirmationName !== (string) $entreprise->name) {
            return redirect()
                ->route('profile')
                ->with('alertmessage', 'La confirmation de suppression est invalide.');
        }

        // Try to cancel the Stripe subscription immediately if one exists.
        $subscription = $entreprise->currentSubscription();
        if ($subscription) {
            try {
                $subscription->cancelNow();
            } catch (\Throwable $e) {
                // Keep going even if immediate cancellation fails.
            }
        } else {
            $stripeSubscription = $entreprise->latestStripeSubscriptionFromApi();
            if ($stripeSubscription) {
                try {
                    Cashier::stripe()->subscriptions->cancel((string) $stripeSubscription->id);
                } catch (\Throwable $e) {
                    // Ignore Stripe API errors so the user can still close the account.
                }
            }
        }

        try {
            $entrepriseId = $entreprise->id;
            $entrepriseName = $entreprise->name;
            $userEmail = $user->email;

            // Delete entreprise and all its related data.
            $entreprise->deleteWithData();

            // Notify platform admin that an enterprise has been closed.
            try {
                Mail::to('dessin@bureaumercier.com')->send(new AdminActivityAlertMail(
                    'Enterprise closed by owner',
                    'An enterprise and its subscription have been cancelled and deleted by the owner.',
                    [
                        'entreprise_id' => $entrepriseId,
                        'entreprise_name' => $entrepriseName,
                        'user_id' => $user->id,
                        'user_email' => $userEmail,
                    ]
                ));
            } catch (\Throwable $alertException) {
                // Do not block user flow on alert failures.
            }

            // Log the user out since their enterprise (and account) no longer exists.
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->with('success', 'Votre abonnement a été résilié et votre entreprise a été définitivement supprimée.');
        } catch (\Throwable $e) {
            return redirect()
                ->route('profile')
                ->with('alertmessage', 'Une erreur est survenue lors de la suppression de votre entreprise. Veuillez réessayer plus tard.');
        }
    }
}


