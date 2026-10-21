<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Language
{
    public function handle(Request $request, Closure $next)
    {
        // Prefer a persisted user locale (so it survives logout/login),
        // otherwise fall back to session, then default.
        $locale = null;

        $supported = ['fr', 'en', 'nl', 'es'];
        $managerArea = $request->is('manager', 'manager/*');
        if ($managerArea && auth('manager')->check()) {
            $locale = auth('manager')->user()->locale ?? null;
        } elseif (auth()->check()) {
            $locale = auth()->user()->locale ?? null;
        }

        if (!$locale && auth('manager')->check()) {
            $locale = auth('manager')->user()->locale ?? null;
        }

        if (!in_array($locale, $supported, true)) {
            $locale = session('locale');
        }
        if (!in_array($locale, $supported, true)) {
            $locale = config('app.locale', 'fr');
        }
        if (!in_array($locale, $supported, true)) {
            $locale = 'fr';
        }

        app()->setLocale($locale);

        // Keep session in sync so existing session-based language switch UI keeps working.
        if (session('locale') !== $locale) {
            session(['locale' => $locale]);
        }

        return $next($request);
    }
}
