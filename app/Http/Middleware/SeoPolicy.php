<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SeoPolicy
{
    public function handle(Request $request, Closure $next)
    {
        $name = $request->route()?->getName();
        $public = array_key_exists($name ?? '', config('seo.pages')) || $name === 'blog.show';
        // Only canonicalize public GET/HEAD URLs. Never rebuild signed or business URLs.
        if ($public && $request->isMethodSafe()) {
            $path = '/'.trim($request->path(), '/');
            $wrongOrigin = app()->environment('production')
                && $request->getSchemeAndHttpHost() !== config('seo.url');
            if ($wrongOrigin || ($request->getPathInfo() !== '/' && str_ends_with($request->getPathInfo(), '/'))) {
                $origin = app()->environment('production') ? config('seo.url') : $request->getSchemeAndHttpHost();
                return redirect($origin.$path.($request->getQueryString() ? '?'.$request->getQueryString() : ''), 301);
            }
        }
        $response = $next($request);
        if (!$public && $name !== 'sitemap') {
            $response->headers->set('X-Robots-Tag', 'noindex, follow');
        }

        return $response;
    }
}
