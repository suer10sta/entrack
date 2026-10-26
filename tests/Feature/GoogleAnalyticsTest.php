<?php

namespace Tests\Feature;

use App\Models\Manager;
use App\Services\SearchConsoleService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class GoogleAnalyticsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'cache.default' => 'array',
            'analytics.property_id' => '123456789', 'search-console.client_id' => 'client-test', 'search-console.client_secret' => 'secret-test']);
        Schema::create('managers', fn ($table) => $table->id());
        \Illuminate\Support\Facades\DB::table('managers')->insert([['id' => 1], ['id' => 2]]);
        (require base_path('database/migrations/2026_10_26_000000_create_search_console_connections_table.php'))->up();
        Http::preventStrayRequests();
    }

    private function login(int $id = 1, bool $tokens = true): void
    {
        $manager = new Manager;
        $manager->forceFill(['id' => $id, 'name' => 'Analytics Manager']);
        $this->actingAs($manager, 'manager');
        if ($tokens) {
            app(SearchConsoleService::class)->storeTokens($id, ['access_token' => 'shared-access', 'refresh_token' => 'shared-refresh', 'expires_in' => 3600]);
        }
    }

    public function test_guests_cannot_access_analytics_and_manager_can_render_page(): void
    {
        $this->getJson('/api/seo/google-analytics/overview')->assertUnauthorized();
        $this->getJson('/manager/seo/google-analytics')->assertUnauthorized();
        $this->login();
        $this->get('/manager/seo/google-analytics')->assertOk()->assertSee('Google Analytics')->assertSee('Sources de trafic')->assertSee('manager-analytics.js');
    }

    public function test_incremental_oauth_uses_existing_client_callback_and_both_scopes(): void
    {
        $this->login();
        $response = $this->get('/api/auth/google?integration=analytics')->assertRedirect();
        parse_str(parse_url($response->headers->get('Location'), PHP_URL_QUERY), $params);
        $this->assertStringContainsString('analytics.readonly', $params['scope']);
        $this->assertStringContainsString('webmasters.readonly', $params['scope']);
        $this->assertSame('true', $params['include_granted_scopes']);
        $this->assertSame('manager.seo.analytics', session('gsc_oauth.destination'));
        $this->assertStringEndsWith('/api/auth/google/callback', $params['redirect_uri']);
    }

    public function test_analytics_callback_returns_to_analytics_and_preserves_same_refresh_token(): void
    {
        $this->login();
        $this->partialMock(SearchConsoleService::class)->shouldReceive('exchange')->once()->andReturn(['access_token' => 'upgraded-access', 'expires_in' => 3600]);
        $this->withSession(['gsc_oauth' => ['state' => 'valid', 'manager' => 1, 'expires' => now()->addMinute()->timestamp, 'destination' => 'manager.seo.analytics']])
            ->get('/api/auth/google/callback?code=code&state=valid')->assertRedirect(route('manager.seo.analytics'));
        $this->assertSame(1, \App\Models\SearchConsoleConnection::count());
        $this->assertSame('shared-refresh', \App\Models\SearchConsoleConnection::first()->refresh_token);
    }

    public function test_kpis_come_from_undimensioned_report_and_use_existing_bearer(): void
    {
        $this->login();
        Http::fake(['analyticsdata.googleapis.com/*' => Http::response(['rows' => [['metricValues' => array_map(fn ($value) => ['value' => (string) $value], [42, 35, 115, 299, .6, 840, 69, 500, 20])]], 'rowCount' => 1, 'metadata' => ['timeZone' => 'Europe/Brussels']])]);
        $this->getJson('/api/seo/google-analytics/overview?days=28')->assertOk()
            ->assertJsonPath('totals.activeUsers', 42)->assertJsonPath('totals.screenPageViews', 299)->assertJsonPath('totals.engagementRate', .6)
            ->assertJsonPath('meta.timezone', 'Europe/Brussels');
        Http::assertSent(fn ($r) => $r->hasHeader('Authorization', 'Bearer shared-access') && $r['dimensions'] === [] && $r['dateRanges'][0] === ['startDate' => '28daysAgo', 'endDate' => 'today']);
    }

    public function test_cache_refresh_period_and_manager_isolation(): void
    {
        $this->login();
        Http::fake(['*' => Http::response(['rows' => []])]);
        $url = '/api/seo/google-analytics/overview';
        $this->getJson($url)->assertOk()->assertJsonPath('meta.cache_hit', false);
        $this->getJson($url)->assertOk()->assertJsonPath('meta.cache_hit', true);
        Http::assertSentCount(1);
        $this->getJson($url.'?refresh=1')->assertOk()->assertJsonPath('meta.cache_hit', false);
        $this->getJson($url.'?days=7')->assertOk();
        Http::assertSentCount(3);
        $this->login(2, false);
        $this->getJson($url)->assertUnauthorized();
        Http::assertSentCount(3);
    }

    public function test_all_breakdowns_preserve_dimensions_metrics_and_truncation(): void
    {
        $this->login();
        Http::fake(['*' => Http::response(['rows' => [['dimensionValues' => [['value' => '20261020'], ['value' => 'google / organic']], 'metricValues' => [['value' => '12'], ['value' => '8'], ['value' => '5'], ['value' => '22']]]], 'rowCount' => 2000])]);
        foreach (['traffic', 'pages', 'sources', 'campaigns', 'events', 'countries', 'devices'] as $report) {
            $this->getJson('/api/seo/google-analytics/'.$report)->assertOk()->assertJsonPath('meta.truncated', true)->assertJsonCount(1, 'rows');
        }
        Http::assertSentCount(7);
        Http::assertSent(fn ($r) => $r['dimensions'] === [['name' => 'date']] && $r['orderBys'][0]['dimension']['dimensionName'] === 'date');
    }

    public function test_google_errors_are_safe_and_not_cached(): void
    {
        $this->login();
        Http::fake(['*' => Http::sequence()->push(['error' => ['message' => 'secret provider details']], 403)->push([], 404)->push([], 429)->push(['rows' => []], 200)]);
        foreach ([403, 404, 429] as $status) {
            $this->getJson('/api/seo/google-analytics/overview')->assertStatus($status)->assertDontSee('secret provider details');
        }
        $this->getJson('/api/seo/google-analytics/overview')->assertOk();
    }

    public function test_expired_token_uses_shared_refresh_and_retries_401_only_once(): void
    {
        $this->login();
        $this->partialMock(SearchConsoleService::class)->shouldReceive('exchange')->once()->andReturn(['access_token' => 'renewed', 'expires_in' => 3600]);
        Http::fake(['*' => Http::sequence()->push([], 401)->push(['rows' => []], 200)]);
        $this->getJson('/api/seo/google-analytics/overview')->assertOk();
        Http::assertSentCount(2);
        Http::assertSent(fn ($r) => $r->hasHeader('Authorization', 'Bearer renewed'));
    }

    public function test_invalid_property_and_period_fail_without_google_request(): void
    {
        $this->login();
        $this->getJson('/api/seo/google-analytics/overview?days=999')->assertUnprocessable();
        config(['analytics.property_id' => 'G-MEASUREMENT']);
        $this->getJson('/api/seo/google-analytics/overview')->assertStatus(503);
        Http::assertNothingSent();
    }

    public function test_six_kpis_use_exact_returning_users_and_only_selected_key_events(): void
    {
        $this->login();
        $fixture = ['reports' => [
            ['rows' => [['metricValues' => array_map(fn ($v) => ['value' => (string) $v], [42, 115, 35, 299])]], 'metadata' => ['timeZone' => 'Europe/Brussels']],
            ['rows' => [['metricValues' => [['value' => '13']]]]],
            ['rows' => [['metricValues' => [['value' => '8']]]]],
        ]];
        Http::fake(['analyticsdata.googleapis.com/*' => Http::response($fixture)]);
        foreach ([7, 28, 90, 180] as $days) {
            $url = '/api/seo/google-analytics/kpis?days='.$days;
            $response = $this->getJson($url)->assertOk()->assertJsonPath('meta.cache_hit', false);
            $this->assertSame(['activeUsers' => 42, 'sessions' => 115, 'newUsers' => 35, 'screenPageViews' => 299, 'returningUsers' => 13, 'conversions' => 8], $response->json('totals'));
            $this->getJson($url)->assertOk()->assertJsonPath('meta.cache_hit', true);
            $this->getJson($url.'&refresh=1')->assertOk()->assertJsonPath('meta.cache_hit', false);
        }
        Http::assertSentCount(8);
        foreach (Http::recorded() as [$request]) {
            $this->assertStringEndsWith(':batchRunReports', $request->url());
            $requests = $request['requests'];
            $this->assertSame([['name' => 'totalUsers']], $requests[1]['metrics']);
            $this->assertSame('returning', $requests[1]['dimensionFilter']['filter']['stringFilter']['value']);
            $this->assertSame([['name' => 'keyEvents']], $requests[2]['metrics']);
            $this->assertSame(['sign_up', 'subscription_success'], $requests[2]['dimensionFilter']['filter']['inListFilter']['values']);
            foreach ($requests as $part) {
                $this->assertSame($requests[0]['dateRanges'], $part['dateRanges']);
            }
            $this->assertSame('today', $requests[0]['dateRanges'][0]['endDate']);
        }
        $html = $this->get('/manager/seo/google-analytics')->assertOk()->getContent();
        preg_match_all('/<strong id="ga-([^"]+)"/', $html, $matches);
        $this->assertSame(['activeUsers', 'sessions', 'newUsers', 'returningUsers', 'screenPageViews', 'conversions'], $matches[1]);
    }

    public function test_empty_kpi_reports_are_zero_and_incomplete_batch_is_not_silently_accepted(): void
    {
        $this->login();
        Http::fake(['*' => Http::sequence()->push(['reports' => [[], [], []]])->push(['reports' => [[]]])]);
        $response = $this->getJson('/api/seo/google-analytics/kpis')->assertOk();
        $this->assertSame(0, array_sum($response->json('totals')));
        $this->getJson('/api/seo/google-analytics/kpis?refresh=1')->assertStatus(502);
    }

    public function test_all_periods_share_dates_across_reports_and_have_independent_refreshable_cache(): void
    {
        $this->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class);
        $this->login();
        Http::fake(['analyticsdata.googleapis.com/*' => Http::response(['rows' => []])]);
        $reports = ['overview', 'traffic', 'pages', 'sources', 'campaigns', 'events', 'countries', 'devices'];
        $count = 0;
        foreach ([7, 28, 90, 180] as $days) {
            foreach ($reports as $report) {
                $url = '/api/seo/google-analytics/'.$report.'?days='.$days;
                $this->getJson($url)->assertOk()->assertJsonPath('meta.cache_hit', false)->assertJsonPath('meta.days', $days);
                $count++;
                Http::assertSentCount($count);
                $this->getJson($url)->assertOk()->assertJsonPath('meta.cache_hit', true);
                Http::assertSentCount($count);
            }
            $this->getJson('/api/seo/google-analytics/overview?days='.$days.'&refresh=1')->assertOk()->assertJsonPath('meta.cache_hit', false);
            $count++;
        }
        foreach (Http::recorded() as [$request]) {
            $this->assertSame('today', $request['dateRanges'][0]['endDate']);
            $this->assertContains($request['dateRanges'][0]['startDate'], ['7daysAgo', '28daysAgo', '90daysAgo', '180daysAgo']);
        }
        Http::assertSentCount(36);
    }
}
