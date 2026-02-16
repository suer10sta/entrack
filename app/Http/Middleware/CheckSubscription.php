<?php

namespace App\Http\Middleware;

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
            // Ensure the user has an 'entreprise' relationship loaded
            // We'll use auth()->user() which is generally available after login.
            $entreprise = Auth::user()->entreprise;

            // This 'if' check is crucial for login/register routes
            if ($entreprise) {
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
                    \Log::info('Route is profile/subscription/logout - allowing access');
                    $isBlocked = false;
                } else {
                    \Log::info('Route is NOT profile - blocked? ' . ($isBlocked ? 'TRUE' : 'FALSE'));
                }

                // Share flag for any UI modal logic
                View::share('showTrialExpiredModal', $isBlocked);
                \Log::info('Final showTrialExpiredModal: ' . ($isBlocked ? 'TRUE' : 'FALSE'));
                \Log::info('=== End Debug ===');

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
            } else {
                \Log::info('No entreprise found for user');
            }
        } else {
            \Log::info('User not authenticated');
        }
        return $next($request);
    }
}
