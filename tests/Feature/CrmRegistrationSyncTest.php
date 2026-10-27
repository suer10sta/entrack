<?php

namespace Tests\Feature;

use App\Enums\ProspectStatus;
use App\Models\CrmLifecycleEvent;
use App\Models\Entreprise;
use App\Models\Manager;
use App\Models\Prospect;
use App\Models\Role;
use App\Models\User;
use App\Services\AnalyticsBusinessEvents;
use App\Services\CrmRegistrationSync;
use App\Services\ProspectStatusService;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use RuntimeException;
use Tests\TestCase;

class CrmRegistrationSyncTest extends TestCase
{
    private ?string $schema = null;

    protected function setUp(): void
    {
        parent::setUp();
        $database = config('database.connections.pgsql.database');
        if (! is_string($database) || ! preg_match('/(_test|_testing)$/', $database) || $database === $this->developmentDatabaseName()) {
            throw new RuntimeException('CRM registration tests require a dedicated PostgreSQL testing database.');
        }
        config(['database.default' => 'pgsql', 'cache.default' => 'array', 'session.driver' => 'array', 'analytics.api_secret' => null]);
        $name = 'crm_register_test_'.bin2hex(random_bytes(8));
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
            $table->boolean('is_activated')->default(true);
            $table->timestampTz('duration')->nullable();
            $table->string('image')->nullable();
            $table->string('type_compt')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamps();
        });
        Schema::create('users', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('password')->nullable();
            $table->string('locale')->nullable();
            $table->unsignedBigInteger('entreprise_id')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
        Schema::create('roles', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
        });
        Schema::create('permissions', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
        });
        Schema::create('model_has_roles', function ($table) {
            $table->unsignedBigInteger('role_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->primary(['role_id', 'model_id', 'model_type']);
        });
        Schema::create('model_has_permissions', function ($table) {
            $table->unsignedBigInteger('permission_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
        });
        Schema::create('role_has_permissions', function ($table) {
            $table->unsignedBigInteger('role_id');
            $table->unsignedBigInteger('permission_id');
        });
        Schema::create('employees', function ($table) {
            $table->id();
            foreach (['name', 'email', 'password', 'phoneNumber', 'age', 'cnss', 'bankAccountNumber', 'image'] as $field) {
                $table->string($field)->nullable();
            }
            foreach (['salary', 'cost_per_hour', 'Cotisation_CNSS', 'workHours_limit', 'Cotisation_AMO'] as $field) {
                $table->decimal($field)->nullable();
            }
            $table->timestamp('Date_Entree')->nullable();
            $table->timestamp('Date_Release')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('entreprise_id');
            $table->timestamps();
        });
        Schema::create('work_days_repports', function ($table) {
            $table->id();
            foreach (['work_days_per_month', 'days_worked', 'sick_days', 'holidays', 'days_of_unpaid_absence', 'remaining_days_of_paid_leave', 'number_of_days_of_paid_leave_acquired', 'number_of_days_of_paid_leave_taken', 'holiday_balance', 'remarque', 'month', 'year'] as $field) {
                $table->decimal($field, 12, 2)->nullable();
            }
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('entreprise_id');
            $table->timestamps();
        });
        foreach (glob(database_path('migrations/2026_10_27_00000*_*.php')) as $path) {
            (require $path)->up();
        }
        Role::create(['name' => 'Admin', 'guard_name' => 'web']);
        Role::create(['name' => 'SuperAdmin', 'guard_name' => 'web']);
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        $this->withoutVite();
        // Subscription enforcement is unrelated to these minimal registration fixtures.
        $this->withoutMiddleware(\App\Http\Middleware\CheckSubscription::class);
        Http::preventStrayRequests();
        Notification::fake();
        $this->travelTo(now('UTC')->setDate(2026, 10, 27)->setTime(12, 0));
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

    private function account(string $email = 'ahmed@example.test', bool $prepare = true): User
    {
        $enterprise = Entreprise::create(['name' => 'Compte test', 'trial_ends_at' => now()->addDays(15)]);
        $user = User::create(['name' => 'Ahmed Test', 'email' => $email, 'entreprise_id' => $enterprise->id]);
        if ($prepare) {
            app(CrmRegistrationSync::class)->registered($user);
        }

        return $user;
    }

    private function prospect(array $attributes = []): Prospect
    {
        return Prospect::create($attributes + ['name' => 'Ahmed Prospect', 'email' => 'ahmed@example.test']);
    }

    private function verify(User $user): void
    {
        $user->markEmailAsVerified();
        event(new Verified($user));
    }

    private function receipt(User $user): CrmLifecycleEvent
    {
        return CrmLifecycleEvent::where('source', 'registration')->where('event_key', 'user:'.$user->id.':registered')->firstOrFail();
    }

    public function test_no_match_never_creates_a_prospect(): void
    {
        $user = $this->account();
        $this->verify($user);
        $this->assertSame(0, Prospect::count());
        $this->assertSame('no_match', $this->receipt($user)->payload['state']);
        $this->assertNotNull($this->receipt($user)->processed_at);
    }

    public function test_preparation_never_links_or_changes_status_before_real_verification(): void
    {
        $prospect = $this->prospect();
        $user = $this->account();
        event(new Verified($user)); // A forged/stale event is insufficient.
        $this->assertNull($prospect->fresh()->linked_user_id);
        $this->assertNull($prospect->fresh()->linked_entreprise_id);
        $this->assertSame(ProspectStatus::Nouveau, $prospect->fresh()->status);
        $this->assertSame('awaiting_verification', $this->receipt($user)->payload['state']);
        $this->assertSame(0, $prospect->statusHistories()->count());
    }

    public function test_verification_links_and_records_real_trial_date_not_verification_date(): void
    {
        $prospect = $this->prospect();
        $user = $this->account();
        $trialStart = $user->entreprise->created_at->toISOString();
        $trialEnd = $user->entreprise->trial_ends_at->toISOString();
        $this->travel(2)->days();
        $this->verify($user);
        $prospect->refresh();
        $this->assertSame($user->id, $prospect->linked_user_id);
        $this->assertSame($user->entreprise_id, $prospect->linked_entreprise_id);
        $this->assertSame($user->email_verified_at->toISOString(), $prospect->linked_at->toISOString());
        $this->assertSame($trialStart, $prospect->trial_started_at->toISOString());
        $this->assertSame($trialEnd, $user->entreprise->fresh()->trial_ends_at->toISOString());
        $this->assertSame(ProspectStatus::DemoEssai, $prospect->status);
        $history = $prospect->statusHistories()->sole();
        $this->assertSame('registration', $history->source);
        $this->assertSame(ProspectStatus::Nouveau, $history->from_status);
        $this->assertSame(ProspectStatus::DemoEssai, $history->to_status);
        $this->assertSame($user->email_verified_at->toISOString(), $history->occurred_at->toISOString());
        $this->assertSame('linked', $this->receipt($user)->payload['state']);
        $this->assertSame($prospect->id, $this->receipt($user)->prospect_id);
        $this->assertSame(1, $prospect->activities()->count());
    }

    public function test_replayed_registration_and_verification_create_no_duplicates_or_regression(): void
    {
        $prospect = $this->prospect();
        $user = $this->account();
        $this->verify($user);
        $linkedAt = $prospect->fresh()->linked_at;
        $manager = Manager::create(['name' => 'Manager', 'email' => 'manager@example.test']);
        app(ProspectStatusService::class)->transition($prospect, ProspectStatus::Client, manager: $manager);
        app(CrmRegistrationSync::class)->registered($user);
        event(new Verified($user));
        event(new Verified($user));
        $this->assertSame(ProspectStatus::Client, $prospect->fresh()->status);
        $this->assertEquals($linkedAt, $prospect->fresh()->linked_at);
        $this->assertSame(2, $prospect->statusHistories()->count());
        $this->assertSame(1, $prospect->activities()->count());
        $this->assertSame(2, CrmLifecycleEvent::count()); // Account receipt + service transition receipt.
    }

    public function test_normalization_preserves_dots_and_plus_suffixes(): void
    {
        $prospect = $this->prospect(['email' => '  Ahmed.Name+CRM@Example.test  ']);
        $this->prospect(['email' => 'ahmedname@example.test']);
        $user = $this->account('ahmed.name+crm@example.test');
        $this->verify($user);
        $this->assertSame($user->id, $prospect->fresh()->linked_user_id);
        $this->assertSame(1, Prospect::whereNotNull('linked_user_id')->count());
    }

    public function test_duplicate_prospects_refuse_matching_and_leave_an_exploitable_conflict(): void
    {
        Log::spy();
        $first = $this->prospect();
        $second = $this->prospect(['email' => ' Ahmed@Example.test ']);
        $user = $this->account();
        $this->verify($user);
        $event = $this->receipt($user);
        $this->assertSame('conflict', $event->payload['state']);
        $this->assertSame('duplicate_prospect_email', $event->payload['reason']);
        $this->assertEqualsCanonicalizing([$first->id, $second->id], $event->payload['conflicting_prospect_ids']);
        $this->assertSame(0, Prospect::whereNotNull('linked_user_id')->count());
        $this->assertSame(0, DB::table('prospect_status_history')->count());
        Log::shouldHaveReceived('warning')->once();
    }

    public function test_duplicate_users_refuse_matching_even_if_other_user_is_unverified(): void
    {
        $prospect = $this->prospect();
        $this->account("\t Ahmed@Example.test \n", false);
        $user = $this->account();
        $this->verify($user);
        $this->assertSame('duplicate_user_email', $this->receipt($user)->payload['reason']);
        $this->assertNull($prospect->fresh()->linked_user_id);
    }

    public function test_existing_or_partial_account_links_are_never_replaced(): void
    {
        $other = $this->account('other@example.test', false);
        $prospect = $this->prospect(['linked_user_id' => $other->id, 'linked_entreprise_id' => $other->entreprise_id]);
        $user = $this->account();
        $this->verify($user);
        $this->assertSame($other->id, $prospect->fresh()->linked_user_id);
        $this->assertSame($other->entreprise_id, $prospect->fresh()->linked_entreprise_id);
        $this->assertSame('prospect_already_linked', $this->receipt($user)->payload['reason']);
        $this->assertSame(0, $prospect->activities()->count());
        $prospect->update(['linked_user_id' => null]);
        $this->assertSame($other->entreprise_id, $prospect->fresh()->linked_entreprise_id);
        event(new Verified($user));
        $this->assertNull($prospect->fresh()->linked_user_id);
    }

    public function test_fully_linked_same_account_can_finish_once_without_moving_links(): void
    {
        $user = $this->account();
        $prospect = $this->prospect(['linked_user_id' => $user->id, 'linked_entreprise_id' => $user->entreprise_id]);
        $this->verify($user);
        event(new Verified($user));
        $this->assertSame(ProspectStatus::DemoEssai, $prospect->fresh()->status);
        $this->assertSame(1, $prospect->activities()->count());
        $this->assertSame(1, $prospect->statusHistories()->count());
    }

    public function test_client_and_lost_statuses_remain_unchanged_while_linking(): void
    {
        $manager = Manager::create(['name' => 'Manager', 'email' => 'manager@example.test']);
        foreach ([ProspectStatus::Client, ProspectStatus::Perdu] as $status) {
            $email = $status->value.'@example.test';
            $prospect = $this->prospect(['email' => $email]);
            app(ProspectStatusService::class)->transition($prospect, $status, manager: $manager);
            $user = $this->account($email);
            $this->verify($user);
            $this->assertSame($status, $prospect->fresh()->status);
            $this->assertSame($user->id, $prospect->fresh()->linked_user_id);
            $this->assertSame(1, $prospect->statusHistories()->count());
            $this->assertTrue($this->receipt($user)->payload['status_preserved']);
        }
    }

    public function test_contacted_engaged_and_existing_trial_are_supported(): void
    {
        $manager = Manager::create(['name' => 'Manager', 'email' => 'manager@example.test']);
        foreach ([ProspectStatus::Contacte, ProspectStatus::Engage, ProspectStatus::DemoEssai] as $status) {
            $email = $status->value.'@example.test';
            $prospect = $this->prospect(['email' => $email]);
            app(ProspectStatusService::class)->transition($prospect, $status, manager: $manager);
            $user = $this->account($email);
            $this->verify($user);
            $this->assertSame(ProspectStatus::DemoEssai, $prospect->fresh()->status);
            $this->assertSame($status === ProspectStatus::DemoEssai ? 1 : 2, $prospect->statusHistories()->count());
        }
    }

    public function test_old_accounts_without_register_marker_are_never_backfilled(): void
    {
        $prospect = $this->prospect();
        $user = $this->account(prepare: false);
        $this->verify($user);
        $this->assertNull($prospect->fresh()->linked_user_id);
        $this->assertSame(0, CrmLifecycleEvent::count());
    }

    public function test_archived_prospect_is_ignored_and_email_change_requires_manual_resolution(): void
    {
        $archived = $this->prospect();
        $archived->delete();
        $user = $this->account();
        $this->verify($user);
        $this->assertSame('no_match', $this->receipt($user)->payload['state']);
        $other = $this->account('initial@example.test');
        $prospect = $this->prospect(['email' => 'changed@example.test']);
        $other->update(['email' => 'changed@example.test']);
        $this->verify($other);
        $this->assertSame('account_identity_changed', $this->receipt($other)->payload['reason']);
        $this->assertNull($prospect->fresh()->linked_user_id);
    }

    public function test_automatic_loss_is_rejected_by_the_shared_service(): void
    {
        $prospect = $this->prospect();
        $this->expectException(\DomainException::class);
        app(ProspectStatusService::class)->transition($prospect, ProspectStatus::Perdu, 'registration');
    }

    public function test_crm_failure_rolls_back_linking_is_logged_and_can_be_replayed(): void
    {
        Log::spy();
        $prospect = $this->prospect();
        $user = $this->account();
        $failing = \Mockery::mock(ProspectStatusService::class);
        $failing->shouldReceive('transition')->once()->andThrow(new RuntimeException('Simulated CRM failure'));
        $user->markEmailAsVerified();
        (new CrmRegistrationSync($failing))->verified($user);
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $this->assertNull($prospect->fresh()->linked_user_id);
        $this->assertSame(0, $prospect->activities()->count());
        $this->assertSame('failed', $this->receipt($user)->payload['state']);
        $this->assertNull($this->receipt($user)->processed_at);
        Log::shouldHaveReceived('error')->once();
        app(CrmRegistrationSync::class)->verified($user);
        $this->assertSame($user->id, $prospect->fresh()->linked_user_id);
        $this->assertSame(1, $prospect->statusHistories()->count());
    }

    public function test_register_and_signed_verification_routes_keep_the_existing_flow_without_analytics(): void
    {
        $this->mock(AnalyticsBusinessEvents::class)->shouldReceive('registration')->once();
        $prospect = $this->prospect(['email' => ' Ahmed@Example.test ']);
        $this->post('/register', ['full_name' => 'Ahmed Register', 'email' => 'ahmed@example.test', 'password' => 'Password123!', 'password_confirmation' => 'Password123!', 'terms_accepted' => '1'])->assertRedirect(route('verification.notice'));
        $user = User::where('email', 'ahmed@example.test')->sole();
        $this->assertAuthenticatedAs($user);
        $this->assertSame(1, DB::table('employees')->where('user_id', $user->id)->count());
        $this->assertSame(1, DB::table('work_days_repports')->count());
        $this->assertNull($prospect->fresh()->linked_user_id);
        $this->assertFalse($user->hasVerifiedEmail());
        $this->assertSame('awaiting_verification', $this->receipt($user)->payload['state']);
        $url = URL::temporarySignedRoute('verification.verify', now()->addHour(), ['id' => $user->id, 'hash' => sha1($user->getEmailForVerification())]);
        $this->get($url)->assertRedirect('/dashboard?verified=1');
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $this->assertSame($user->id, $prospect->fresh()->linked_user_id);
        $this->get($url)->assertRedirect('/dashboard?verified=1');
        $this->assertSame(1, $prospect->statusHistories()->count());
        $this->assertSame(1, $prospect->activities()->count());
        Http::assertNothingSent();
    }

    public function test_register_without_prospect_and_missing_crm_table_does_not_invalidate_account(): void
    {
        Log::spy();
        $this->mock(AnalyticsBusinessEvents::class)->shouldReceive('registration')->once();
        Schema::rename('crm_lifecycle_events', 'crm_unavailable_for_test');
        $this->post('/register', ['full_name' => 'Independent Account', 'email' => 'independent@example.test', 'password' => 'Password123!', 'password_confirmation' => 'Password123!', 'terms_accepted' => '1'])->assertRedirect(route('verification.notice'));
        $user = User::where('email', 'independent@example.test')->sole();
        $url = URL::temporarySignedRoute('verification.verify', now()->addHour(), ['id' => $user->id, 'hash' => sha1($user->getEmailForVerification())]);
        $this->get($url)->assertRedirect('/dashboard?verified=1');
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $this->assertSame(0, Prospect::count());
        Log::shouldHaveReceived('error')->atLeast()->once();
    }

    public function test_invalid_verification_signature_or_hash_cannot_trigger_matching(): void
    {
        $prospect = $this->prospect();
        $user = $this->account();
        $this->get(route('verification.verify', ['id' => $user->id, 'hash' => sha1($user->email)]))->assertForbidden();
        $url = URL::temporarySignedRoute('verification.verify', now()->addHour(), ['id' => $user->id, 'hash' => sha1('wrong@example.test')]);
        $this->get($url)->assertForbidden();
        $this->assertFalse($user->fresh()->hasVerifiedEmail());
        $this->assertNull($prospect->fresh()->linked_user_id);
    }

    public function test_signed_callback_replay_retries_a_failed_receipt_without_reverifying_email(): void
    {
        $prospect = $this->prospect();
        $user = $this->account();
        $failing = \Mockery::mock(ProspectStatusService::class);
        $failing->shouldReceive('transition')->once()->andThrow(new RuntimeException('Transient failure'));
        $this->app->instance(CrmRegistrationSync::class, new CrmRegistrationSync($failing));
        $url = URL::temporarySignedRoute('verification.verify', now()->addHour(), ['id' => $user->id, 'hash' => sha1($user->email)]);
        $this->get($url)->assertRedirect('/dashboard?verified=1');
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $this->assertNull($prospect->fresh()->linked_user_id);
        $this->app->forgetInstance(CrmRegistrationSync::class);
        $this->get($url)->assertRedirect('/dashboard?verified=1');
        $this->assertSame($user->id, $prospect->fresh()->linked_user_id);
        $this->assertSame(1, $prospect->statusHistories()->count());
        $this->assertSame(1, $prospect->activities()->count());
    }

    public function test_existing_authenticated_verification_action_still_works(): void
    {
        $prospect = $this->prospect();
        $user = $this->account();
        $this->actingAs($user);
        $request = \Illuminate\Foundation\Auth\EmailVerificationRequest::create('/verify-email');
        $request->setUserResolver(fn () => $user);
        $response = app(\App\Http\Controllers\Auth\VerifyEmailController::class)($request);
        $this->assertSame(302, $response->getStatusCode());
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $this->assertSame($user->id, $prospect->fresh()->linked_user_id);
        app(\App\Http\Controllers\Auth\VerifyEmailController::class)($request);
        $this->assertSame(1, $prospect->statusHistories()->count());
    }

    public function test_crm_transaction_uses_utc_without_changing_the_session_timezone(): void
    {
        $previousTimezone = DB::selectOne('SHOW TIME ZONE')->TimeZone;
        try {
            DB::statement("SET TIME ZONE 'Europe/Brussels'");
            $prospect = $this->prospect();
            $user = $this->account();
            $start = $user->entreprise->created_at->toISOString();
            $this->travel(3)->days();
            $this->verify($user);
            $this->assertSame($start, $prospect->fresh()->trial_started_at->toISOString());
            $this->assertSame($user->email_verified_at->toISOString(), $prospect->fresh()->linked_at->toISOString());
            $this->assertSame('Europe/Brussels', DB::selectOne('SHOW TIME ZONE')->TimeZone);
        } finally {
            DB::select("SELECT set_config('TimeZone', ?, false)", [$previousTimezone]);
        }
    }

    public function test_a_partial_link_is_a_conflict_even_when_the_user_matches(): void
    {
        $user = $this->account();
        $prospect = $this->prospect(['linked_user_id' => $user->id]);
        $this->verify($user);
        $this->assertSame($user->id, $prospect->fresh()->linked_user_id);
        $this->assertNull($prospect->fresh()->linked_entreprise_id);
        $this->assertSame(ProspectStatus::Nouveau, $prospect->fresh()->status);
        $this->assertSame('prospect_already_linked', $this->receipt($user)->payload['reason']);
    }

    public function test_linked_and_unlinked_matches_are_ambiguous_and_analytics_is_not_called(): void
    {
        $this->mock(AnalyticsBusinessEvents::class)->shouldNotReceive('registration', 'record', 'rememberContext');
        $other = $this->account('other@example.test', false);
        $this->prospect(['linked_user_id' => $other->id, 'linked_entreprise_id' => $other->entreprise_id]);
        $unlinked = $this->prospect();
        $user = $this->account();
        $this->verify($user);
        $this->assertNull($unlinked->fresh()->linked_user_id);
        $this->assertSame('duplicate_prospect_email', $this->receipt($user)->payload['reason']);
        Http::assertNothingSent();
    }

    public function test_invalid_registration_never_prepares_a_crm_event(): void
    {
        $this->postJson('/register', ['full_name' => 'Invalid', 'email' => 'invalid', 'password' => 'short'])->assertUnprocessable();
        $this->assertSame(0, CrmLifecycleEvent::count());
        $this->assertSame(0, User::count());
        $this->assertSame(0, Entreprise::count());
    }

    public function test_referral_is_captured_at_register_but_only_verified_email_selects_the_account_link(): void
    {
        $this->mock(AnalyticsBusinessEvents::class)->shouldReceive('registration')->once();
        $referral = $this->prospect(['email' => 'original-recipient@example.test']);
        $emailMatch = $this->prospect(['email' => 'actual-registrant@example.test']);
        $this->withSession(['analytics_context' => ['granted' => false]])->get('/?utm_source=linkedin&utm_medium=message_prive&ref='.$referral->public_token)->assertRedirect();
        $this->get(route('register'))->assertOk()->assertSessionHas(\App\Services\ProspectReferralAttribution::SESSION_KEY.'.prospect_id', $referral->id);
        $this->post('/register', ['full_name' => 'Actual Registrant', 'email' => 'actual-registrant@example.test', 'password' => 'Password123!', 'password_confirmation' => 'Password123!', 'terms_accepted' => '1'])->assertRedirect(route('verification.notice'));
        $user = User::where('email', 'actual-registrant@example.test')->sole();
        $this->assertSame($referral->id, $this->receipt($user)->payload['referral']['prospect_id']);
        $this->assertSame($referral->public_token, $this->receipt($user)->payload['referral']['token']);
        $this->assertNull($referral->fresh()->linked_user_id);
        $this->assertNull($emailMatch->fresh()->linked_user_id);
        $this->assertFalse(session()->has(\App\Services\ProspectReferralAttribution::SESSION_KEY));
        $url = URL::temporarySignedRoute('verification.verify', now()->addHour(), ['id' => $user->id, 'hash' => sha1($user->email)]);
        $this->get($url)->assertRedirect('/dashboard?verified=1');
        $this->assertSame($user->id, $emailMatch->fresh()->linked_user_id);
        $this->assertSame(ProspectStatus::DemoEssai, $emailMatch->fresh()->status);
        $this->assertNull($referral->fresh()->linked_user_id);
        $this->assertNull($referral->fresh()->linked_entreprise_id);
        $this->assertSame(ProspectStatus::Nouveau, $referral->fresh()->status);
        Http::assertNothingSent();
    }

    public function test_ref_alone_never_links_a_prospect_when_the_verified_email_has_no_match(): void
    {
        $this->mock(AnalyticsBusinessEvents::class)->shouldReceive('registration')->once();
        $prospect = $this->prospect(['email' => 'recipient@example.test']);
        $this->get('/?ref='.$prospect->public_token)->assertRedirect();
        $this->post('/register', ['full_name' => 'Other', 'email' => 'unrelated@example.test', 'password' => 'Password123!', 'password_confirmation' => 'Password123!', 'terms_accepted' => '1'])->assertRedirect();
        $user = User::where('email', 'unrelated@example.test')->sole();
        $this->verify($user);
        $this->assertSame('no_match', $this->receipt($user)->payload['state']);
        $this->assertSame($prospect->id, $this->receipt($user)->payload['referral']['prospect_id']);
        $this->assertNull($prospect->fresh()->linked_user_id);
        $this->assertSame(1, Prospect::count());
        $this->assertSame(0, DB::table('prospect_status_history')->count());
    }

    public function test_ref_does_not_disambiguate_duplicate_prospects_with_the_same_email(): void
    {
        $first = $this->prospect();
        $this->prospect();
        $user = $this->account(prepare: false);
        app(CrmRegistrationSync::class)->registered($user, ['token' => $first->public_token, 'prospect_id' => $first->id, 'captured_at' => now()->timestamp]);
        $this->verify($user);
        $this->assertSame('duplicate_prospect_email', $this->receipt($user)->payload['reason']);
        $this->assertSame(0, Prospect::whereNotNull('linked_user_id')->count());
        $this->assertSame(0, DB::table('prospect_status_history')->count());
    }
}
