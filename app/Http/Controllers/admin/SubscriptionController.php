<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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
                'message' => __('messages.Entreprise introuvable.'),
            ], 404);
        }

        $renewalPreference = $request->input('renewal', 'auto');
        if (!in_array($renewalPreference, ['auto', 'manual'], true)) {
            return response()->json([
                'message' => __('messages.Préférence de renouvellement invalide.'),
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
                    Log::error('Stripe subscription update failed: '.$e->getMessage(), ['entreprise_id' => $entreprise->id]);
                }
            }
        }

        // COMMENTED: admin alert emails to dessin@bureaumercier.com disabled
        /*
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
        */

        $payload = [
            'message' => __('messages.Préférence de renouvellement enregistrée.'),
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
                ->with('error', __('messages.Plan invalide.'));
        }

        $entreprise = Auth::user()->entreprise;

        if (! $entreprise) {
            return redirect()
                ->route('profile.upgrade')
                ->with('error', __('messages.Aucune entreprise associée à cet utilisateur.'));
        }

        // If country is provided via request, save it if empty
        $country = $request->query('country');
        if ($country && empty(trim((string) $entreprise->country))) {
            $entreprise->update(['country' => trim($country)]);
        }

        // Before starting payment, ensure the entreprise has a country configured.
        if (empty(trim((string) $entreprise->country))) {
            return redirect()
                ->route('profile.upgrade')
                ->with('error', __('messages.Veuillez d\'abord renseigner le pays de votre entreprise dans les informations d\'entreprise avant de souscrire à un plan.'));
        }

        if ($entreprise->hasActivePaidSubscription()) {
            return redirect()
                ->route('dashboard')
                ->with('error', __('messages.Votre entreprise est déjà abonnée à un plan !'));
        }

        $priceId = config('services.stripe.prices.' . $planName);

        if (! $priceId) {
            Log::error('Stripe price ID not configured for plan: ' . $planName, ['entreprise_id' => $entreprise->id]);
            return redirect()
                ->route('profile.upgrade')
                ->with('error', __('messages.Le paiement est momentanément indisponible pour ce plan. Veuillez réessayer plus tard ou contacter le support.'));
        }

        $renewalPreference = $request->query('renewal', 'auto');
        if (!in_array($renewalPreference, ['auto', 'manual'], true)) {
            $renewalPreference = 'auto';
        }

        $successUrl = route('subscription.success', ['plan' => $planName])
            . '?session_id={CHECKOUT_SESSION_ID}'
            . '&renewal=' . $renewalPreference;

        try {
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
        } catch (\Throwable $e) {
            Log::error('Stripe checkout creation failed: ' . $e->getMessage(), ['entreprise_id' => $entreprise->id, 'plan' => $planName]);
            return redirect()
                ->route('profile.upgrade')
                ->with('error', __('messages.Impossible de démarrer le paiement. Veuillez vérifier votre connexion puis réessayer.'));
        }
    }

    /**
     * Handle redirect after a successful payment.
     * We update the entreprise record and, if renewal is manual, cancel at the end of the period.
     */
    public function paymentSuccess(Request $request, string $planName)
    {
        $user = Auth::user();
        $entreprise = $user?->entreprise;

        if (!$entreprise) {
            return redirect()
                ->route('profile.upgrade')
                ->with('error', __('messages.Aucune entreprise associée à cet utilisateur.'));
        }

        $validPlans = ['solo', 'premium', 'pro'];
        if (!in_array($planName, $validPlans, true)) {
            return redirect()
                ->route('profile.upgrade')
                ->with('error', __('messages.Plan invalide.'));
        }

        $renewalPreference = $request->query('renewal', 'auto');
        if (!in_array($renewalPreference, ['auto', 'manual'], true)) {
            $renewalPreference = 'auto';
        }

        // Prevent double processing (e.g. after a page refresh).
        if ($entreprise->type_compt === $planName) {
            return redirect()
                ->route('dashboard')
                ->with('success', __('messages.Abonnement déjà activé !'));
        }

        // Protect existing enterprises (created before the Cashier wiring fix)
        // that already have an active Stripe subscription: never create a
        // second one. Plan changes are not supported yet.
        if ($entreprise->hasActivePaidSubscription() && $entreprise->type_compt) {
            return redirect()
                ->route('dashboard')
                ->with('error', __('messages.Votre entreprise est déjà abonnée à un plan !'));
        }

        // Validate the Stripe Checkout session before activating the plan.
        $sessionId = $request->query('session_id');
        if (!$sessionId) {
            return redirect()
                ->route('profile.upgrade')
                ->with('error', __('messages.Session de paiement manquante. Veuillez réessayer.'));
        }

        try {
            $session = Cashier::stripe()->checkout->sessions->retrieve($sessionId);
        } catch (\Throwable $e) {
            Log::error('Stripe checkout session verification failed: ' . $e->getMessage(), ['entreprise_id' => $entreprise->id]);
            return redirect()
                ->route('profile.upgrade')
                ->with('error', __('messages.Impossible de vérifier le paiement. Veuillez réessayer.'));
        }

        if (($session->payment_status ?? '') !== 'paid') {
            return redirect()
                ->route('profile.upgrade')
                ->with('error', __('messages.Le paiement n\'a pas été confirmé.'));
        }

        $metaPlan = $this->checkoutMetadataValue($session, 'plan_name');
        $metaEntreprise = $this->checkoutMetadataValue($session, 'entreprise_id');

        if ($metaPlan !== $planName || (int) $metaEntreprise !== (int) $entreprise->id) {
            Log::warning('Stripe paymentSuccess metadata mismatch.', [
                'entreprise_id' => $entreprise->id,
                'user_id' => $user->id,
                'url_plan' => $planName,
                'meta_plan' => $metaPlan,
                'meta_entreprise' => $metaEntreprise,
                'payment_status' => $session->payment_status ?? null,
                'session_id' => $sessionId,
            ]);
            return redirect()
                ->route('profile.upgrade')
                ->with('error', __('messages.Session de paiement incohérente. Veuillez recommencer.'));
        }

        // Keep the Stripe customer id in sync if it was not stored during checkout.
        if (!$entreprise->stripe_id && !empty($session->customer)) {
            $entreprise->forceFill(['stripe_id' => $session->customer])->save();
        }

        $entreprise->forceFill([
            'type_compt' => $planName,
            'trial_ends_at' => null,
            'auto_renew' => $renewalPreference === 'auto',
            'account_status' => 'active',
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
                    Log::error('Stripe cancel_at_period_end update failed: '.$e->getMessage(), ['entreprise_id' => $entreprise->id]);
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
                Log::error('Subscription invoice PDF generation failed: '.$e->getMessage(), ['entreprise_id' => $entreprise->id]);
            }
        }

        // COMMENTED: admin alert emails to dessin@bureaumercier.com disabled
        /*
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
        */

        return redirect()
            ->route('dashboard')
            ->with('success', __('messages.Paiement réussi ! Plan') . ucfirst($planName) . ' activé.');
    }

    /**
     * Read a Stripe Checkout Session metadata value.
     *
     * Stripe returns metadata as a StripeObject (ArrayAccess), NOT a plain
     * array: a plain (array) cast exposes SDK internals (_values, _opts...)
     * and always yields null, which would reject every legitimate payment.
     * ArrayAccess reads the real values. The strict caller-side comparison
     * (anti-fraud) is unchanged.
     */
    protected function checkoutMetadataValue($session, string $key): ?string
    {
        $metadata = $session->metadata ?? null;

        if ($metadata instanceof \ArrayAccess || is_array($metadata)) {
            $value = $metadata[$key] ?? null;

            return $value === null || is_array($value) ? null : (string) $value;
        }

        return null;
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

        // COMMENTED: admin alert emails to dessin@bureaumercier.com disabled
        /*
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
        */

        return redirect()
            ->route('profile')
            ->with('success', __('messages.Votre abonnement a été annulé. Vous pouvez toujours accéder à vos données, mais vous n’êtes plus facturé.'));
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
                Log::error('Stripe immediate cancellation failed: '.$e->getMessage(), ['entreprise_id' => $entreprise->id]);
            }
        } else {
            $stripeSubscription = $entreprise->latestStripeSubscriptionFromApi();
            if ($stripeSubscription) {
                try {
                    Cashier::stripe()->subscriptions->cancel((string) $stripeSubscription->id);
                } catch (\Throwable $e) {
                    Log::error('Stripe subscription cancel failed: '.$e->getMessage(), ['entreprise_id' => $entreprise->id]);
                }
            }
        }

        try {
            $entrepriseId = $entreprise->id;
            $entrepriseName = $entreprise->name;
            $userEmail = $user->email;

            // Delete entreprise and all its related data.
            $entreprise->deleteWithData();

            // COMMENTED: admin alert emails to dessin@bureaumercier.com disabled
            /*
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
            */

            // Log the user out since their enterprise (and account) no longer exists.
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->with('success', __('messages.Votre abonnement a été résilié et votre entreprise a été définitivement supprimée.'));
        } catch (\Throwable $e) {
            return redirect()
                ->route('profile')
                ->with('alertmessage', 'Une erreur est survenue lors de la suppression de votre entreprise. Veuillez réessayer plus tard.');
        }
    }
}


