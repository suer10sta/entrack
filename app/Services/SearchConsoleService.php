<?php

namespace App\Services;

use App\Models\SearchConsoleConnection;
use Google\Auth\OAuth2;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpKernel\Exception\HttpException;

class SearchConsoleService
{
    public function oauth(bool $analytics = false): OAuth2
    {
        if (! config('search-console.client_id') || ! config('search-console.client_secret')) {
            throw new HttpException(503, 'Configurez les identifiants Google sur le serveur.');
        }

        return new OAuth2([
            'clientId' => config('search-console.client_id'), 'clientSecret' => config('search-console.client_secret'),
            'redirectUri' => config('search-console.redirect_uri'),
            'authorizationUri' => 'https://accounts.google.com/o/oauth2/v2/auth',
            'tokenCredentialUri' => 'https://oauth2.googleapis.com/token',
            'scope' => $analytics
                ? ['https://www.googleapis.com/auth/webmasters.readonly', 'https://www.googleapis.com/auth/analytics.readonly']
                : 'https://www.googleapis.com/auth/webmasters.readonly',
        ]);
    }

    public function exchange(OAuth2 $oauth): array
    {
        try {
            return $oauth->fetchAuthToken(\Google\Auth\HttpHandler\HttpHandlerFactory::build(new Client(['timeout' => 20, 'connect_timeout' => 5])));
        } catch (\Throwable $e) {
            // Provider exceptions may contain secrets; never return or log them.
            $status = method_exists($e, 'getResponse') ? $e->getResponse()?->getStatusCode() : null;
            throw new HttpException($status === 429 ? 429 : ($status && $status < 500 ? 401 : 502),
                $status === 429 ? 'Quota Google atteint. Réessayez plus tard.' : 'Connexion Google impossible. Reconnectez votre compte.');
        }
    }

    public function storeTokens(int $managerId, array $tokens): SearchConsoleConnection
    {
        $connection = SearchConsoleConnection::firstOrNew(['manager_id' => $managerId]);
        if (empty($tokens['access_token']) || (empty($tokens['refresh_token']) && ! $connection->refresh_token)) {
            throw new HttpException(401, 'Autorisations Google incomplètes. Reconnectez votre compte.');
        }
        $connection->access_token = $tokens['access_token'];
        $connection->refresh_token = $tokens['refresh_token'] ?? $connection->refresh_token;
        $connection->expires_at = now()->addSeconds(max(1, (int) ($tokens['expires_in'] ?? 3600)));
        $connection->save();

        return $connection;
    }

    public function token(int $managerId, bool $force = false, ?string $rejected = null): string
    {
        return Cache::lock('gsc:refresh:'.$managerId, 30)->block(5, function () use ($managerId, $force, $rejected) {
            $connection = SearchConsoleConnection::where('manager_id', $managerId)->first();
            if (! $connection) {
                throw new HttpException(401, 'Connectez votre compte Google Search Console.');
            }
            if ($connection->expires_at->gt(now()->addMinute()) && (! $force || $connection->access_token !== $rejected)) {
                return $connection->access_token;
            }
            $oauth = $this->oauth();
            $oauth->setRefreshToken($connection->refresh_token);

            return $this->storeTokens($managerId, $this->exchange($oauth))->access_token;
        });
    }

    private function request(int $managerId, string $path, ?array $body = null): array
    {
        $token = $this->token($managerId);
        for ($attempt = 0; $attempt < 2; $attempt++) {
            try {
                $client = Http::withToken($token)->acceptJson()->connectTimeout(5)->timeout(25);
                $url = 'https://www.googleapis.com/webmasters/v3/'.$path;
                $response = $body === null ? $client->get($url) : $client->post($url, $body);
            } catch (\Illuminate\Http\Client\ConnectionException $e) {
                throw new HttpException(502, 'Google est temporairement indisponible.');
            }
            if ($response->status() === 401 && $attempt === 0) {
                $token = $this->token($managerId, true, $token);

                continue;
            }
            if ($response->successful()) {
                return $response->json() ?? [];
            }
            $status = $response->status();
            if ($status === 403 && in_array($response->json('error.errors.0.reason'), ['rateLimitExceeded', 'userRateLimitExceeded', 'quotaExceeded'])) {
                $status = 429;
            }
            $messages = [401 => 'Session Google expirée. Reconnectez votre compte.', 403 => 'Accès refusé à cette propriété Search Console.', 404 => 'Propriété ou sitemap introuvable.', 429 => 'Quota Google atteint. Réessayez plus tard.'];
            throw new HttpException(isset($messages[$status]) ? $status : 502, $messages[$status] ?? 'Google est temporairement indisponible.', null, $status === 429 ? ['Retry-After' => '300'] : []);
        }
        throw new HttpException(401, 'Reconnectez votre compte Google.');
    }

    public function report(int $managerId, string $report, string $site, string $start, string $end, array $options = []): array
    {
        $connection = SearchConsoleConnection::where('manager_id', $managerId)->first();
        if (! $connection) {
            throw new HttpException(401, 'Connectez votre compte Google Search Console.');
        }
        $state = $options['data_state'] ?? 'all';
        $type = $options['type'] ?? 'web';
        if (! in_array($state, ['all', 'final'], true) || ! in_array($type, ['web', 'image', 'video', 'news'], true)) {
            throw new HttpException(422, 'Paramètres Search Console invalides.');
        }
        // Version the cache when changing API semantics; never reuse old final-only results.
        $key = 'gsc:v2:'.hash('sha256', json_encode([$managerId, $connection->getRawOriginal('access_token'), $report, $site, $start, $end, $state, $type]));
        $cached = empty($options['refresh']) ? Cache::get($key) : null;
        if ($cached !== null) {
            if (isset($cached['_meta'])) {
                $cached['_meta']['cache_hit'] = true;
            }

            return $cached;
        }
        if ($report === 'sites') {
            $result = $this->request($managerId, 'sites');
        } elseif ($report === 'sitemaps') {
            $result = $this->request($managerId, 'sites/'.rawurlencode($site).'/sitemaps');
        } else {
            $path = 'sites/'.rawurlencode($site).'/searchAnalytics/query';
            $body = ['startDate' => $start, 'endDate' => $end, 'type' => $type, 'dataState' => $state];
            if ($report === 'overview') {
                // No dimensions, filters or row limit: use Google's complete property aggregate.
                $aggregateRequest = $body + ['aggregationType' => 'byProperty'];
                $totals = $this->request($managerId, $path, $aggregateRequest);
                $daily = $this->request($managerId, $path, $aggregateRequest + ['dimensions' => ['date'], 'rowLimit' => 1000]);
                $rows = $daily['rows'] ?? [];
                usort($rows, fn ($a, $b) => strcmp($a['keys'][0], $b['keys'][0]));
                $result = ['totals' => $totals['rows'][0] ?? null, 'rows' => $rows, '_meta' => [
                    'property' => $site, 'request' => $aggregateRequest,
                    'timezone' => 'America/Los_Angeles', 'dates_inclusive' => true,
                    'filters' => [], 'fetched_at' => now('UTC')->toIso8601String(), 'cache_hit' => false,
                    'raw_aggregate_response' => $totals,
                    'daily_metadata' => $daily['metadata'] ?? null,
                    'daily_row_count' => count($rows),
                    // Diagnostic only: neither these sums nor averages of rows drive the KPI cards.
                    'daily_counts' => ['clicks' => array_sum(array_column($rows, 'clicks')), 'impressions' => array_sum(array_column($rows, 'impressions'))],
                    'kpi_source' => 'raw_aggregate_response.rows[0]',
                ]];
                if (config('search-console.debug')) {
                    \Illuminate\Support\Facades\Log::debug('GSC KPI trace', ['manager_id' => $managerId, 'trace' => $result['_meta'], 'backend_totals' => $result['totals']]);
                }
            } else {
                $dimensions = ['queries' => 'query', 'pages' => 'page', 'countries' => 'country', 'devices' => 'device'];
                $result = $this->request($managerId, $path, $body + ['dimensions' => [$dimensions[$report]], 'rowLimit' => 1000]);
            }
        }
        Cache::put($key, $result, now()->addHours(config('search-console.cache_hours')));

        return $result;
    }
}
