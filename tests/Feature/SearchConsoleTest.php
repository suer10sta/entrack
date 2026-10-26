<?php

namespace Tests\Feature;

use App\Models\Manager;
use App\Models\SearchConsoleConnection;
use App\Services\SearchConsoleService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SearchConsoleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'cache.default' => 'array',
            'search-console.client_id' => 'client-test', 'search-console.client_secret' => 'secret-test']);
        Schema::create('managers', function ($table) {
            $table->id();
        });
        \Illuminate\Support\Facades\DB::table('managers')->insert([['id' => 1], ['id' => 2]]);
        (require base_path('database/migrations/2026_10_26_000000_create_search_console_connections_table.php'))->up();
        Http::preventStrayRequests();
    }

    private function login(int $id = 1): void
    {
        $manager = new Manager;
        $manager->forceFill(['id' => $id, 'name' => 'SEO Manager']);
        $this->actingAs($manager, 'manager');
    }

    private function connection(int $id = 1): SearchConsoleConnection
    {
        return app(SearchConsoleService::class)->storeTokens($id, ['access_token' => 'access-secret', 'refresh_token' => 'refresh-secret', 'expires_in' => 3600]);
    }

    public function test_routes_require_manager_authentication(): void
    {
        $this->getJson('/api/seo/sites')->assertUnauthorized();
        $this->getJson('/api/auth/google')->assertUnauthorized();
        $this->getJson('/api/auth/google/callback')->assertUnauthorized();
    }

    public function test_tokens_are_encrypted_and_hidden(): void
    {
        $connection = $this->connection();
        $this->assertNotSame('access-secret', $connection->getRawOriginal('access_token'));
        $this->assertNotSame('refresh-secret', $connection->getRawOriginal('refresh_token'));
        $this->assertArrayNotHasKey('access_token', $connection->toArray());
        $this->assertSame('refresh-secret', $connection->fresh()->refresh_token);
    }

    public function test_oauth_uses_state_offline_access_and_readonly_scope(): void
    {
        $this->login();
        $response = $this->get('/api/auth/google')->assertRedirect();
        parse_str(parse_url($response->headers->get('Location'), PHP_URL_QUERY), $params);
        $this->assertSame('offline', $params['access_type']);
        $this->assertSame('https://www.googleapis.com/auth/webmasters.readonly', $params['scope']);
        $this->assertSame(session('gsc_oauth.state'), $params['state']);
    }

    public function test_invalid_callback_state_never_exchanges_code(): void
    {
        $this->login();
        $this->get('/api/auth/google/callback?code=code&state=invalid')->assertRedirect(route('manager.seo.index'))->assertSessionHas('gsc_error');
        $this->assertSame(0, SearchConsoleConnection::count());
    }

    public function test_valid_callback_stores_tokens_and_consumes_state(): void
    {
        $this->login();
        $service = $this->partialMock(SearchConsoleService::class);
        $service->shouldReceive('exchange')->once()->andReturn(['access_token' => 'new-access', 'refresh_token' => 'new-refresh', 'expires_in' => 3600]);
        $this->withSession(['gsc_oauth' => ['state' => 'valid', 'manager' => 1, 'expires' => now()->addMinute()->timestamp]])
            ->get('/api/auth/google/callback?code=code&state=valid')->assertRedirect(route('manager.seo.index'))->assertSessionHas('gsc_success')->assertSessionMissing('gsc_oauth');
        $this->assertSame('new-refresh', SearchConsoleConnection::first()->refresh_token);
    }

    public function test_overview_uses_aggregate_totals_and_backend_cache(): void
    {
        $this->login();
        $this->connection();
        Http::fake(['*' => Http::response(['rows' => [['clicks' => 12, 'impressions' => 100, 'ctr' => .12, 'position' => 3, 'keys' => ['2026-10-01']]]])]);
        $url = '/api/seo/overview?site=sc-domain:example.com';
        $this->getJson($url)->assertOk()->assertJsonPath('totals.clicks', 12);
        $this->getJson($url)->assertOk();
        Http::assertSentCount(2);
        Http::assertSent(fn ($r) => ! isset($r->data()['dimensions']));
    }

    public function test_other_manager_cannot_read_cached_data(): void
    {
        $this->login();
        $this->connection();
        Http::fake(['*' => Http::response(['siteEntry' => []])]);
        $this->getJson('/api/seo/sites')->assertOk();
        $this->login(2);
        $this->getJson('/api/seo/sites')->assertUnauthorized();
        Http::assertSentCount(1);
    }

    public function test_expired_token_refreshes_and_preserves_refresh_token(): void
    {
        $this->login();
        $connection = $this->connection();
        $connection->update(['expires_at' => now()->subMinute()]);
        $this->partialMock(SearchConsoleService::class)->shouldReceive('exchange')->once()->andReturn(['access_token' => 'renewed', 'expires_in' => 3600]);
        Http::fake(['*' => Http::response(['siteEntry' => []])]);
        $this->getJson('/api/seo/sites')->assertOk();
        Http::assertSent(fn ($r) => $r->hasHeader('Authorization', 'Bearer renewed'));
        $this->assertSame('refresh-secret', $connection->fresh()->refresh_token);
    }

    public function test_provider_errors_are_safe_and_not_cached(): void
    {
        $this->login();
        $this->connection();
        Http::fake(['*' => Http::sequence()->push(['error' => ['message' => 'sensitive-provider-detail']], 403)->push([], 404)->push([], 429)->push(['rows' => []], 200)]);
        foreach ([403, 404, 429] as $status) {
            $this->getJson('/api/seo/queries?site=sc-domain:example.com')->assertStatus($status)->assertDontSee('sensitive-provider-detail');
        }
        $this->getJson('/api/seo/queries?site=sc-domain:example.com')->assertOk();
    }

    public function test_401_refreshes_once_then_returns_safe_error(): void
    {
        $this->login();
        $this->connection();
        $this->partialMock(SearchConsoleService::class)->shouldReceive('exchange')->once()->andReturn(['access_token' => 'renewed', 'expires_in' => 3600]);
        Http::fake(['*' => Http::response([], 401)]);
        $this->getJson('/api/seo/sites')->assertUnauthorized();
        Http::assertSentCount(2);
    }

    public function test_date_validation_and_manager_page(): void
    {
        $this->login();
        $this->getJson('/api/seo/pages?site=test&start=2026-10-10&end=2026-10-01')->assertUnprocessable();
        $this->get('/manager/seo')->assertOk()->assertSee('Google Search Console')->assertSee('Connecter Google');
        $this->connection();
        $this->get('/manager/seo')->assertOk()->assertSee('Top requêtes')->assertSee('Top pages')->assertSee('Position moyenne')->assertSee('État des sitemaps')->assertSee('manager-seo.js');
    }

    public function test_kpis_preserve_raw_aggregate_not_daily_or_table_totals(): void
    {
        $this->login();
        $this->connection();
        $raw = ['clicks' => 2, 'impressions' => 10, 'ctr' => .2, 'position' => 26.3];
        Http::fake(['*' => Http::sequence()->push(['rows' => [$raw], 'responseAggregationType' => 'byProperty'])->push(['rows' => [['keys' => ['2026-10-22'], 'clicks' => 0, 'impressions' => 7, 'ctr' => 0, 'position' => 99]]])]);
        $response = $this->getJson('/api/seo/overview?site=sc-domain:dalytrac.com&start=2026-09-26&end=2026-10-26')->assertOk();
        $this->assertSame($raw, $response->json('totals'));
        $this->assertSame($raw, $response->json('_meta.raw_aggregate_response.rows.0'));
        $response->assertJsonPath('_meta.daily_counts.impressions', 7)->assertJsonPath('_meta.timezone', 'America/Los_Angeles');
        Http::assertSent(function ($request) {
            $data = $request->data();

            return $data['startDate'] === '2026-09-26' && $data['endDate'] === '2026-10-26'
                && $data['dataState'] === 'all' && $data['type'] === 'web' && $data['aggregationType'] === 'byProperty'
                && ! isset($data['dimensions'], $data['rowLimit']) && ! isset($data['dimensionFilterGroups']);
        });
    }

    public function test_cache_separates_data_state_search_type_and_explicit_refresh(): void
    {
        $this->login();
        $this->connection();
        Http::fake(['*' => Http::response(['rows' => []])]);
        $url = '/api/seo/overview?site=sc-domain:dalytrac.com&start=2026-09-26&end=2026-10-26';
        $this->getJson($url)->assertOk()->assertJsonPath('_meta.cache_hit', false);
        $this->getJson($url)->assertOk()->assertJsonPath('_meta.cache_hit', true);
        Http::assertSentCount(2);
        $this->getJson($url.'&data_state=final')->assertOk()->assertJsonPath('_meta.request.dataState', 'final');
        $this->getJson($url.'&type=image')->assertOk()->assertJsonPath('_meta.request.type', 'image');
        $this->getJson($url.'&refresh=1')->assertOk()->assertJsonPath('_meta.cache_hit', false);
        Http::assertSentCount(8);
        $this->getJson($url)->assertOk()->assertJsonPath('_meta.cache_hit', true);
        Http::assertSentCount(8);
    }

    public function test_dates_remain_inclusive_pacific_dates_despite_application_timezone(): void
    {
        $this->login();
        $this->connection();
        config(['app.timezone' => 'Asia/Tokyo']);
        SearchConsoleConnection::query()->update(['expires_at' => now()->addDays(2)]);
        Http::fake(['*' => Http::response(['rows' => []])]);
        $this->getJson('/api/seo/overview?site=sc-domain:dalytrac.com&start=2026-09-26&end=2026-10-26')->assertOk()
            ->assertJsonPath('_meta.request.startDate', '2026-09-26')->assertJsonPath('_meta.request.endDate', '2026-10-26');
    }
}
