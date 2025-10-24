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
            if (!$entreprise->is_activated || $entreprise->duration < now()) {
                $entreprise->update(['is_activated' => false]);
                Auth::logout();
                return redirect()->route('login')->with('error', 'You cannot log in, the activation time is out or the account is not activated.');
            }
        }

        return $next($request);
    }
}
