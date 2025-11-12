<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Language
{
    public function handle(Request $request, Closure $next)
    {
        // Example: Set the app locale based on session or default
        $locale = session('locale', 'en');
        app()->setLocale($locale);

        return $next($request);
    }
}
