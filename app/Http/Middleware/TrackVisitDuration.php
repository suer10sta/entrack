<?php

namespace App\Http\Middleware;

use App\Models\Visit;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\View;

class TrackVisitDuration
{
    public function handle(Request $request, Closure $next)
    {
        // Only track GET requests for duration (actual updates are done via /track/visit).
        if (!$request->isMethod('get')) {
            return $next($request);
        }

        // Homepage is handled by the existing '/' route logic.
        if ($request->routeIs('welcome')) {
            return $next($request);
        }

        $visitId = null;

        try {
            $ip = $request->ip();
            $country = null;
            $countryCode = null;
            $city = null;
            $referrer = null;
            $utmSource = null;
            $deviceType = null;
            $isProxy = null;
            $isHosting = null;

            if ($ip && !in_array($ip, ['127.0.0.1', '::1'], true)) {
                try {
                    $response = Http::get('http://ip-api.com/json/' . $ip . '?fields=status,country,countryCode,city,proxy,hosting');
                    if ($response->ok() && ($response['status'] ?? null) === 'success') {
                        $country = $response['country'] ?? null;
                        $countryCode = $response['countryCode'] ?? null;
                        $city = $response['city'] ?? null;
                        $isProxy = isset($response['proxy']) ? (bool) $response['proxy'] : null;
                        $isHosting = isset($response['hosting']) ? (bool) $response['hosting'] : null;
                    }
                } catch (\Throwable $e) {
                    // Ignore geo lookup failures to keep pages responsive.
                }

                // Cross-check reputation with a second provider.
                try {
                    $secondResponse = Http::get('https://api.ipapi.is/?q=' . urlencode($ip));
                    if ($secondResponse->ok()) {
                        $secondData = $secondResponse->json();

                        $secondProxy =
                            (bool) ($secondData['is_proxy'] ?? false)
                            || (bool) ($secondData['is_vpn'] ?? false)
                            || (bool) ($secondData['is_tor'] ?? false);
                        $secondHosting =
                            (bool) ($secondData['is_datacenter'] ?? false)
                            || (bool) ($secondData['is_hosting'] ?? false);

                        $isProxy = is_null($isProxy) ? $secondProxy : ($isProxy || $secondProxy);
                        $isHosting = is_null($isHosting) ? $secondHosting : ($isHosting || $secondHosting);

                        // Keep geo from primary source, but fill missing values from the secondary source.
                        $country = $country ?: ($secondData['location']['country'] ?? null);
                        $countryCode = $countryCode ?: ($secondData['location']['country_code'] ?? null);
                        $city = $city ?: ($secondData['location']['city'] ?? null);
                    }
                } catch (\Throwable $e) {
                    // Ignore secondary provider failures.
                }
            }

            // Basic referrer / UTM / device tracking
            $referrerHeader = $request->headers->get('referer');
            if (is_string($referrerHeader) && $referrerHeader !== '') {
                $referrer = parse_url($referrerHeader, PHP_URL_HOST) ?: $referrerHeader;
            }

            $utmSource = $request->query('utm_source');

            $userAgent = (string) $request->userAgent();
            $uaLower = strtolower($userAgent);
            if (str_contains($uaLower, 'mobile') || str_contains($uaLower, 'android') || str_contains($uaLower, 'iphone')) {
                $deviceType = 'mobile';
            } elseif (str_contains($uaLower, 'tablet') || str_contains($uaLower, 'ipad')) {
                $deviceType = 'tablet';
            } else {
                $deviceType = 'desktop';
            }

            $visit = Visit::create([
                'ip' => $ip,
                'country' => $country,
                'country_code' => $countryCode,
                'city' => $city,
                'url' => $request->fullUrl(),
                'user_agent' => substr($userAgent, 0, 255),
                'referrer' => $referrer,
                'utm_source' => $utmSource,
                'device_type' => $deviceType,
                'is_proxy' => $isProxy,
                'is_hosting' => $isHosting,
                'last_activity_at' => now(),
                'duration_seconds' => 0,
            ]);

            $visitId = $visit?->id;
        } catch (\Throwable $e) {
            // Never block the request if tracking fails.
        }

        View::share('visitId', $visitId);

        return $next($request);
    }
}

