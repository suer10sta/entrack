<?php

namespace App\Services;

use App\Models\SearchConsoleConnection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpKernel\Exception\HttpException;

class GoogleAnalyticsService
{
    public function __construct(private SearchConsoleService $google) {}

    public function report(int $managerId, string $report, int $days, bool $refresh = false): array
    {
        $property = (string) config('analytics.property_id');
        if (! preg_match('/^[1-9][0-9]*$/', $property)) {
            throw new HttpException(503, 'Le Property ID GA4 numérique doit être configuré sur le serveur.');
        }
        $connection = SearchConsoleConnection::where('manager_id', $managerId)->first();
        if (! $connection) {
            throw new HttpException(401, 'Connectez votre compte Google pour accéder à Analytics.');
        }
        $definitions = [
            'kpis' => [[], ['activeUsers', 'sessions', 'newUsers', 'screenPageViews']],
            'overview' => [[], ['activeUsers', 'newUsers', 'sessions', 'screenPageViews', 'engagementRate', 'userEngagementDuration', 'engagedSessions', 'eventCount', 'keyEvents']],
            'traffic' => [['date'], ['activeUsers', 'newUsers', 'sessions', 'screenPageViews']],
            'pages' => [['pagePath'], ['screenPageViews', 'activeUsers', 'engagementRate']],
            'sources' => [['sessionDefaultChannelGroup', 'sessionSourceMedium'], ['sessions', 'activeUsers', 'newUsers']],
            'campaigns' => [['sessionCampaignName', 'sessionSourceMedium', 'sessionManualAdContent', 'sessionManualTerm'], ['sessions', 'activeUsers', 'keyEvents']],
            'events' => [['eventName'], ['eventCount', 'totalUsers', 'keyEvents']],
            'countries' => [['country'], ['activeUsers', 'sessions', 'screenPageViews']],
            'devices' => [['deviceCategory'], ['activeUsers', 'sessions', 'screenPageViews']],
        ];
        if (! isset($definitions[$report]) || ! in_array($days, [7, 28, 90, 180], true)) {
            throw new HttpException(422, 'Rapport ou période invalide.');
        }
        [$dimensions, $metrics] = $definitions[$report];
        // Relative dates are evaluated by GA4 in the property's own timezone.
        $body = ['dateRanges' => [['startDate' => $days.'daysAgo', 'endDate' => 'today']],
            'dimensions' => array_map(fn ($name) => ['name' => $name], $dimensions),
            'metrics' => array_map(fn ($name) => ['name' => $name], $metrics), 'limit' => $report === 'overview' ? 1 : 1000];
        if ($dimensions) {
            $body['orderBys'] = $report === 'traffic' ? [['dimension' => ['dimensionName' => 'date']]] : [['metric' => ['metricName' => $metrics[0]], 'desc' => true]];
        }
        if ($report === 'kpis') {
            $base = ['dateRanges' => $body['dateRanges'], 'limit' => 1];
            $body = ['requests' => [
                $base + ['metrics' => $body['metrics']],
                $base + ['metrics' => [['name' => 'totalUsers']], 'dimensionFilter' => ['filter' => [
                    'fieldName' => 'newVsReturning', 'stringFilter' => ['matchType' => 'EXACT', 'value' => 'returning', 'caseSensitive' => true],
                ]]],
                $base + ['metrics' => [['name' => 'keyEvents']], 'dimensionFilter' => ['filter' => [
                    'fieldName' => 'eventName', 'inListFilter' => ['values' => ['sign_up', 'subscription_success'], 'caseSensitive' => true],
                ]]],
            ]];
        }
        $key = 'ga4:v2:'.hash('sha256', json_encode([$managerId, $connection->getRawOriginal('access_token'), $property, $body, now('UTC')->toDateString()]));
        if (! $refresh && ($cached = Cache::get($key)) !== null) {
            $cached['meta']['cache_hit'] = true;

            return $cached;
        }
        $token = $this->google->token($managerId);
        for ($attempt = 0; $attempt < 2; $attempt++) {
            try {
                $response = Http::withToken($token)->acceptJson()->connectTimeout(5)->timeout(30)
                    ->post('https://analyticsdata.googleapis.com/v1beta/properties/'.$property.($report === 'kpis' ? ':batchRunReports' : ':runReport'), $body);
            } catch (\Illuminate\Http\Client\ConnectionException $e) {
                throw new HttpException(502, 'Google Analytics est temporairement indisponible.');
            }
            if ($response->status() === 401 && $attempt === 0) {
                $token = $this->google->token($managerId, true, $token);

                continue;
            }
            if (! $response->successful()) {
                $status = $response->status();
                if ($response->json('error.status') === 'RESOURCE_EXHAUSTED') {
                    $status = 429;
                }
                $messages = [400 => 'Google Analytics ne peut pas traiter ce rapport.', 401 => 'Reconnectez Google pour renouveler l’accès Analytics.', 403 => 'Accès Analytics refusé. Autorisez Analytics et vérifiez vos droits sur cette propriété.', 404 => 'Propriété GA4 introuvable. Vérifiez le Property ID.', 429 => 'Quota Analytics atteint. Réessayez dans quelques minutes.'];
                if ($status === 403 && in_array('ACCESS_TOKEN_SCOPE_INSUFFICIENT', array_column($response->json('error.details') ?? [], 'reason'), true)) {
                    $messages[403] = 'Cliquez sur « Autoriser Analytics » pour ajouter l’accès en lecture à votre connexion Google existante.';
                }
                throw new HttpException(isset($messages[$status]) ? $status : 502, $messages[$status] ?? 'Google Analytics est temporairement indisponible.', null, $status === 429 ? ['Retry-After' => '300'] : []);
            }
            $raw = $response->json();
            if ($report === 'kpis') {
                if (count($raw['reports'] ?? []) !== 3) {
                    throw new HttpException(502, 'Réponse KPI Google Analytics incomplète.');
                }
                $reports = $raw['reports'];
                $totals = [];
                foreach ($metrics as $i => $name) {
                    $totals[$name] = (float) ($reports[0]['rows'][0]['metricValues'][$i]['value'] ?? 0);
                }
                $totals['returningUsers'] = (float) ($reports[1]['rows'][0]['metricValues'][0]['value'] ?? 0);
                $totals['conversions'] = (float) ($reports[2]['rows'][0]['metricValues'][0]['value'] ?? 0);
                $quality = $reports[0]['metadata'] ?? [];
                foreach ($reports as $part) {
                    foreach (['subjectToThresholding', 'dataLossFromOtherRow'] as $flag) {
                        $quality[$flag] = ! empty($quality[$flag]) || ! empty($part['metadata'][$flag]);
                    }
                    if (! empty($part['metadata']['samplingMetadatas'])) {
                        $quality['samplingMetadatas'] = $part['metadata']['samplingMetadatas'];
                    }
                }
                $result = ['totals' => $totals, 'rows' => [], 'meta' => ['property_id' => $property, 'days' => $days,
                    'fetched_at' => now('UTC')->toIso8601String(), 'cache_hit' => false, 'timezone' => $quality['timeZone'] ?? null,
                    'data_quality' => $quality, 'conversion_events' => ['sign_up', 'subscription_success']]];
                Cache::put($key, $result, now()->addHours(config('analytics.cache_hours')));

                return $result;
            }
            $rows = array_map(function ($row) use ($dimensions, $metrics) {
                $result = [];
                foreach ($dimensions as $i => $name) {
                    $result[$name] = $row['dimensionValues'][$i]['value'] ?? '';
                }
                foreach ($metrics as $i => $name) {
                    $result[$name] = (float) ($row['metricValues'][$i]['value'] ?? 0);
                }

                return $result;
            }, $raw['rows'] ?? []);
            $result = ['rows' => $rows, 'totals' => $report === 'overview' ? ($rows[0] ?? array_fill_keys($metrics, 0)) : null,
                'meta' => ['property_id' => $property, 'days' => $days, 'fetched_at' => now('UTC')->toIso8601String(), 'cache_hit' => false,
                    'row_count' => $raw['rowCount'] ?? count($rows), 'truncated' => ($raw['rowCount'] ?? 0) > count($rows),
                    'timezone' => $raw['metadata']['timeZone'] ?? null, 'data_quality' => $raw['metadata'] ?? []]];
            Cache::put($key, $result, now()->addHours(config('analytics.cache_hours')));

            return $result;
        }
        throw new HttpException(401, 'Reconnectez votre compte Google.');
    }
}
