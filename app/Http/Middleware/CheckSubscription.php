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
        if (Auth::check()) {
            $user = Auth::user();
            $entreprise = $user ? $user->entreprise : null;

            // Send trial expiration warning for all users with an entreprise (including admins)
            if ($entreprise) {
                $this->sendTrialExpirationIfNeeded($user, $entreprise);
            }

            // Admin users may not be linked to a specific entreprise; skip subscription checks entirely.
            if ($user && (method_exists($user, 'hasRole') && ($user->hasRole('Admin') || $user->hasRole('super_admin')))) {
                return $next($request);
            }
            // Ensure the user has an 'entreprise' relationship loaded
            // We'll use auth()->user() which is generally available after login.

                // Check if subscription is canceled
                $hasCanceledSubscription = method_exists($entreprise, 'hasCanceledSubscription') 
                    ? $entreprise->hasCanceledSubscription() 
                    : false;
                
                // Inactive if trial expired OR account explicitly marked inactive OR subscription is canceled
                $isBlocked = $entreprise->isTrialExpired()
                    || $entreprise->account_status === 'inactive'
                    || $hasCanceledSubscription;

                // Never block entreprises that are exempt from blocking (e.g. ID = 43)
                if (method_exists($entreprise, 'isBlockingExempt') && $entreprise->isBlockingExempt()) {
                    $isBlocked = false;
                }

                // Allow access to profile & subscription routes so they can upgrade,
                // and always allow logout so blocked users can sign out.
                if (
                    $request->routeIs('profile.*')
                    || $request->routeIs('subscription.*')
                    || $request->routeIs('profile.index')
                    || $request->routeIs('logout')
                ) {
                    $isBlocked = false;
                }

                // Share flag for any UI modal logic
                View::share('showTrialExpiredModal', $isBlocked);

                // If blocked, force redirect to upgrade page and prevent access to other interfaces
                if ($isBlocked) {
                    // Set appropriate message based on the reason
                    if ($hasCanceledSubscription) {
                        return redirect()->route('profile.upgrade')
                            ->with('error', 'Your subscription has been canceled. Please reactivate your plan to continue using the application.');
                    } elseif ($entreprise->account_status === 'inactive') {
                        return redirect()->route('profile.upgrade')
                            ->with('error', 'Your account is inactive. Please reactivate your subscription to continue using the application.');
                    } else {
                        return redirect()->route('profile.upgrade');
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

        $daysRemaining = (int) $entreprise->trial_ends_at->diffInDays(now());

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
