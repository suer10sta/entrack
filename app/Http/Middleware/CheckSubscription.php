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
                $isExpired = $entreprise->isTrialExpired();
                
                
                if ($request->routeIs('profile.*') || $request->routeIs('subscription.*') || $request->routeIs('profile.index')) {
                    \Log::info('Route is profile/subscription - setting to false');
                    $isExpired = false;
                } else {
                    \Log::info('Route is NOT profile - keeping value: ' . ($isExpired ? 'TRUE' : 'FALSE'));
                }
    
                View::share('showTrialExpiredModal', $isExpired);
                \Log::info('Final showTrialExpiredModal: ' . ($isExpired ? 'TRUE' : 'FALSE'));
                \Log::info('=== End Debug ===');
            } else {
                \Log::info('No entreprise found for user');
            }
        } else {
            \Log::info('User not authenticated');
        }
        return $next($request);
    }
}
