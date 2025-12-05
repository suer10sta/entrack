<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;


class SetUserTimezone
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            $timezone = $user->timezone; // This can be null or empty

            // ✅ START FIX
            // Check if the timezone is valid and not empty
            if (!empty($timezone)) {
                // If it's valid, set it.
                date_default_timezone_set($timezone);
            } else {
                // If it's empty, fall back to the app's default (which we set to 'UTC')
                date_default_timezone_set(config('app.timezone', 'UTC'));
            }
            // ✅ END FIX
        }

        return $next($request);
    }
}
