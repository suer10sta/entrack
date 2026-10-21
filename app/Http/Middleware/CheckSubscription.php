<?php

namespace App\Http\Middleware;

use App\Events\TrialExpirationWarning;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\View;


class CheckSubscription
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        View::share('showTrialExpiredModal', false);
        View::share('trialDaysRemaining', null);
        View::share('isOnTrial', false);
        if (Auth::check()) {
            $user = Auth::user();
            $entreprise = $user ? $user->entreprise : null;

            if ($entreprise) {
                $trialDaysRemaining = $entreprise->trial_ends_at
                    ? (int) ceil(now()->floatDiffInDays($entreprise->trial_ends_at))
                    : null;
                $isOnTrial = $entreprise->isOnTrial();
                View::share('trialDaysRemaining', $trialDaysRemaining);
                View::share('isOnTrial', $isOnTrial);

                $this->sendTrialExpirationIfNeeded($user, $entreprise);
            }

            if ($user && (method_exists($user, 'hasRole') && $user->hasRole('super_admin'))) {
                return $next($request);
            }

            // A user without an entreprise has no subscription state to enforce
            // (e.g. dangling entreprise_id, or manager-area requests carrying an
            // unrelated web session) — let the request through instead of crashing.
            if (!$entreprise) {
                return $next($request);
            }

            // Ensure the user has an 'entreprise' relationship loaded
            // We'll use auth()->user() which is generally available after login.

                // Check if subscription is canceled
                $hasCanceledSubscription = method_exists($entreprise, 'hasCanceledSubscription') 
                    ? $entreprise->hasCanceledSubscription() 
                    : false;
                
                // Inactive if trial expired OR blocked by failed payments OR subscription is canceled
                $isBlocked = $entreprise->isTrialExpired()
                    || $entreprise->isBlockedByFailedPayments()
                    || $hasCanceledSubscription;

                // Never block entreprises that are exempt from blocking (e.g. ID = 43)
                if (method_exists($entreprise, 'isBlockingExempt') && $entreprise->isBlockingExempt()) {
                    $isBlocked = false;
                }

                // The existing upgrade page saves the country through this API.
                // Allow only that field; company settings pages stay blocked.
                if ($isBlocked && $user->hasAnyRole([
                    'Admin', 'SuperAdmin', 'Responsable de projet', 'Responsable de projet et gestionnaire',
                ])) {
                    $companyRoutes = ['admin', 'SuperAdmin', 'Responsable_de_projet', 'Responsable_gestionnaire'];
                    foreach ($companyRoutes as $prefix) {
                        $isCountryUpdate = $request->isMethod('POST')
                            && $request->routeIs($prefix.'.Enterprise_infos.updateInfos')
                            && $request->input('column') === 'country'
                            && (string) $request->input('id') === (string) $user->entreprise_id;
                        if ($isCountryUpdate) {
                            $request->attributes->set('subscriptionCountryRecovery', true);
                            return $next($request);
                        }
                    }
                }

                // Allow access to profile & subscription routes so they can upgrade,
                // and always allow logout so blocked users can sign out.
                if (
                    $request->routeIs('profile.*')
                    || $request->routeIs('subscription.*')
                    || $request->routeIs('profile.index')
                    || $request->routeIs('account.blocked')
                    || $request->routeIs('logout')
                ) {
                    $isBlocked = false;
                }

                // Share flag for any UI modal logic
                View::share('showTrialExpiredModal', $isBlocked);

                // If blocked, force redirect to upgrade page and prevent access to other interfaces
                if ($isBlocked) {
                    // The upgrade page is only reachable by roles allowed on it
                    // (Admin / super_admin / SuperAdmin / Responsible de projet ...).
                    // For everyone else (e.g. Employee), send them to a neutral
                    // "account blocked" page instead of an Admin-only route that
                    // would return 403.
                    $upgradeRoles = [
                        'Admin',
                        'super_admin',
                        'SuperAdmin',
                        'Responsable de projet',
                        'Responsable de projet et gestionnaire',
                    ];

                    $canAccessUpgrade = method_exists($user, 'hasAnyRole')
                        ? $user->hasAnyRole($upgradeRoles)
                        : false;

                    if (!$canAccessUpgrade) {
                        return redirect()->route('account.blocked');
                    }

                    // Set appropriate message based on the reason
                    if ($entreprise->isBlockedByFailedPayments()) {
                        return redirect()->route('profile.upgrade')
                            ->with('error', __('messages.Votre paiement a échoué 3 fois consécutives. Veuillez mettre à jour votre moyen de paiement pour continuer.'));
                    } elseif ($hasCanceledSubscription) {
                        return redirect()->route('profile.upgrade')
                            ->with('error', __('messages.Your subscription has been canceled. Please reactivate your plan to continue using the application.'));
                    } else {
                        return redirect()->route('profile.upgrade')
                            ->with('error', __('messages.Votre période d\'essai est terminée. Choisissez un plan pour continuer à utiliser l\'application.'));
                    }
                }
            }
        return $next($request);
    }

    /**
     * Send trial expiration warning notification if trial ends within 3 days.
     * Uses a session flag to avoid spamming the user on every request.
     */
    private function sendTrialExpirationIfNeeded($user, $entreprise): void
    {
        if (!$user || !$entreprise || !$entreprise->trial_ends_at) {
            return;
        }

        // Only send if trial is still active but expiring soon
        if (!$entreprise->isOnTrial()) {
            return;
        }

        $daysRemaining = (int) ceil(now()->floatDiffInDays($entreprise->trial_ends_at));

        if ($daysRemaining > 3) {
            return;
        }

        // Use session to avoid sending multiple times per day
        $sessionKey = 'trial_warning_sent_' . $user->id;
        $lastSent = session($sessionKey);

        if ($lastSent && $lastSent >= now()->startOfDay()->timestamp) {
            return;
        }

        session([$sessionKey => now()->timestamp]);

        event(new TrialExpirationWarning($user, $entreprise, $daysRemaining));
    }
}
