<?php

namespace App\Http\Middleware;

use App\Services\ProspectReferralAttribution;
use Closure;
use Illuminate\Http\Request;

class CaptureProspectReferral
{
    public function __construct(private ProspectReferralAttribution $attribution) {}

    public function handle(Request $request, Closure $next)
    {
        $name = $request->route()?->getName() ?? '';
        $public = array_key_exists($name, config('seo.pages'))
            || (str_starts_with($name, 'legacy.') && array_key_exists(substr($name, 7), config('seo.pages')))
            || in_array($name, ['blog.show', 'register'], true);
        if ($request->isMethod('GET') && $public && $request->query->has('ref')) {
            $this->attribution->capture($request);
        }

        return $next($request);
    }
}
