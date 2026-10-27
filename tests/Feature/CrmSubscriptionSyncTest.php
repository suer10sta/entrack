<?php

namespace Tests\Feature;

use App\Enums\ProspectStatus;
use App\Http\Controllers\admin\SubscriptionController;
use App\Models\CrmLifecycleEvent;
use App\Models\Entreprise;
use App\Models\Manager;
use App\Models\Prospect;
use App\Models\User;
use App\Services\AnalyticsBusinessEvents;
use App\Services\CrmSubscriptionSync;
use App\Services\ProspectStatusService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Stripe\Checkout\Session;
use Stripe\StripeClient;
use Tests\TestCase;

class CrmSubscriptionSyncTest extends TestCase
{
    private ?string $schema = null;

    private Entreprise $enterprise;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $database = config('database.connections.pgsql.database');
        if (! is_string($database) || ! preg_match('/(_test|_testing)$/', $database) || $database === $this->developmentDatabaseName()) {
            throw new RuntimeException('CRM UI tests require a dedicated PostgreSQL testing database.');
        }
        config(['database.default' => 'pgsql', 'cache.default' => 'array', 'session.driver' => 'array']);
        $name = 'crm_paid_test_'.bin2hex(random_bytes(8));
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
            $table->string('invoice_prefix')->nullable();
            $table->string('invoice_number_format')->nullable();
            $table->string('type_compt')->nullable();
            $table->string('stripe_id')->nullable();
            $table->string('account_status')->nullable();
            $table->boolean('auto_renew')->default(true);
            $table->timestamp('trial_ends_at')->nullable();
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
        config(['analytics.api_secret' => null]);
        $this->enterprise = Entreprise::create(['name' => 'Paid company', 'trial_ends_at' => now()->addDays(14)]);
        $this->user = User::create(['name' => 'Owner', 'email' => 'owner@example.test', 'entreprise_id' => $this->enterprise->id]);
        $this->withoutVite();
        Http::preventStrayRequests();
    }

    protected function tearDown(): void
    {
        try {
            if ($this->schema !== null) {
                DB::statement('SET search_path TO public');
                DB::statement('DROP SCHEMA "'.$this->schema.'" CASCADE');
            }
        } finally {
            parent::tearDown();
        }
    }

    private function checkoutSession(array $overrides = []): Session
    {
        return Session::constructFrom(array_replace_recursive([
            'id' => 'cs_test_confirmed', 'object' => 'checkout.session', 'payment_status' => 'paid',
            'mode' => 'subscription', 'status' => 'complete', 'amount_total' => 2900, 'currency' => 'eur',
            'subscription' => 'sub_first', 'invoice' => 'in_first',
            'metadata' => ['plan_name' => 'solo', 'entreprise_id' => (string) $this->enterprise->id, 'user_id' => (string) $this->user->id],
        ], $overrides));
    }

    private function prospect(ProspectStatus $status = ProspectStatus::DemoEssai): Prospect
    {
        $prospect = Prospect::create(['name' => 'CRM contact', 'email' => 'different@example.test',
            'linked_user_id' => $this->user->id, 'linked_entreprise_id' => $this->enterprise->id, 'linked_at' => now()]);
        if ($status === ProspectStatus::Perdu) {
            $manager = Manager::create(['name' => 'Manager', 'email' => 'manager@example.test']);
            app(ProspectStatusService::class)->transition($prospect, $status, manager: $manager);
        } else {
            app(ProspectStatusService::class)->transition($prospect, $status, 'registration');
        }

        return $prospect->fresh();
    }

    private function activate(): void
    {
        $this->enterprise->forceFill(['type_compt' => 'solo', 'account_status' => 'active'])->save();
    }

    private function receipt(): CrmLifecycleEvent
    {
        return CrmLifecycleEvent::where('event_type', 'subscription_paid')->sole();
    }

    private function success(?Session $session, ?string $sessionId = 'cs_test_confirmed')
    {
        // Exercise the real paymentSuccess checks and activation with Stripe retrieval mocked.
        $enterprise = \Mockery::mock(Entreprise::class)->makePartial();
        $enterprise->setRawAttributes($this->enterprise->fresh()->getAttributes(), true);
        $enterprise->exists = true;
        $enterprise->setTable('entreprises');
        $enterprise->shouldReceive('hasActivePaidSubscription')->andReturn(false);
        $enterprise->shouldReceive('currentSubscription')->andReturn(null);
        $this->user->setRelation('entreprise', $enterprise);
        $this->actingAs($this->user);
        $sessions = \Mockery::mock();
        if ($session && $this->enterprise->fresh()->type_compt !== 'solo') {
            $sessions->shouldReceive('retrieve')->once()->with($sessionId)->andReturn($session);
        } else {
            $sessions->shouldNotReceive('retrieve');
        }
        $client = \Mockery::mock(StripeClient::class);
        $client->shouldReceive('getService')->with('checkout')->andReturn((object) ['sessions' => $sessions]);
        $this->app->bind(StripeClient::class, fn () => $client);

        return app(SubscriptionController::class)->paymentSuccess(Request::create('/payment/success/solo', 'GET', array_filter(['session_id' => $sessionId])), 'solo');
    }

    public function test_confirmed_payment_activates_company_and_converts_with_history_and_activity(): void
    {
        $prospect = $this->prospect();
        $response = $this->success($this->checkoutSession());
        $this->assertSame(route('dashboard'), $response->getTargetUrl());
        $this->assertSame('solo', $this->enterprise->fresh()->type_compt);
        $this->assertSame('active', $this->enterprise->fresh()->account_status);
        $this->assertSame(ProspectStatus::Client, $prospect->fresh()->status);
        $this->assertNotNull($prospect->fresh()->first_paid_at);
        $history = $prospect->statusHistories()->where('source', 'stripe')->sole();
        $this->assertSame(ProspectStatus::DemoEssai, $history->from_status);
        $this->assertSame(ProspectStatus::Client, $history->to_status);
        $this->assertSame('converted', $this->receipt()->payload['state']);
        $this->assertSame($prospect->id, $this->receipt()->prospect_id);
        $this->assertSame($this->user->id, $this->receipt()->user_id);
        $this->assertSame('cs_test_confirmed', $this->receipt()->payload['checkout_session_id']);
        $this->assertSame('Abonnement DalyTrac payé', $prospect->activities()->sole()->subject);
        $manager = Manager::create(['name' => 'Manager', 'email' => 'manager@example.test']);
        $this->actingAs($manager, 'manager')->get(route('manager.prospects.show', $prospect))->assertOk()
            ->assertSee('Abonnement DalyTrac payé')->assertSee('Démo / Essai')->assertSee('Client');
    }

    public function test_unpaid_or_wrong_metadata_never_activates_or_converts(): void
    {
        $prospect = $this->prospect();
        foreach ([['payment_status' => 'unpaid'], ['metadata' => ['plan_name' => 'pro']], ['metadata' => ['entreprise_id' => '99999']]] as $override) {
            $this->assertSame(route('profile.upgrade'), $this->success($this->checkoutSession($override))->getTargetUrl());
            $this->assertNull($this->enterprise->fresh()->type_compt);
            $this->assertSame(ProspectStatus::DemoEssai, $prospect->fresh()->status);
            $this->assertSame(0, CrmLifecycleEvent::count());
        }
    }

    public function test_missing_session_url_cannot_convert(): void
    {
        $prospect = $this->prospect();
        $this->assertSame(route('profile.upgrade'), $this->success(null, null)->getTargetUrl());
        $this->assertNull($this->enterprise->fresh()->type_compt);
        $this->assertSame(ProspectStatus::DemoEssai, $prospect->fresh()->status);
        $this->assertSame(0, CrmLifecycleEvent::count());
    }

    public function test_refresh_and_service_replay_do_not_duplicate_or_change_first_date(): void
    {
        $prospect = $this->prospect();
        $this->success($this->checkoutSession());
        $at = $prospect->fresh()->first_paid_at->toISOString();
        $count = CrmLifecycleEvent::count();
        $this->travel(2)->hours();
        $this->success($this->checkoutSession());
        app(CrmSubscriptionSync::class)->paid($this->enterprise->fresh(), $this->user, $this->checkoutSession());
        $this->assertSame($at, $prospect->fresh()->first_paid_at->toISOString());
        $this->assertSame($count, CrmLifecycleEvent::count());
        $this->assertSame(1, $prospect->statusHistories()->where('source', 'stripe')->count());
        $this->assertSame(1, $prospect->activities()->count());
    }

    public function test_already_client_and_lost_are_preserved_without_conversion_entries(): void
    {
        foreach ([ProspectStatus::Client, ProspectStatus::Perdu] as $i => $status) {
            $prospect = $this->prospect($status);
            $this->activate();
            app(CrmSubscriptionSync::class)->paid($this->enterprise, $this->user, $this->checkoutSession(['id' => 'cs_test_'.$i]));
            $this->assertSame($status, $prospect->fresh()->status);
            $this->assertNull($prospect->fresh()->first_paid_at);
            $this->assertSame(0, $prospect->statusHistories()->where('source', 'stripe')->count());
            $this->assertSame(0, $prospect->activities()->count());
            $prospect->delete();
        }
        $this->assertSame(['already_client', 'lost_preserved'], CrmLifecycleEvent::orderBy('id')->get()->pluck('payload.state')->all());
    }

    public function test_no_match_or_archived_match_does_not_block_activation_or_create_prospect(): void
    {
        $prospect = $this->prospect();
        $prospect->delete();
        $this->assertSame(route('dashboard'), $this->success($this->checkoutSession())->getTargetUrl());
        $this->assertSame('active', $this->enterprise->fresh()->account_status);
        $this->assertSame('no_match', $this->receipt()->payload['state']);
        $this->assertSame(0, Prospect::count());
        $this->assertSame(1, Prospect::withTrashed()->count());
    }

    public function test_ambiguous_enterprise_preserves_statuses_and_records_conflict(): void
    {
        $one = $this->prospect();
        $two = $this->prospect();
        $this->assertSame(route('dashboard'), $this->success($this->checkoutSession())->getTargetUrl());
        $this->assertSame('active', $this->enterprise->fresh()->account_status);
        $this->assertSame(ProspectStatus::DemoEssai, $one->fresh()->status);
        $this->assertSame(ProspectStatus::DemoEssai, $two->fresh()->status);
        $this->assertSame('conflict', $this->receipt()->payload['state']);
        $this->assertSame('multiple_linked_prospects', $this->receipt()->payload['reason']);
        $this->assertSame([$one->id, $two->id], $this->receipt()->payload['prospect_ids']);
    }

    public function test_analytics_failure_is_never_consulted(): void
    {
        $this->mock(AnalyticsBusinessEvents::class)->shouldNotReceive('payment');
        $prospect = $this->prospect();
        $this->success($this->checkoutSession());
        $this->assertSame(ProspectStatus::Client, $prospect->fresh()->status);
        Http::assertNothingSent();
    }

    public function test_crm_failure_rolls_back_crm_but_keeps_payment_and_diagnostic(): void
    {
        $prospect = $this->prospect();
        $this->mock(ProspectStatusService::class)->shouldReceive('transition')->once()->andThrow(new \DomainException('Simulated CRM failure'));
        $this->assertSame(route('dashboard'), $this->success($this->checkoutSession())->getTargetUrl());
        $this->assertSame('active', $this->enterprise->fresh()->account_status);
        $this->assertSame('solo', $this->enterprise->fresh()->type_compt);
        $this->assertSame(ProspectStatus::DemoEssai, $prospect->fresh()->status);
        $this->assertNull($prospect->fresh()->first_paid_at);
        $this->assertSame('failed', $this->receipt()->payload['state']);
        $this->assertNull($this->receipt()->processed_at);
        $this->assertSame(0, $prospect->activities()->count());
    }

    public function test_earlier_pipeline_statuses_follow_central_forward_transition_rules(): void
    {
        $prospect = $this->prospect(ProspectStatus::Engage);
        $this->success($this->checkoutSession());
        $this->assertSame(ProspectStatus::Client, $prospect->fresh()->status);
        $this->assertTrue($this->receipt()->payload['historical_status']);
        $this->assertSame('engage', $this->receipt()->payload['from_status']);
    }

    public function test_another_checkout_for_existing_client_does_not_change_conversion_date(): void
    {
        $prospect = $this->prospect();
        $this->success($this->checkoutSession());
        $at = $prospect->fresh()->first_paid_at->toISOString();
        app(CrmSubscriptionSync::class)->paid($this->enterprise->fresh(), $this->user, $this->checkoutSession(['id' => 'cs_test_second']));
        $this->assertSame($at, $prospect->fresh()->first_paid_at->toISOString());
        $this->assertSame(1, $prospect->activities()->count());
        $this->assertSame(1, $prospect->statusHistories()->where('source', 'stripe')->count());
    }

    public function test_service_rejects_unpaid_or_user_mismatch(): void
    {
        $prospect = $this->prospect();
        $this->activate();
        app(CrmSubscriptionSync::class)->paid($this->enterprise, $this->user, $this->checkoutSession(['payment_status' => 'unpaid']));
        $this->assertSame(0, CrmLifecycleEvent::count());
        app(CrmSubscriptionSync::class)->paid($this->enterprise, $this->user, $this->checkoutSession(['metadata' => ['user_id' => '99999']]));
        $this->assertSame('checkout_user_mismatch', $this->receipt()->payload['reason']);
        $this->assertSame(ProspectStatus::DemoEssai, $prospect->fresh()->status);
    }

    public function test_no_crm_contact_still_activates_paid_subscription(): void
    {
        $this->assertSame(route('dashboard'), $this->success($this->checkoutSession())->getTargetUrl());
        $this->assertSame('solo', $this->enterprise->fresh()->type_compt);
        $this->assertSame('no_match', $this->receipt()->payload['state']);
        $this->assertSame(0, Prospect::count());
    }

    public function test_failure_after_status_transition_rolls_back_every_crm_write_and_can_be_replayed(): void
    {
        $prospect = $this->prospect();
        $this->activate();
        Schema::rename('prospect_activities', 'temporarily_unavailable_activities');
        try {
            app(CrmSubscriptionSync::class)->paid($this->enterprise, $this->user, $this->checkoutSession());
            $this->assertSame(ProspectStatus::DemoEssai, $prospect->fresh()->status);
            $this->assertNull($prospect->fresh()->first_paid_at);
            $this->assertSame(0, $prospect->statusHistories()->where('source', 'stripe')->count());
            $this->assertSame(1, CrmLifecycleEvent::count());
            $this->assertSame('failed', $this->receipt()->payload['state']);
        } finally {
            Schema::rename('temporarily_unavailable_activities', 'prospect_activities');
        }
        app(CrmSubscriptionSync::class)->paid($this->enterprise, $this->user, $this->checkoutSession());
        $this->assertSame(ProspectStatus::Client, $prospect->fresh()->status);
        $this->assertSame('converted', $this->receipt()->payload['state']);
        $this->assertSame(1, $prospect->activities()->count());
    }

    public function test_same_checkout_cannot_reconvert_a_manually_lost_prospect(): void
    {
        $prospect = $this->prospect();
        $this->success($this->checkoutSession());
        $manager = Manager::create(['name' => 'Manager', 'email' => 'manager@example.test']);
        app(ProspectStatusService::class)->transition($prospect, ProspectStatus::Perdu, manager: $manager);
        app(CrmSubscriptionSync::class)->paid($this->enterprise->fresh(), $this->user, $this->checkoutSession());
        $this->assertSame(ProspectStatus::Perdu, $prospect->fresh()->status);
        $this->assertSame(1, $prospect->activities()->count());
    }

    public function test_confirmation_timestamp_remains_utc_with_a_non_utc_database_session(): void
    {
        $prospect = $this->prospect();
        $this->activate();
        $timezone = DB::selectOne("SELECT current_setting('TimeZone') AS zone")->zone;
        try {
            DB::select("SELECT set_config('TimeZone', ?, false)", ['Europe/Paris']);
            $this->travelTo(\Carbon\CarbonImmutable::parse('2026-10-27T12:00:00Z'));
            app(CrmSubscriptionSync::class)->paid($this->enterprise, $this->user, $this->checkoutSession());
            $this->assertSame('2026-10-27T12:00:00.000000Z', $this->receipt()->occurred_at->toISOString());
            $this->assertSame('2026-10-27T12:00:00.000000Z', $prospect->fresh()->first_paid_at->toISOString());
        } finally {
            DB::select("SELECT set_config('TimeZone', ?, false)", [$timezone]);
        }
    }
}
