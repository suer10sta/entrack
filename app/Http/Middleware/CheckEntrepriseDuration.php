<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckEntrepriseDuration
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get the authenticated user
        $user = Auth::user();

        // Check if the user is associated with an entreprise
        if ($user && $user->entreprise) {
            // Check the duration and update is_activated accordingly
            $entreprise = $user->entreprise;

            // The 6-month "duration" restriction (set at registration) must not
            // apply to paying customers: a subscribed enterprise would otherwise
            // be logged out after 6 months regardless of its active plan.
            $isExempt = method_exists($entreprise, 'isBlockingExempt') && $entreprise->isBlockingExempt();
            $hasPaidPlan = method_exists($entreprise, 'hasActiveSubscription') && $entreprise->hasActiveSubscription();

            $durationExpired = $entreprise->duration && $entreprise->duration < now();

            if (!$entreprise->is_activated || ($durationExpired && !$hasPaidPlan && !$isExempt)) {
                $entreprise->update(['is_activated' => false]);
                Auth::logout();
                return redirect()->route('login')->with('error', 'You cannot log in, the activation time is out or the account is not activated.');
            }
        }

        return $next($request);
    }
}
