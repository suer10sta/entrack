<?php

namespace Tests\Feature;

use App\Enums\ProspectStatus;
use App\Models\Manager;
use App\Models\Prospect;
use App\Services\ProspectReferralAttribution;
use App\Services\ProspectStatusService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Tests\TestCase;

class ProspectReferralTest extends TestCase
{
    private ?string $schema = null;

    private Manager $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $database = config('database.connections.pgsql.database');
        if (! is_string($database) || ! preg_match('/(_test|_testing)$/', $database) || $database === $this->developmentDatabaseName()) {
            throw new RuntimeException('Referral tests require a dedicated PostgreSQL testing database.');
        }
        config(['database.default' => 'pgsql', 'cache.default' => 'array', 'session.driver' => 'array']);
        $name = 'crm_ref_test_'.bin2hex(random_bytes(8));
        DB::statement('CREATE SCHEMA "'.$name.'"');
        $this->schema = $name;
        DB::statement('SET search_path TO "'.$name.'"');
        Schema::create('managers', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->timestamps();
        });
        Schema::create('entreprises', function ($table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });
        Schema::create('users', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->unsignedBigInteger('entreprise_id')->nullable();
            $table->timestamps();
        });
        foreach (glob(database_path('migrations/2026_10_27_00000*_*.php')) as $path) {
            (require $path)->up();
        }
        $this->manager = Manager::create(['name' => 'Manager', 'email' => 'manager@example.test']);
        $this->withoutVite();
        Http::preventStrayRequests();
    }

    protected function tearDown(): void
    {
        try {
            $this->travelBack();
            if ($this->schema !== null) {
                DB::statement('SET search_path TO public');
                DB::statement('DROP SCHEMA "'.$this->schema.'" CASCADE');
            }
        } finally {
            parent::tearDown();
        }
    }

    private function prospect(array $data = []): Prospect
    {
        return Prospect::create($data + ['name' => 'Contact Test', 'email' => 'contact@example.test']);
    }

    private function context(Prospect $prospect): array
    {
        return ['token' => $prospect->public_token, 'prospect_id' => $prospect->id,
            'captured_at' => now()->timestamp, 'utm_source' => 'linkedin', 'utm_medium' => 'message_prive'];
    }

    public function test_manual_creation_generates_unique_tokens_and_the_exact_fixed_url(): void
    {
        $this->actingAs($this->manager, 'manager');
        foreach (['First', 'Second'] as $name) {
            $this->post(route('manager.prospects.store'), ['name' => $name, 'email' => strtolower($name).'@example.test', 'public_token' => 'attacker-supplied'])->assertRedirect();
        }
        $prospects = Prospect::orderBy('id')->get();
        $this->assertNotSame($prospects[0]->public_token, $prospects[1]->public_token);
        foreach ($prospects as $prospect) {
            $this->assertMatchesRegularExpression('/\A[a-f0-9]{48}\z/', $prospect->public_token);
            $this->assertSame('https://dalytrac.com/?utm_source=linkedin&utm_medium=message_prive&ref='.$prospect->public_token, $prospect->linkedin_url);
            parse_str(parse_url($prospect->linkedin_url, PHP_URL_QUERY), $query);
            $this->assertSame(['utm_source' => 'linkedin', 'utm_medium' => 'message_prive', 'ref' => $prospect->public_token], $query);
            $this->assertNotSame((string) $prospect->id, $query['ref']);
            $this->assertStringNotContainsString($prospect->email, $prospect->linkedin_url);
            $this->assertSame(ProspectStatus::Nouveau, $prospect->status);
        }
        $this->assertSame(0, DB::table('prospect_status_history')->count());
    }

    public function test_detail_page_displays_link_and_copy_control_without_a_generation_form(): void
    {
        $prospect = $this->prospect();
        $this->actingAs($this->manager, 'manager')->get(route('manager.prospects.show', $prospect))->assertOk()
            ->assertSee('Lien LinkedIn')->assertSee('Copier le lien')->assertSee($prospect->linkedin_url)
            ->assertSee('prospect-referral-link.js')->assertDontSee('Générer le lien');
    }

    public function test_token_is_stable_after_contact_changes_and_cannot_be_replaced(): void
    {
        $prospect = $this->prospect();
        $token = $prospect->public_token;
        $prospect->update(['email' => 'new@example.test', 'public_token' => 'ignored']);
        $this->assertSame($token, $prospect->fresh()->public_token);
        $prospect->public_token = str_repeat('a', 48);
        $this->expectException(\LogicException::class);
        $prospect->save();
    }

    public function test_ref_resolves_the_prospect_and_is_stored_before_the_home_redirect(): void
    {
        $prospect = $this->prospect();
        $this->assertSame($prospect->id, app(ProspectReferralAttribution::class)->resolve($prospect->public_token)->id);
        $response = $this->get('/?utm_source=linkedin&utm_medium=message_prive&ref='.$prospect->public_token)->assertStatus(301);
        $this->assertStringContainsString('ref='.$prospect->public_token, $response->headers->get('Location'));
        $response->assertSessionHas(ProspectReferralAttribution::SESSION_KEY.'.prospect_id', $prospect->id);
        $response->assertSessionHas(ProspectReferralAttribution::SESSION_KEY.'.utm_source', 'linkedin');
        $this->assertSame(ProspectStatus::Engage, $prospect->fresh()->status);
        $this->assertNull($prospect->fresh()->linked_user_id);
        $this->assertNull($prospect->fresh()->linked_entreprise_id);
        $this->assertSame(1, $prospect->activities()->count());
    }

    public function test_normal_marketing_navigation_preserves_attribution_until_register(): void
    {
        $prospect = $this->prospect();
        $this->get('/?ref='.$prospect->public_token)->assertRedirect();
        $this->get('/fr/produit')->assertOk()->assertSessionHas(ProspectReferralAttribution::SESSION_KEY.'.token', $prospect->public_token);
        $this->get(route('register'))->assertOk()->assertSessionHas(ProspectReferralAttribution::SESSION_KEY.'.prospect_id', $prospect->id);
    }

    public function test_invalid_array_unknown_and_id_refs_associate_no_prospect_and_clear_old_context(): void
    {
        $prospect = $this->prospect();
        foreach (['ref=65', 'ref=unknown', 'ref='.str_repeat('b', 48), 'ref[]=bad'] as $query) {
            $this->withSession([ProspectReferralAttribution::SESSION_KEY => $this->context($prospect)])->get('/?'.$query)->assertRedirect()
                ->assertSessionMissing(ProspectReferralAttribution::SESSION_KEY);
        }
        $this->assertNull(app(ProspectReferralAttribution::class)->resolve('65'));
    }

    public function test_archived_prospects_are_not_resolved(): void
    {
        $prospect = $this->prospect();
        $prospect->delete();
        $this->get('/?ref='.$prospect->public_token)->assertRedirect()->assertSessionMissing(ProspectReferralAttribution::SESSION_KEY);
        $this->assertNull(app(ProspectReferralAttribution::class)->resolve($prospect->public_token));
    }

    public function test_lost_and_client_statuses_do_not_change_when_the_link_is_opened(): void
    {
        foreach ([ProspectStatus::Perdu, ProspectStatus::Client] as $status) {
            $prospect = $this->prospect();
            app(ProspectStatusService::class)->transition($prospect, $status, manager: $this->manager);
            $this->get('/?ref='.$prospect->public_token)->assertRedirect();
            $this->assertSame($status, $prospect->fresh()->status);
            $this->assertSame(1, $prospect->statusHistories()->count());
            $this->assertNull($prospect->fresh()->linked_user_id);
        }
    }

    public function test_last_valid_link_replaces_previous_context_without_trusting_query_utms(): void
    {
        $first = $this->prospect();
        $second = $this->prospect();
        $this->get('/?ref='.$first->public_token)->assertRedirect();
        $this->get('/?ref='.$second->public_token.'&utm_source=forged&utm_medium=forged')->assertRedirect()
            ->assertSessionHas(ProspectReferralAttribution::SESSION_KEY.'.prospect_id', $second->id)
            ->assertSessionHas(ProspectReferralAttribution::SESSION_KEY.'.utm_medium', 'message_prive');
    }

    public function test_attribution_expires_and_is_validated_again_before_consumption(): void
    {
        $prospect = $this->prospect();
        $request = Request::create('/register');
        $request->setLaravelSession(app('session.store'));
        $request->session()->put(ProspectReferralAttribution::SESSION_KEY, $this->context($prospect));
        $service = app(ProspectReferralAttribution::class);
        $this->travel(31)->days();
        $this->assertNull($service->consume($request));
        $this->assertFalse($request->session()->has(ProspectReferralAttribution::SESSION_KEY));
        $this->travelBack();
        $request->session()->put(ProspectReferralAttribution::SESSION_KEY, $this->context($prospect));
        $this->assertSame($prospect->id, $service->consume($request)['prospect_id']);
        $this->assertNull($service->consume($request));
    }

    public function test_storage_failure_does_not_break_the_public_landing_page(): void
    {
        Log::spy();
        Schema::rename('prospects', 'crm_unavailable_for_test');
        $this->get('/?ref='.str_repeat('a', 48))->assertRedirect()->assertSessionMissing(ProspectReferralAttribution::SESSION_KEY);
        Log::shouldHaveReceived('warning')->once();
    }

    public function test_migration_backfills_existing_contacts_without_modifying_status_or_timestamps(): void
    {
        $migration = require database_path('migrations/2026_10_27_000004_add_public_token_to_prospects_table.php');
        $migration->down();
        $time = '2026-01-01 12:00:00+00';
        DB::table('prospects')->insert([
            ['name' => 'Legacy', 'email' => 'old@example.test', 'status' => 'perdu', 'created_at' => $time, 'updated_at' => $time, 'deleted_at' => null],
            ['name' => 'Archived', 'email' => 'archived@example.test', 'status' => 'client', 'created_at' => $time, 'updated_at' => $time, 'deleted_at' => $time],
        ]);
        $before = DB::table('prospects')->orderBy('id')->get(['id', 'status', 'updated_at', 'deleted_at'])->toArray();
        $migration->up();
        $after = DB::table('prospects')->orderBy('id')->get(['id', 'status', 'updated_at', 'deleted_at'])->toArray();
        $this->assertEquals($before, $after);
        $tokens = DB::table('prospects')->pluck('public_token')->all();
        $this->assertCount(2, array_unique($tokens));
        foreach ($tokens as $token) {
            $this->assertMatchesRegularExpression('/\A[a-f0-9]{48}\z/', $token);
        }
        $this->assertSame(0, DB::table('prospect_status_history')->count());
    }

    public function test_database_enforces_unique_tokens(): void
    {
        $first = $this->prospect();
        $second = $this->prospect();
        $this->expectException(\Illuminate\Database\QueryException::class);
        DB::table('prospects')->where('id', $second->id)->update(['public_token' => $first->public_token]);
    }

    public function test_database_requires_a_token(): void
    {
        $prospect = $this->prospect();
        $this->expectException(\Illuminate\Database\QueryException::class);
        DB::table('prospects')->where('id', $prospect->id)->update(['public_token' => null]);
    }

    public function test_link_visit_engages_prospect_without_email_or_analytics_and_records_one_clear_event(): void
    {
        foreach ([ProspectStatus::Nouveau, ProspectStatus::Contacte] as $initial) {
            $prospect = $this->prospect(['email' => null]);
            if ($initial === ProspectStatus::Contacte) {
                app(ProspectStatusService::class)->transition($prospect, $initial, manager: $this->manager);
            }
            $url = '/?utm_source=linkedin&utm_medium=message_prive&ref='.$prospect->public_token;
            $this->withSession(['analytics_context' => ['granted' => false]])->get($url)->assertRedirect();
            $this->get('/fr?utm_source=linkedin&utm_medium=message_prive&ref='.$prospect->public_token)->assertOk();
            $this->get($url)->assertRedirect();
            $this->assertSame(ProspectStatus::Engage, $prospect->fresh()->status);
            $this->assertNull($prospect->fresh()->email);
            $this->assertNull($prospect->fresh()->linked_user_id);
            $this->assertNull($prospect->fresh()->linked_entreprise_id);
            $this->assertSame(1, $prospect->statusHistories()->where('source', 'linkedin_click')->count());
            $this->assertSame(1, $prospect->activities()->where('subject', 'Lien LinkedIn consulté')->count());
            $this->assertSame($initial, $prospect->statusHistories()->where('source', 'linkedin_click')->sole()->from_status);
            app(ProspectStatusService::class)->transition($prospect->fresh(), ProspectStatus::Contacte, manager: $this->manager);
            $this->get($url)->assertRedirect();
            $this->assertSame(ProspectStatus::Contacte, $prospect->fresh()->status);
            $this->assertSame(1, $prospect->activities()->count());
        }
        $this->assertSame(0, DB::table('users')->count());
        $this->assertSame(0, DB::table('entreprises')->count());
        Http::assertNothingSent();
    }

    public function test_preview_crawlers_prefetch_head_and_manager_tests_do_not_engage_prospect(): void
    {
        $prospect = $this->prospect(['email' => null]);
        $url = '/?ref='.$prospect->public_token;
        foreach (['LinkedInBot/1.0', 'facebookexternalhit/1.1', 'Twitterbot/1.0', 'Slackbot-LinkExpanding', 'Googlebot'] as $agent) {
            $this->withHeader('User-Agent', $agent)->get($url)->assertRedirect();
        }
        $this->flushHeaders();
        $this->withHeader('Purpose', 'prefetch')->get($url)->assertRedirect();
        $this->flushHeaders();
        $this->withHeader('Sec-Purpose', 'prefetch;prerender')->get($url)->assertRedirect();
        $this->flushHeaders();
        $this->head($url)->assertRedirect();
        $this->actingAs($this->manager, 'manager')->get($url)->assertRedirect();
        $this->assertSame(ProspectStatus::Nouveau, $prospect->fresh()->status);
        $this->assertSame(0, $prospect->statusHistories()->count());
        $this->assertSame(0, $prospect->activities()->count());
    }

    public function test_link_visits_protect_later_statuses_archived_and_invalid_refs(): void
    {
        foreach ([ProspectStatus::Engage, ProspectStatus::DemoEssai, ProspectStatus::Client, ProspectStatus::Perdu] as $status) {
            $prospect = $this->prospect(['email' => null]);
            app(ProspectStatusService::class)->transition($prospect, $status, manager: $this->manager);
            $this->get('/?ref='.$prospect->public_token)->assertRedirect();
            $this->assertSame($status, $prospect->fresh()->status);
            $this->assertSame(0, $prospect->activities()->count());
        }
        $archived = $this->prospect(['email' => null]);
        $archived->delete();
        $this->get('/?ref='.$archived->public_token)->assertRedirect();
        $this->get('/?ref[]=invalid')->assertRedirect();
        $this->get('/?ref='.str_repeat('a', 48))->assertRedirect();
        $this->get('/?utm_source=linkedin')->assertRedirect();
        $this->assertSame(ProspectStatus::Nouveau, Prospect::withTrashed()->findOrFail($archived->id)->status);
        $this->assertSame(0, DB::table('prospect_activities')->count());
    }

    public function test_engagement_failure_never_blocks_landing_and_can_retry_on_later_visit(): void
    {
        $prospect = $this->prospect(['email' => null]);
        $mock = \Mockery::mock(ProspectStatusService::class);
        $mock->shouldReceive('transition')->once()->andThrow(new RuntimeException('Temporary CRM error'));
        $this->app->instance(ProspectStatusService::class, $mock);
        $this->get('/?ref='.$prospect->public_token)->assertRedirect()->assertSessionHas(ProspectReferralAttribution::SESSION_KEY.'.prospect_id', $prospect->id);
        $this->assertSame(ProspectStatus::Nouveau, $prospect->fresh()->status);
        $this->assertSame(0, $prospect->activities()->count());
        $this->app->forgetInstance(ProspectStatusService::class);
        $this->get('/?ref='.$prospect->public_token)->assertRedirect();
        $this->assertSame(ProspectStatus::Engage, $prospect->fresh()->status);
        $this->assertSame(1, $prospect->activities()->count());
    }
}
