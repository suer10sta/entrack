<?php

namespace Tests\Feature;

use App\Enums\ProspectStatus;
use App\Mail\ProspectCampaignMail;
use App\Models\CrmCampaign;
use App\Models\CrmCampaignRecipient;
use App\Models\Manager;
use App\Models\Prospect;
use App\Models\User;
use App\Services\CrmCampaignSender;
use App\Services\CrmCampaignService;
use App\Services\ProspectStatusService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class CrmCampaignTest extends TestCase
{
    private ?string $schema = null;

    private Manager $manager;

    private array $addresses = [];

    protected function setUp(): void
    {
        parent::setUp();
        $database = config('database.connections.pgsql.database');
        if (! is_string($database) || ! preg_match('/(_test|_testing)$/', $database) || $database === $this->developmentDatabaseName()) {
            throw new RuntimeException('CRM UI tests require a dedicated PostgreSQL testing database.');
        }
        config(['database.default' => 'pgsql', 'cache.default' => 'array', 'session.driver' => 'array']);
        $name = 'crm_campaign_test_'.bin2hex(random_bytes(8));
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
            $table->string('type_compt')->nullable();
            $table->timestampTz('trial_ends_at')->nullable();
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
        (require database_path('migrations/2026_10_27_000100_create_crm_campaigns.php'))->up();
        (require database_path('migrations/2026_10_27_000102_add_campaign_composition_fields.php'))->up();
        (require database_path('migrations/2026_10_27_000103_add_campaign_media.php'))->up();
        (require database_path('migrations/2026_10_27_000104_add_campaign_tracking.php'))->up();
        (require database_path('migrations/2026_10_28_000000_add_campaign_scheduling.php'))->up();
        (require database_path('migrations/2026_10_28_000001_add_campaign_soft_deletes.php'))->up();
        (require database_path('migrations/2026_10_28_000002_add_campaign_user_audiences.php'))->up();
        (require database_path('migrations/2026_10_29_200000_add_crm_campaign_opt_out.php'))->up();
        (require database_path('migrations/2026_10_30_200000_add_crm_campaign_smtp_limits.php'))->up();
        Schema::create('subscriptions', function ($table) {
            $table->id();
            $table->foreignId('entreprise_id');
            $table->string('stripe_status');
            $table->string('type')->default('default');
            $table->timestampTz('ends_at')->nullable();
            $table->timestamps();
        });
        config(['mail.default' => 'smtp', 'crm.campaign_batch_size' => 10, 'crm.campaign_interval_ms' => 0]);
        $this->manager = Manager::create(['name' => 'CRM Manager', 'email' => 'manager@example.test']);
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

    private function campaign(): CrmCampaign
    {
        return CrmCampaign::create(['name' => 'Présentation', 'subject' => 'Découvrez DalyTrac', 'content' => "Bonjour,\nVoici notre présentation.", 'manager_id' => $this->manager->id]);
    }

    private function prospect(string $email = 'contact@example.test', ProspectStatus $status = ProspectStatus::Nouveau, string $source = 'LinkedIn'): Prospect
    {
        $prospect = Prospect::create(['name' => 'Contact', 'email' => $email, 'source' => $source]);
        if ($status === ProspectStatus::Perdu) {
            app(ProspectStatusService::class)->transition($prospect, $status, manager: $this->manager);
        } else {
            app(ProspectStatusService::class)->transition($prospect, $status, 'registration');
        }

        return $prospect->fresh();
    }

    private function selectAll(CrmCampaign $campaign, array $filters = []): void
    {
        app(CrmCampaignService::class)->select($campaign, ['mode' => 'all'] + $filters);
    }

    public function test_audience_presets_count_select_and_deduplicate_real_accounts(): void
    {
        $campaign = $this->campaign();
        $paid = DB::table('entreprises')->insertGetId(['name' => 'Paid', 'type_compt' => 'Solo']);
        $trial = DB::table('entreprises')->insertGetId(['name' => 'Trial', 'trial_ends_at' => now('UTC')->addDays(5)]);
        $expired = DB::table('entreprises')->insertGetId(['name' => 'Expired', 'trial_ends_at' => now('UTC')->subDay()]);
        $cancelled = DB::table('entreprises')->insertGetId(['name' => 'Cancelled', 'type_compt' => 'Pro']);
        DB::table('subscriptions')->insert([
            ['entreprise_id' => $paid, 'stripe_status' => 'active'],
            ['entreprise_id' => $cancelled, 'stripe_status' => 'canceled'],
        ]);
        foreach ([['Paid', ' SHARED@example.test ', $paid], ['Duplicate', 'shared@example.test', $paid],
            ['Trial', 'trial@example.test', $trial], ['Expired', 'expired@example.test', $expired],
            ['Cancelled', 'cancelled@example.test', $cancelled], ['Invalid', 'bad-email', $paid]] as [$name, $email, $company]) {
            User::create(['name' => $name, 'email' => $email, 'entreprise_id' => $company]);
        }
        $this->prospect('shared@example.test');
        $this->prospect('other@example.test');
        $this->actingAs($this->manager, 'manager');
        foreach (['prospects' => 2, 'subscribers' => 1, 'trials' => 1] as $audience => $count) {
            $this->getJson(route('manager.campaigns.audience-count', ['campaign' => $campaign, 'audience' => $audience]))->assertOk()->assertJsonPath('count', $count);
            $this->assertSame(0, $campaign->recipients()->count());
        }
        $this->post(route('manager.campaigns.recipients', $campaign), ['mode' => 'page', 'audience' => 'subscribers', '_wizard' => 1, 'wizard_next' => 3])->assertRedirect();
        $recipient = $campaign->recipients()->sole();
        $this->assertNull($recipient->prospect_id);
        $this->assertNotNull($recipient->user_id);
        $this->assertSame('shared@example.test', $recipient->email_normalized);
        $this->assertSame('subscribers', $campaign->fresh()->audience);
        // Custom selections can combine groups; the same normalized email is still sent once.
        $this->selectAll($campaign);
        $this->assertSame(2, $campaign->recipients()->count());
        $this->assertSame(1, $campaign->recipients()->where('email_normalized', 'shared@example.test')->count());
        $this->assertSame(2, Prospect::count()); // No artificial prospects created for User recipients.
        $this->post(route('manager.campaigns.recipients', $campaign), ['mode' => 'page', 'audience' => 'trials'])->assertRedirect();
        $this->assertSame('trial@example.test', $campaign->recipients()->sole()->email_normalized);
        $this->get(route('manager.campaigns.show', ['campaign' => $campaign, 'step' => 2]))->assertOk()->assertSee('Tous les abonnés actifs')->assertSee('Sélection personnalisée');
        $this->getJson(route('manager.campaigns.audience-count', ['campaign' => $campaign, 'audience' => 'invalid']))->assertUnprocessable();
    }

    public function test_user_audience_uses_existing_smtp_and_tracking_without_creating_crm_records(): void
    {
        $campaign = $this->campaign();
        $company = DB::table('entreprises')->insertGetId(['name' => 'Trial', 'trial_ends_at' => now('UTC')->addDay()]);
        User::create(['name' => 'Trial user', 'email' => 'trial@example.test', 'entreprise_id' => $company]);
        app(CrmCampaignService::class)->select($campaign, ['mode' => 'page', 'audience' => 'trials']);
        app(CrmCampaignService::class)->enqueue($campaign);
        $this->mailer();
        $this->assertSame(1, app(CrmCampaignSender::class)->batch());
        $recipient = $campaign->recipients()->sole();
        $this->assertSame('sent', $recipient->status);
        $this->assertSame(['trial@example.test'], $this->addresses);
        $mail = new ProspectCampaignMail('Test', 'Message', ctaText: 'Découvrir', ctaUrl: 'https://dalytrac.com', trackingRecipient: $recipient);
        $mail->build();
        $recipient->refresh();
        $this->get(route('crm.tracking.open', $recipient->tracking_token))->assertOk();
        $this->get(route('crm.tracking.click', $recipient->links()->sole()->token))->assertRedirect('https://dalytrac.com');
        $this->assertSame(1, $recipient->fresh()->open_count);
        $this->assertSame(1, $recipient->fresh()->click_count);

        $this->assertSame(0, Prospect::count());
        $this->assertSame(0, DB::table('prospect_status_history')->count());
        $this->assertSame(0, app(CrmCampaignSender::class)->batch());
    }

    public function test_audience_limit_and_non_draft_checks_preserve_existing_selection(): void
    {
        $campaign = $this->campaign();
        $this->prospect('one@example.test');
        $this->selectAll($campaign);
        $this->prospect('two@example.test');
        config(['crm.campaign_max_recipients' => 1]);
        $this->actingAs($this->manager, 'manager')->post(route('manager.campaigns.recipients', $campaign), ['mode' => 'page', 'audience' => 'prospects'])->assertSessionHasErrors('audience');
        $this->assertSame(1, $campaign->recipients()->count());
        $this->getJson(route('manager.campaigns.audience-count', ['campaign' => $campaign, 'audience' => 'prospects']))->assertUnprocessable();
        $campaign->update(['status' => 'queued']);
        $this->getJson(route('manager.campaigns.audience-count', ['campaign' => $campaign, 'audience' => 'subscribers']))->assertStatus(409);
        $this->post(route('manager.campaigns.recipients', $campaign), ['mode' => 'page', 'audience' => 'subscribers'])->assertStatus(409);
    }

    private function mailer(?string $failure = null, bool $cancel = false): void
    {
        Mail::shouldReceive('to')->andReturnUsing(function ($address) use ($failure, $cancel) {
            $this->addresses[] = $address;
            $pending = Mockery::mock();
            $expectation = $pending->shouldReceive('send')->once()->with(Mockery::type(ProspectCampaignMail::class));
            if ($address === $failure) {
                $expectation->andThrow(new RuntimeException('SMTP refused'));
            } else {
                $expectation->andReturn($cancel ? null : (object) ['message' => 'accepted']);
            }

            return $pending;
        });
    }

    private function queue(CrmCampaign $campaign): void
    {
        app(CrmCampaignService::class)->enqueue($campaign);
    }

    public function test_all_campaign_routes_require_manager(): void
    {
        $campaign = $this->campaign();
        foreach (['guest', 'web'] as $identity) {
            if ($identity === 'web') {
                $this->actingAs(User::create(['name' => 'User', 'email' => 'user@example.test']), 'web');
            }
            $this->getJson(route('manager.campaigns.audience-count', ['campaign' => $campaign, 'audience' => 'subscribers']))->assertUnauthorized();
            $this->getJson(route('manager.campaigns.index'))->assertUnauthorized();
            $this->getJson(route('manager.campaigns.create'))->assertUnauthorized();
            $this->getJson(route('manager.campaigns.preview', $campaign))->assertUnauthorized();
            $this->getJson(route('manager.campaigns.sent-email', $campaign))->assertUnauthorized();
            $this->getJson(route('manager.campaigns.sent-email.content', $campaign))->assertUnauthorized();
            $this->postJson(route('manager.campaigns.setup', $campaign), [])->assertUnauthorized();
            $this->getJson(route('manager.campaigns.show', $campaign))->assertUnauthorized();
            $this->postJson(route('manager.campaigns.store'), [])->assertUnauthorized();
            $this->deleteJson(route('manager.campaigns.destroy', $campaign))->assertUnauthorized();
            $this->patchJson(route('manager.campaigns.update', $campaign), [])->assertUnauthorized();
            $this->postJson(route('manager.campaigns.recipients', $campaign), ['mode' => 'clear'])->assertUnauthorized();
            $this->postJson(route('manager.campaigns.send', $campaign), ['confirm' => 1, 'revision' => $campaign->fresh()->revision])->assertUnauthorized();
        }
    }

    public function test_create_validate_and_render_campaign_history(): void
    {
        $this->actingAs($this->manager, 'manager')->postJson(route('manager.campaigns.store'), ['name' => '', 'subject' => "Injected\r\nBcc: bad@example.test", 'content' => ''])->assertUnprocessable();
        $this->post(route('manager.campaigns.store'), ['name' => 'Lancement', 'subject' => 'Bienvenue', 'content' => 'Bonjour'])->assertRedirect();
        $campaign = CrmCampaign::sole();
        $this->assertSame('draft', $campaign->status);
        $this->get(route('manager.campaigns.index'))->assertOk()->assertSee('Campagnes')->assertSee('Lancement')->assertSee('Brouillon');
        $this->get(route('manager.campaigns.show', $campaign))->assertOk()->assertSee('Choisir les destinataires')->assertSee('Nombre de destinataires');
    }

    public function test_filtered_selection_normalizes_and_deduplicates_without_business_side_effects(): void
    {
        $campaign = $this->campaign();
        $one = $this->prospect(' Person@EXAMPLE.test ');
        $this->prospect('person@example.test');
        $this->prospect('invalid');
        $this->prospect('other@example.test', ProspectStatus::Engage);
        $this->prospect('source@example.test', ProspectStatus::Nouveau, 'Google');
        $this->actingAs($this->manager, 'manager')->post(route('manager.campaigns.recipients', $campaign), ['mode' => 'all', 'filter_status' => 'nouveau', 'source' => 'LinkedIn'])
            ->assertRedirect(route('manager.campaigns.show', ['campaign' => $campaign, 'filter_status' => 'nouveau', 'source' => 'LinkedIn']));
        $recipient = $campaign->recipients()->sole();
        $this->assertSame('person@example.test', $recipient->email);
        $this->assertSame($one->id, $recipient->prospect_id);
        $this->assertSame(ProspectStatus::Nouveau, $one->fresh()->status);
        $this->assertSame(0, User::count());
        $this->assertSame(0, DB::table('entreprises')->count());
        $this->get(route('manager.campaigns.show', ['campaign' => $campaign, 'filter_status' => 'engage', 'source' => 'LinkedIn']))->assertOk()
            ->assertViewHas('prospects', fn ($rows) => $rows->total() === 1)->assertViewHas('selected', fn ($rows) => $rows->count() === 1);
    }

    public function test_selection_survives_pages_and_can_be_cleared(): void
    {
        $campaign = $this->campaign();
        $one = $this->prospect('one@example.test');
        $two = $this->prospect('two@example.test');
        $service = app(CrmCampaignService::class);
        $service->select($campaign, ['mode' => 'page', 'visible_ids' => [$one->id], 'selected_ids' => [$one->id]]);
        $service->select($campaign, ['mode' => 'page', 'visible_ids' => [$two->id], 'selected_ids' => [$two->id]]);
        $this->assertSame(2, $campaign->recipients()->count());
        $service->select($campaign, ['mode' => 'page', 'visible_ids' => [$one->id], 'selected_ids' => []]);
        $this->assertSame($two->id, $campaign->recipients()->sole()->prospect_id);
        $service->select($campaign, ['mode' => 'clear']);
        $this->assertSame(0, $campaign->recipients()->count());
    }

    public function test_send_requires_confirmation_and_recipients_and_only_enqueues(): void
    {
        Mail::fake();
        $campaign = $this->campaign();
        $this->actingAs($this->manager, 'manager')->postJson(route('manager.campaigns.send', $campaign), [])->assertUnprocessable();
        $this->postJson(route('manager.campaigns.send', $campaign), ['confirm' => 1, 'revision' => $campaign->fresh()->revision])->assertUnprocessable();
        $this->prospect();
        $this->selectAll($campaign);
        $this->post(route('manager.campaigns.send', $campaign), ['confirm' => 1, 'revision' => $campaign->fresh()->revision])->assertRedirect();
        $this->post(route('manager.campaigns.send', $campaign), ['confirm' => 1, 'revision' => $campaign->fresh()->revision])->assertRedirect();
        $this->assertSame('queued', $campaign->fresh()->status);
        $this->assertSame('pending', $campaign->recipients()->sole()->status);
        Mail::assertNothingSent();
        $this->patchJson(route('manager.campaigns.update', $campaign), ['name' => 'Changed', 'subject' => 'Changed', 'content' => 'Changed'])->assertStatus(409);
        $this->postJson(route('manager.campaigns.recipients', $campaign), ['mode' => 'clear'])->assertStatus(409);
    }

    public function test_controlled_batches_and_replays_send_each_prospect_once_and_contact_only_after_success(): void
    {
        config(['crm.campaign_batch_size' => 2]);
        $campaign = $this->campaign();
        for ($i = 0; $i < 3; $i++) {
            $this->prospect('person'.$i.'@example.test');
        }
        $this->selectAll($campaign);
        $this->queue($campaign);
        $this->mailer();
        $sender = app(CrmCampaignSender::class);
        $this->assertSame(2, $sender->batch());
        $this->assertSame(2, Prospect::where('status', 'contacte')->count());
        $this->assertSame(0, $sender->batch());
        $this->travel(61)->seconds();
        $this->assertSame(1, $sender->batch());
        $this->assertSame(0, $sender->batch());
        $this->assertCount(3, array_unique($this->addresses));
        $this->assertSame('completed', $campaign->fresh()->status);
        $this->assertSame(3, DB::table('prospect_status_history')->where('source', 'campaign')->count());
        $this->assertSame(3, DB::table('prospect_activities')->where('type', 'campaign')->count());
    }

    public function test_failure_is_terminal_and_does_not_contact_prospect(): void
    {
        $campaign = $this->campaign();
        $prospect = $this->prospect();
        $this->selectAll($campaign);
        $this->queue($campaign);
        $this->mailer('contact@example.test');
        $sender = app(CrmCampaignSender::class);
        $this->assertSame(0, $sender->batch());
        $sender->batch();
        $this->assertCount(1, $this->addresses);
        $this->assertSame(ProspectStatus::Nouveau, $prospect->fresh()->status);
        $this->assertSame('failed', $campaign->recipients()->sole()->status);
        $this->assertSame('failed', $campaign->fresh()->status);
        $this->assertSame(0, $prospect->activities()->count());
    }

    public function test_lost_and_other_existing_statuses_are_not_changed(): void
    {
        $campaign = $this->campaign();
        foreach ([ProspectStatus::Perdu, ProspectStatus::Engage, ProspectStatus::DemoEssai, ProspectStatus::Client, ProspectStatus::Contacte] as $i => $status) {
            $this->prospect('status'.$i.'@example.test', $status);
        }
        $before = Prospect::orderBy('id')->pluck('status')->all();
        $this->selectAll($campaign);
        $this->queue($campaign);
        $this->mailer();
        $this->assertSame(5, app(CrmCampaignSender::class)->batch());
        $this->assertSame($before, Prospect::orderBy('id')->pluck('status')->all());
        $this->assertSame(0, DB::table('prospect_status_history')->where('source', 'campaign')->count());
    }

    public function test_archived_and_changed_addresses_are_skipped(): void
    {
        Mail::shouldReceive('to')->never();
        $campaign = $this->campaign();
        $one = $this->prospect('one@example.test');
        $two = $this->prospect('two@example.test');
        $this->selectAll($campaign);
        $this->queue($campaign);
        $one->delete();
        $two->update(['email' => 'changed@example.test']);
        $this->assertSame(0, app(CrmCampaignSender::class)->batch());
        $this->assertSame(2, $campaign->recipients()->where('status', 'skipped')->count());
        $this->assertSame(ProspectStatus::Nouveau, $two->fresh()->status);
    }

    public function test_interrupted_send_is_uncertain_and_never_retried(): void
    {
        Mail::shouldReceive('to')->never();
        $campaign = $this->campaign();
        $prospect = $this->prospect();
        $this->selectAll($campaign);
        $this->queue($campaign);
        $campaign->recipients()->sole()->update(['status' => 'sending', 'attempted_at' => now()->subHour()]);
        app(CrmCampaignSender::class)->batch();
        app(CrmCampaignSender::class)->batch();
        $this->assertSame('uncertain', $campaign->recipients()->sole()->status);
        $this->assertSame(ProspectStatus::Nouveau, $prospect->fresh()->status);
    }

    public function test_crm_sync_can_retry_without_resending_email(): void
    {
        $campaign = $this->campaign();
        $prospect = $this->prospect();
        $this->selectAll($campaign);
        $this->queue($campaign);
        $this->mailer();
        $real = app(ProspectStatusService::class);
        $this->mock(ProspectStatusService::class)->shouldReceive('transition')->once()->andThrow(new RuntimeException('CRM unavailable'));
        app(CrmCampaignSender::class)->batch();
        $this->assertSame('sent', $campaign->recipients()->sole()->status);
        $this->assertNotNull($campaign->recipients()->sole()->crm_error);
        $this->assertSame(ProspectStatus::Nouveau, $prospect->fresh()->status);
        $this->app->instance(ProspectStatusService::class, $real);
        (new CrmCampaignSender($real))->batch();
        $this->assertCount(1, $this->addresses);
        $this->assertSame(ProspectStatus::Contacte, $prospect->fresh()->status);
        $this->assertSame(1, $prospect->activities()->count());
    }

    public function test_two_campaigns_share_the_global_smtp_budget(): void
    {
        config(['crm.campaign_batch_size' => 1]);
        $this->prospect();
        $one = $this->campaign();
        $two = $this->campaign();
        $this->selectAll($one);
        $this->selectAll($two);
        $this->queue($one);
        $two->update(['manager_id' => Manager::create(['name' => 'Second manager', 'email' => 'second@example.test'])->id]);
        $this->queue($two);
        $this->mailer();
        $sender = app(CrmCampaignSender::class);
        $this->assertSame(1, $sender->batch());
        $this->assertSame(0, $sender->batch());
        $this->assertSame('queued', $two->fresh()->status);
        $this->travel(61)->seconds();
        $this->assertSame(1, $sender->batch());
        $this->assertCount(2, $this->addresses);
    }

    public function test_cancelled_mail_and_log_mailer_do_not_count_as_contacted(): void
    {
        $campaign = $this->campaign();
        $prospect = $this->prospect();
        $this->selectAll($campaign);
        $this->queue($campaign);
        config(['mail.default' => 'log']);
        $this->assertSame(0, app(CrmCampaignSender::class)->batch());
        $this->assertSame('pending', $campaign->recipients()->sole()->status);
        config(['mail.default' => 'smtp']);
        $this->mailer(cancel: true);
        $this->assertSame(0, app(CrmCampaignSender::class)->batch());
        $this->assertSame(ProspectStatus::Nouveau, $prospect->fresh()->status);
    }

    public function test_mail_body_is_escaped_and_contains_no_tracking(): void
    {
        $html = (new ProspectCampaignMail('Sujet', '<script>alert(1)</script>'))->render();
        $this->assertStringContainsString('&lt;script&gt;', $html);
        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringNotContainsString('<img', $html);
        $this->assertStringNotContainsString('tracking', $html);
    }

    public function test_second_sender_cannot_work_while_another_process_holds_the_lock(): void
    {
        $campaign = $this->campaign();
        $this->prospect();
        $this->selectAll($campaign);
        $this->queue($campaign);
        Mail::shouldReceive('to')->never();
        config(['database.connections.crm_lock_test' => config('database.connections.pgsql')]);
        $other = DB::connection('crm_lock_test');
        $key = 'crm.campaign.sender:'.DB::connection()->getDatabaseName();
        $other->select('SELECT pg_advisory_lock(hashtextextended(?, 0))', [$key]);
        try {
            $this->assertSame(0, app(CrmCampaignSender::class)->batch());
            $this->assertSame('pending', $campaign->recipients()->sole()->status);
        } finally {
            $other->select('SELECT pg_advisory_unlock(hashtextextended(?, 0))', [$key]);
            DB::purge('crm_lock_test');
        }
    }

    public function test_selection_limit_rolls_back_and_database_uniqueness_prevents_duplicate_recipients(): void
    {
        config(['crm.campaign_max_recipients' => 2]);
        $campaign = $this->campaign();
        for ($i = 0; $i < 3; $i++) {
            $this->prospect('limit'.$i.'@example.test');
        }
        $this->actingAs($this->manager, 'manager')->postJson(route('manager.campaigns.recipients', $campaign), ['mode' => 'all'])->assertUnprocessable();
        $this->assertSame(0, $campaign->recipients()->count());
        config(['crm.campaign_max_recipients' => 5000]);
        $this->selectAll($campaign);
        $recipient = $campaign->recipients()->first();
        $this->expectException(\Illuminate\Database\QueryException::class);
        $campaign->recipients()->create($recipient->only(['prospect_id', 'email', 'email_normalized', 'name']));
    }

    public function test_artisan_command_uses_existing_mailer_and_records_the_results(): void
    {
        $campaign = $this->campaign();
        $this->prospect();
        $this->selectAll($campaign);
        $this->queue($campaign);
        $this->mailer();
        $this->artisan('crm:send-campaigns')->expectsOutput('1 email(s) accepted by the mailer.')->assertSuccessful();
        $this->assertSame('completed', $campaign->fresh()->status);
        $this->assertNotNull($campaign->fresh()->started_at);
        $this->assertNotNull($campaign->fresh()->completed_at);
        $this->actingAs($this->manager, 'manager')->get(route('manager.campaigns.show', $campaign))->assertOk()->assertSee('Résultats par destinataire')->assertSee('Envoyé');
    }

    public function test_non_delivery_failover_cannot_mark_emails_as_sent(): void
    {
        config(['mail.default' => 'failover']);
        Mail::shouldReceive('to')->never();
        $campaign = $this->campaign();
        $prospect = $this->prospect();
        $this->selectAll($campaign);
        $this->queue($campaign);
        $this->assertSame(0, app(CrmCampaignSender::class)->batch());
        $this->assertSame('pending', $campaign->recipients()->sole()->status);
        $this->assertSame(ProspectStatus::Nouveau, $prospect->fresh()->status);
    }

    public function test_smtp_acceptance_followed_by_database_failure_is_never_resent(): void
    {
        $campaign = $this->campaign();
        $prospect = $this->prospect();
        $this->selectAll($campaign);
        $this->queue($campaign);
        $this->mailer();
        $failOnce = true;
        CrmCampaignRecipient::updating(function ($recipient) use (&$failOnce) {
            if ($failOnce && $recipient->status === 'sent') {
                $failOnce = false;
                throw new RuntimeException('Database unavailable after SMTP accepted');
            }
        });
        try {
            try {
                app(CrmCampaignSender::class)->batch();
                $this->fail('Expected the post-SMTP write to fail.');
            } catch (RuntimeException $error) {
                $this->assertSame('Database unavailable after SMTP accepted', $error->getMessage());
            }
            $this->assertSame('sending', $campaign->recipients()->sole()->status);
            $this->travel(16)->minutes();
            app(CrmCampaignSender::class)->batch();
            $this->assertSame('uncertain', $campaign->recipients()->sole()->status);
            $this->assertCount(1, $this->addresses);
            $this->assertSame(ProspectStatus::Nouveau, $prospect->fresh()->status);
        } finally {
            CrmCampaignRecipient::flushEventListeners();
        }
    }

    public function test_stale_confirmation_cannot_send_a_group_changed_by_another_manager(): void
    {
        $campaign = $this->campaign();
        $this->prospect();
        $revision = $campaign->fresh()->revision;
        $this->selectAll($campaign);
        $this->actingAs($this->manager, 'manager')->postJson(route('manager.campaigns.send', $campaign), ['confirm' => 1, 'revision' => $revision])
            ->assertUnprocessable()->assertJsonValidationErrors('campaign');
        $this->assertSame('draft', $campaign->fresh()->status);
        $this->assertSame('selected', $campaign->recipients()->sole()->status);
        $this->post(route('manager.campaigns.send', $campaign), ['confirm' => 1, 'revision' => $campaign->fresh()->revision])->assertRedirect();
        $this->assertSame('queued', $campaign->fresh()->status);
    }

    public function test_send_dates_and_rate_limit_are_consistent_in_a_non_utc_database_session(): void
    {
        $campaign = $this->campaign();
        $prospect = $this->prospect();
        $this->selectAll($campaign);
        $this->mailer();
        $zone = DB::selectOne("SELECT current_setting('TimeZone') AS zone")->zone;
        try {
            DB::select("SELECT set_config('TimeZone', ?, false)", ['Europe/Paris']);
            $this->travelTo(\Carbon\CarbonImmutable::parse('2026-10-27T12:00:00Z'));
            $this->queue($campaign);
            $this->assertSame(1, app(CrmCampaignSender::class)->batch());
            $this->assertSame('2026-10-27T12:00:00.000000Z', $campaign->recipients()->sole()->sent_at->toISOString());
            $this->assertSame('2026-10-27T12:00:00.000000Z', $campaign->fresh()->started_at->toISOString());
            $this->assertSame('2026-10-27T12:00:00.000000Z', $prospect->statusHistories()->where('source', 'campaign')->sole()->occurred_at->toISOString());
        } finally {
            DB::select("SELECT set_config('TimeZone', ?, false)", [$zone]);
        }
    }

    public function test_revision_upgrade_preserves_existing_campaigns_and_is_safe_on_fresh_installs(): void
    {
        $campaign = $this->campaign();
        $migration = require database_path('migrations/2026_10_27_000101_add_revision_to_crm_campaigns.php');
        $migration->up();
        $this->assertSame(0, (int) $campaign->fresh()->revision);
        Schema::table('crm_campaigns', fn ($table) => $table->dropColumn('revision'));
        $migration->up();
        $this->assertSame('Présentation', $campaign->fresh()->name);
        $this->assertSame(0, (int) $campaign->fresh()->revision);
        $this->prospect();
        $this->selectAll($campaign);
        $this->assertSame(1, (int) $campaign->fresh()->revision);
        $this->assertSame(1, $campaign->recipients()->count());
        $migration->up();
        $this->assertSame(1, (int) $campaign->fresh()->revision);
    }

    public function test_results_use_actual_smtp_outcomes_and_completion_banner_appears_only_after_processing(): void
    {
        config(['crm.campaign_batch_size' => 2]);
        $campaign = $this->campaign();
        $success = $this->prospect('success@example.test');
        $failure = $this->prospect('failure@example.test');
        $pending = $this->prospect('pending@example.test');
        $this->selectAll($campaign);
        $this->queue($campaign);
        $this->actingAs($this->manager, 'manager')->get(route('manager.campaigns.show', $campaign))->assertOk()
            ->assertSee('Campagne en attente d’envoi.')->assertDontSee('Campagne terminée :')->assertSee('data-campaign-refresh', false);
        $this->mailer('failure@example.test');
        $this->assertSame(1, app(CrmCampaignSender::class)->batch());
        $this->get(route('manager.campaigns.index'))->assertOk()->assertSee('Voir les détails')->assertSee('Total destinataires')->assertSee('Envoyés avec succès')
            ->assertViewHas('campaigns', fn ($rows) => $rows->first()->sent_count === 1 && $rows->first()->failed_count === 1 && $rows->first()->pending_count === 1);
        $this->get(route('manager.campaigns.show', $campaign))->assertOk()->assertSee('Envoi en cours : 1 email(s) envoyé(s), 1 échec(s), 1 en attente.')
            ->assertSee('success@example.test')->assertSee('failure@example.test')->assertSee('Envoi refusé ou non confirmé.')
            ->assertSee('Tentative d’envoi')->assertDontSee('Campagne terminée :');
        $this->assertSame(ProspectStatus::Contacte, $success->fresh()->status);
        $this->assertSame(ProspectStatus::Nouveau, $failure->fresh()->status);
        $this->assertSame(ProspectStatus::Nouveau, $pending->fresh()->status);
        $this->travel(61)->seconds();
        app(CrmCampaignSender::class)->batch();
        $this->get(route('manager.campaigns.show', $campaign))->assertOk()->assertSee('Campagne terminée : 2 emails envoyés, 1 échec.')
            ->assertSee('Terminée avec erreurs')->assertDontSee('data-campaign-refresh', false)->assertSee('campaign-results');
        $this->assertSame(ProspectStatus::Nouveau, $failure->fresh()->status);
    }

    public function test_uncertain_and_skipped_are_not_reported_as_smtp_failures_or_successes(): void
    {
        $campaign = $this->campaign();
        $one = $this->prospect('uncertain@example.test');
        $two = $this->prospect('skipped@example.test');
        $this->selectAll($campaign);
        $this->queue($campaign);
        $campaign->recipients()->where('prospect_id', $one->id)->update(['status' => 'sending', 'attempted_at' => now()->subHour()]);
        $two->delete();
        Mail::shouldReceive('to')->never();
        app(CrmCampaignSender::class)->batch();
        $this->actingAs($this->manager, 'manager')->get(route('manager.campaigns.index'))->assertOk()
            ->assertViewHas('campaigns', fn ($rows) => $rows->first()->sent_count === 0 && $rows->first()->failed_count === 0 && $rows->first()->pending_count === 0 && $rows->first()->uncertain_count === 1 && $rows->first()->skipped_count === 1);
        $this->get(route('manager.campaigns.show', $campaign))->assertOk()->assertSee('Campagne terminée : 0 emails envoyés, 0 échecs.')
            ->assertSee('1 envoi(s) à vérifier')->assertSee('1 destinataire(s) ignoré(s).')->assertSee('Terminée avec erreurs');
        $this->assertSame(ProspectStatus::Nouveau, $one->fresh()->status);
    }

    public function test_wizard_saves_previews_and_queues_once_without_sending(): void
    {
        Mail::fake();
        $this->actingAs($this->manager, 'manager');
        $this->get(route('manager.campaigns.create'))->assertOk()->assertSee('Étape 1 sur 3');
        $this->get(route('manager.campaigns.index'))->assertOk()->assertDontSee('campaign-information');
        $this->post(route('manager.campaigns.store'), ['_wizard' => 1, 'name' => 'Launch', 'subject' => 'Hello', 'preheader' => 'Preview', 'utm_campaign' => 'launch test'])->assertRedirect();
        $campaign = CrmCampaign::sole();
        $this->assertSame('', $campaign->content);
        $prospect = $this->prospect();
        $this->post(route('manager.campaigns.recipients', $campaign), ['_wizard' => 1, 'mode' => 'all', 'wizard_next' => 3])->assertRedirect(route('manager.campaigns.show', ['campaign' => $campaign, 'step' => 3]));
        $campaign->refresh();
        $this->get(route('manager.campaigns.show', ['campaign' => $campaign, 'step' => 3]))->assertOk()->assertSee('Étape 3 sur 3');
        $data = ['step' => 3, 'revision' => $campaign->revision, 'content' => 'Real message', 'cta_text' => 'Discover', 'cta_url' => 'https://dalytrac.com/?ref=abc&utm_source=old#details'];
        $this->post(route('manager.campaigns.setup', $campaign), $data)->assertRedirect();
        $campaign->refresh();
        $this->assertSame('https://dalytrac.com/?ref=abc&utm_source=dalytrac&utm_medium=email&utm_campaign=launch%20test#details', $campaign->ctaLink());
        $this->get(route('manager.campaigns.preview', $campaign))->assertOk()->assertSee('Real message')->assertSee('Preview')->assertSee('Discover')->assertHeader('Cache-Control', 'no-store, private');
        $data['revision'] = $campaign->revision;
        $data['wizard_final'] = 1;
        $data['confirm'] = 1;
        $this->post(route('manager.campaigns.send', $campaign), $data)->assertRedirect();
        $this->post(route('manager.campaigns.send', $campaign), $data)->assertRedirect();
        $this->assertSame('queued', $campaign->fresh()->status);
        $this->assertSame(1, $campaign->recipients()->count());
        $this->assertSame(ProspectStatus::Nouveau, $prospect->fresh()->status);
        Mail::assertNothingSent();
    }

    public function test_wizard_rejects_incomplete_unsafe_and_stale_content(): void
    {
        $campaign = $this->campaign();
        $this->actingAs($this->manager, 'manager');
        $campaign->refresh();
        $url = route('manager.campaigns.setup', $campaign);
        $data = ['step' => 3, 'revision' => $campaign->revision, 'content' => 'Message', 'cta_text' => 'CTA'];
        $this->post($url, $data)->assertSessionHasErrors('cta_url');
        $this->post($url, $data + ['cta_url' => 'javascript:alert(1)'])->assertSessionHasErrors('cta_url');
        $this->post($url, $data + ['cta_url' => 'https://user:pass@example.com'])->assertSessionHasErrors('cta_url');
        $this->post($url, ['step' => 3, 'revision' => 99, 'content' => 'Changed'])->assertSessionHasErrors('campaign');
        $this->post($url, ['step' => 3, 'revision' => $campaign->revision, 'content' => '', 'back_to_audience' => 1])->assertRedirect();
        $this->assertSame('', $campaign->fresh()->content);
        $this->post(route('manager.campaigns.send', $campaign), ['revision' => $campaign->fresh()->revision, 'confirm' => 1])->assertSessionHasErrors('content');
    }

    private function campaignImage(): \Illuminate\Http\UploadedFile
    {
        return \Illuminate\Http\UploadedFile::fake()->createWithContent('banner.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jRZkAAAAASUVORK5CYII='));
    }

    public function test_campaign_uploads_render_inline_image_and_real_mime_attachment(): void
    {
        \Illuminate\Support\Facades\Storage::fake('local');
        $campaign = $this->campaign()->fresh();
        $this->actingAs($this->manager, 'manager');
        $data = ['step' => 3, 'revision' => $campaign->revision, 'content' => 'Message with media', 'image_file' => $this->campaignImage(),
            'attachment_file' => \Illuminate\Http\UploadedFile::fake()->createWithContent('guide.pdf', "%PDF-1.4\n1 0 obj <<>> endobj\n%%EOF")];
        $this->post(route('manager.campaigns.setup', $campaign), $data)->assertSessionHasNoErrors()->assertRedirect();
        $campaign->refresh();
        $this->assertSame('image/png', $campaign->media['image']['mime']);
        $this->assertSame('guide.pdf', $campaign->media['attachment']['name']);
        $this->get(route('manager.campaigns.preview', $campaign))->assertOk()->assertSee('data:image/png;base64,', false)->assertSee('guide.pdf');
        $mail = new ProspectCampaignMail($campaign->subject, $campaign->content, null, 'Discover', 'https://dalytrac.com', $campaign->media['image'], $campaign->media['attachment']);
        $sent = Mail::mailer('array')->to('recipient@example.test')->send($mail);
        $message = $sent->getSymfonySentMessage()->getOriginalMessage();
        $this->assertCount(2, $message->getAttachments());
        $this->assertStringContainsString('cid:', $message->getHtmlBody());
        $this->assertStringContainsString('Discover', $message->getHtmlBody());
        $this->assertContains('guide.pdf', array_map(fn ($part) => $part->getFilename(), $message->getAttachments()));
        $this->assertSame('draft', $campaign->status);
    }

    public function test_media_urls_are_snapshotted_and_private_addresses_redirects_and_invalid_files_rejected(): void
    {
        \Illuminate\Support\Facades\Storage::fake('local');
        $campaign = $this->campaign()->fresh();
        $this->actingAs($this->manager, 'manager');
        $fixture = $this->campaignImage();
        $image = file_get_contents($fixture->getRealPath());
        Http::fake(['http://8.8.8.8/guide.pdf' => Http::response("%PDF-1.4\n1 0 obj <<>> endobj\n%%EOF", 200), 'http://8.8.8.8/banner.png' => Http::response($image, 200), 'http://8.8.8.8/redirect' => Http::response('', 302, ['Location' => 'http://127.0.0.1/private']), 'http://8.8.8.8/large' => Http::response('', 200, ['Content-Length' => 3000000])]);
        $url = route('manager.campaigns.setup', $campaign);
        $data = ['step' => 3, 'revision' => $campaign->revision, 'content' => 'Message'];
        foreach (['http://127.0.0.1/file', 'http://169.254.169.254/file', 'http://user:pass@example.com/file', 'http://8.8.8.8/redirect', 'http://8.8.8.8/large'] as $invalid) {
            $this->post($url, $data + ['image_url' => $invalid])->assertSessionHasErrors('image_url');
        }
        $this->post($url, $data + ['image_file' => $this->campaignImage(), 'image_url' => 'http://8.8.8.8/banner.png'])->assertSessionHasErrors('image_url');
        $this->post($url, $data + ['image_file' => \Illuminate\Http\UploadedFile::fake()->createWithContent('bad.svg', '<svg xmlns="http://www.w3.org/2000/svg"></svg>')])->assertSessionHasErrors('image_file');
        $this->post($url, $data + ['attachment_file' => \Illuminate\Http\UploadedFile::fake()->create('large.pdf', 10241, 'application/pdf')])->assertSessionHasErrors('attachment_file');
        $this->post($url, $data + ['image_url' => 'http://8.8.8.8/banner.png', 'attachment_url' => 'http://8.8.8.8/guide.pdf'])->assertSessionHasNoErrors();
        $campaign->refresh();
        $this->assertSame('http://8.8.8.8/banner.png', $campaign->media['image']['source_url']);
        $this->assertSame('application/pdf', $campaign->media['attachment']['mime']);
        \Illuminate\Support\Facades\Storage::disk('local')->assertExists($campaign->media['image']['path']);
        $this->get(route('manager.campaigns.preview', $campaign))->assertOk()->assertSee('data:image/png;base64,', false);
        Http::assertNotSent(fn ($request) => str_contains($request->url(), '127.0.0.1') || str_contains($request->url(), '169.254.169.254'));
    }

    public function test_failed_final_confirmation_cleans_new_files_and_preserves_saved_media(): void
    {
        \Illuminate\Support\Facades\Storage::fake('local');
        $campaign = $this->campaign()->fresh();
        $this->actingAs($this->manager, 'manager');
        $url = route('manager.campaigns.setup', $campaign);
        $this->post($url, ['step' => 3, 'revision' => $campaign->revision, 'content' => 'Saved', 'image_file' => $this->campaignImage()])->assertSessionHasNoErrors();
        $campaign->refresh();
        $old = $campaign->media['image']['path'];
        $this->post(route('manager.campaigns.send', $campaign), ['wizard_final' => 1, 'confirm' => 1, 'revision' => $campaign->revision, 'content' => 'Changed', 'image_file' => $this->campaignImage()])->assertSessionHasErrors('recipients');
        $this->assertSame($old, $campaign->fresh()->media['image']['path']);
        $this->assertCount(1, \Illuminate\Support\Facades\Storage::disk('local')->allFiles('crm-campaigns'));
        $this->post($url, ['step' => 3, 'revision' => $campaign->revision, 'content' => 'Saved', 'remove_image' => 1])->assertSessionHasNoErrors();
        $this->assertNull($campaign->fresh()->media);
        \Illuminate\Support\Facades\Storage::disk('local')->assertMissing($old);
    }

    public function test_office_workbook_is_supported_and_missing_saved_media_fails_delivery(): void
    {
        \Illuminate\Support\Facades\Storage::fake('local');
        $book = new \PhpOffice\PhpSpreadsheet\Spreadsheet;
        $book->getActiveSheet()->setCellValue('A1', 'DalyTrac');
        $path = tempnam(sys_get_temp_dir(), 'crm-office-');
        try {
            (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($book))->save($path);
            $file = new \Illuminate\Http\UploadedFile($path, 'presentation.xlsx', null, null, true);
            $campaign = $this->campaign()->fresh();
            $this->actingAs($this->manager, 'manager')->post(route('manager.campaigns.setup', $campaign), ['step' => 3, 'revision' => $campaign->revision, 'content' => 'Workbook', 'attachment_file' => $file])->assertSessionHasNoErrors();
            $campaign->refresh();
            $this->assertSame('presentation.xlsx', $campaign->media['attachment']['name']);
            $prospect = $this->prospect();
            $this->selectAll($campaign);
            $this->queue($campaign);
            \Illuminate\Support\Facades\Storage::disk('local')->delete($campaign->media['attachment']['path']);
            $pending = Mockery::mock();
            $pending->shouldReceive('send')->once()->andReturnUsing(function (ProspectCampaignMail $mail) {
                $mail->render();

                return (object) ['accepted' => true];
            });
            Mail::shouldReceive('to')->once()->with($prospect->email)->andReturn($pending);
            // The real Mailable fails before transport; no prospect is marked contacted.
            app(CrmCampaignSender::class)->batch();
            $this->assertSame('failed', $campaign->recipients()->sole()->status);
            $this->assertSame(ProspectStatus::Nouveau, $prospect->fresh()->status);
        } finally {
            $book->disconnectWorksheets();
            if (is_file($path)) {
                unlink($path);
            }
        }
    }

    private function trackedRecipient(CrmCampaign $campaign, Prospect $prospect, string $status = 'sent'): CrmCampaignRecipient
    {
        $recipient = $campaign->recipients()->create(['prospect_id' => $prospect->id, 'name' => $prospect->name, 'email' => $prospect->email,
            'email_normalized' => $prospect->email, 'status' => $status, 'sent_at' => $status === 'sent' ? now('UTC') : null]);
        (new ProspectCampaignMail($campaign->subject, $campaign->content, null, $campaign->cta_text, $campaign->ctaLink(), null, null, $recipient))->render();

        return $recipient->fresh();
    }

    public function test_sender_adds_recipient_specific_tracking_but_preview_stays_untracked(): void
    {
        $campaign = $this->campaign();
        $campaign->update(['cta_text' => 'Discover', 'cta_url' => 'https://dalytrac.com/produit?ref=public#offer', 'utm_campaign' => 'launch test',
            'content' => 'Read https://dalytrac.com/tarifs?offer=1 then <script>alert(1)</script>']);
        $prospect = $this->prospect();
        $this->selectAll($campaign);
        $this->actingAs($this->manager, 'manager')->get(route('manager.campaigns.preview', $campaign))->assertOk()->assertDontSee('/crm/t/');
        $this->assertNull($campaign->recipients()->sole()->tracking_token);
        $this->assertSame(0, \App\Models\CrmCampaignLink::count());
        $renderMailer = Mail::mailer('array');
        Mail::shouldReceive('mailer')->andReturn($renderMailer);
        $html = '';
        $plain = '';
        $pending = Mockery::mock();
        $pending->shouldReceive('send')->once()->andReturnUsing(function (ProspectCampaignMail $mail) use (&$html, &$plain) {
            $this->assertNotNull($mail->trackingRecipient);
            $html = $mail->render();
            $plain = $mail->content.' '.$mail->ctaUrl;
            $first = \App\Models\CrmCampaignLink::count();
            $mail->render();
            $this->assertSame($first, \App\Models\CrmCampaignLink::count());

            return (object) ['accepted' => true];
        });
        Mail::shouldReceive('to')->once()->with($prospect->email)->andReturn($pending);
        $this->queue($campaign);
        $this->assertSame(1, app(CrmCampaignSender::class)->batch());
        $recipient = $campaign->recipients()->sole();
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $recipient->tracking_token);
        $this->assertStringContainsString('/crm/t/o/'.$recipient->tracking_token.'.gif', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringNotContainsString('https://dalytrac.com', $html);
        $this->assertStringNotContainsString('https://dalytrac.com', $plain);
        $this->assertCount(2, $recipient->links);
        $this->assertSame(ProspectStatus::Contacte, $prospect->fresh()->status);
        $this->assertStringNotContainsString($prospect->email, route('crm.tracking.open', $recipient->tracking_token));
    }

    public function test_public_pixel_counts_repeats_preserves_first_date_and_never_changes_status(): void
    {
        $campaign = $this->campaign();
        $prospect = $this->prospect(status: ProspectStatus::Contacte);
        $recipient = $this->trackedRecipient($campaign, $prospect);
        $url = route('crm.tracking.open', $recipient->tracking_token);
        $this->get($url)->assertOk()->assertHeader('Content-Type', 'image/gif')->assertHeaderMissing('Set-Cookie');
        $first = $recipient->fresh()->first_opened_at;
        $this->travel(2)->minutes();
        $this->get($url)->assertOk();
        $this->assertSame(2, $recipient->fresh()->open_count);
        $this->assertTrue($first->equalTo($recipient->fresh()->first_opened_at));
        $this->assertSame(ProspectStatus::Contacte, $prospect->fresh()->status);
        $this->head($url)->assertOk();
        $this->withHeader('Purpose', 'prefetch')->get($url)->assertOk();
        $this->assertSame(2, $recipient->fresh()->open_count);
        $this->get(route('crm.tracking.open', str_repeat('a', 64)))->assertOk();
        $this->get('/crm/t/o/invalid.gif')->assertNotFound();
        $this->assertSame(0, $recipient->fresh()->click_count);
    }

    public function test_clicks_redirect_to_saved_utm_destination_and_engage_once_without_inventing_an_open(): void
    {
        $campaign = $this->campaign();
        $campaign->update(['cta_text' => 'Go', 'cta_url' => 'https://dalytrac.com/produit?ref=token&utm_source=old#offer', 'utm_campaign' => 'launch']);
        $prospect = $this->prospect(status: ProspectStatus::Contacte);
        $recipient = $this->trackedRecipient($campaign, $prospect);
        $link = $recipient->links()->sole();
        $url = route('crm.tracking.click', $link->token);
        $destination = 'https://dalytrac.com/produit?ref=token&utm_source=dalytrac&utm_medium=email&utm_campaign=launch#offer';
        $this->get($url.'?url=https://evil.example')->assertRedirect($destination)->assertHeader('Referrer-Policy', 'no-referrer')->assertHeaderMissing('Set-Cookie');
        $first = $recipient->fresh()->first_clicked_at;
        $this->assertNull($recipient->fresh()->first_opened_at);
        $this->assertSame(ProspectStatus::Engage, $prospect->fresh()->status);
        $this->travel(1)->minutes();
        $this->get($url)->assertRedirect($destination);
        $this->assertSame(2, $recipient->fresh()->click_count);
        $this->assertSame(2, $link->fresh()->click_count);
        $this->assertSame($destination, $recipient->fresh()->last_clicked_url);
        $this->assertTrue($first->equalTo($recipient->fresh()->first_clicked_at));
        $this->assertTrue($first->equalTo($link->fresh()->first_clicked_at));
        $this->assertSame(1, $prospect->statusHistories()->where('source', 'campaign_click')->count());
        app(ProspectStatusService::class)->transition($prospect->fresh(), ProspectStatus::Contacte, manager: $this->manager);
        $this->get($url)->assertRedirect($destination);
        $this->assertSame(ProspectStatus::Contacte, $prospect->fresh()->status);
        $this->assertSame(1, $prospect->statusHistories()->where('source', 'campaign_click')->count());
    }

    public function test_clicks_leave_other_statuses_unchanged_and_invalid_unsent_and_prefetch_do_not_count(): void
    {
        $campaign = $this->campaign();
        $campaign->update(['cta_text' => 'Go', 'cta_url' => 'https://dalytrac.com']);
        foreach ([ProspectStatus::Nouveau, ProspectStatus::Engage, ProspectStatus::DemoEssai, ProspectStatus::Client, ProspectStatus::Perdu] as $status) {
            $prospect = $this->prospect($status->value.'@example.test', $status);
            $recipient = $this->trackedRecipient($campaign, $prospect);
            $url = route('crm.tracking.click', $recipient->links()->sole()->token);
            $this->head($url)->assertRedirect();
            $this->withHeader('Sec-Purpose', 'prefetch')->get($url)->assertRedirect();
            $this->assertSame(0, $recipient->fresh()->click_count);
            $this->flushHeaders();
            $this->get($url)->assertRedirect();
            $this->assertSame(1, $recipient->fresh()->click_count);
            $this->assertSame($status, $prospect->fresh()->status);
        }
        $failed = $this->trackedRecipient($campaign, $this->prospect('failed-track@example.test', ProspectStatus::Contacte), 'failed');
        $this->get(route('crm.tracking.open', $failed->tracking_token))->assertOk();
        $this->get(route('crm.tracking.click', $failed->links()->sole()->token))->assertRedirect();
        $this->assertSame(0, $failed->fresh()->open_count);
        $this->assertSame(0, $failed->fresh()->click_count);
        $this->get(route('crm.tracking.click', str_repeat('f', 64)))->assertNotFound();
        $this->get('/crm/t/c/not-a-token')->assertNotFound();
        $link = $failed->links()->sole();
        $link->update(['destination' => 'javascript:alert(1)']);
        $this->get(route('crm.tracking.click', $link->token))->assertNotFound();
    }

    public function test_tracking_reports_unique_recipients_and_per_url_counts(): void
    {
        $campaign = $this->campaign();
        $campaign->update(['cta_text' => 'Go', 'cta_url' => 'https://dalytrac.com', 'content' => 'See https://dalytrac.com/tarifs']);
        $recipient = $this->trackedRecipient($campaign, $this->prospect(status: ProspectStatus::Contacte));
        $other = $this->trackedRecipient($campaign, $this->prospect('other-track@example.test'));
        $this->assertNotSame($recipient->tracking_token, $other->tracking_token);
        $this->assertNotSame($recipient->links->first()->token, $other->links->first()->token);
        $pixel = route('crm.tracking.open', $recipient->tracking_token);
        $this->get($pixel);
        $this->get($pixel);
        $this->get($pixel);
        foreach ($recipient->links as $link) {
            $this->get(route('crm.tracking.click', $link->token))->assertRedirect();
        }
        $campaign->update(['status' => 'completed', 'completed_at' => now()]);
        $this->actingAs($this->manager, 'manager')->get(route('manager.campaigns.index'))->assertOk()->assertSee('Taux d’ouverture')->assertSee('50,0 %')
            ->assertViewHas('campaigns', fn ($rows) => $rows->first()->opened_count === 1 && $rows->first()->clicked_count === 1 && $rows->first()->sent_count === 2);
        $this->get(route('manager.campaigns.show', $campaign))->assertOk()->assertSee('Ouvert')->assertSee('Cliqué')->assertSee('3 ouverture(s)')->assertSee('2 clic(s)')->assertSee('Liens cliqués');
        $empty = $this->campaign();
        $empty->setAttribute('sent_count', 0);
        $this->assertSame('0,0 %', $empty->trackingRate(0));
    }

    public function test_prospect_timeline_shows_first_campaign_interactions_without_duplicates(): void
    {
        $campaign = $this->campaign();
        $campaign->update(['name' => 'Campagne découverte', 'cta_text' => 'Go', 'cta_url' => 'https://dalytrac.com']);
        $prospect = $this->prospect(status: ProspectStatus::Contacte);
        $recipient = $this->trackedRecipient($campaign, $prospect);
        $other = $this->trackedRecipient($campaign, $this->prospect('other-timeline@example.test'));
        foreach ([$recipient, $other] as $row) {
            for ($i = 0; $i < 3; $i++) {
                $this->get(route('crm.tracking.open', $row->tracking_token))->assertOk();
                $this->get(route('crm.tracking.click', $row->links()->sole()->token))->assertRedirect();
            }
        }
        $response = $this->actingAs($this->manager, 'manager')->get(route('manager.prospects.show', $prospect))->assertOk()
            ->assertSee('Email de la campagne « Campagne découverte » ouvert')
            ->assertSee('Lien de la campagne « Campagne découverte » cliqué')
            ->assertViewHas('timeline', fn ($rows) => $rows->where('kind', 'campaign_opened')->count() === 1 && $rows->where('kind', 'campaign_clicked')->count() === 1);
        $this->assertSame(3, $recipient->fresh()->open_count);
        $this->assertSame(3, $recipient->fresh()->click_count);
        $this->assertSame(ProspectStatus::Engage, $prospect->fresh()->status);
    }

    public function test_click_recording_survives_crm_failure_and_retries_without_duplicate_transition(): void
    {
        $campaign = $this->campaign();
        $campaign->update(['cta_text' => 'Go', 'cta_url' => 'https://dalytrac.com']);
        $prospect = $this->prospect(status: ProspectStatus::Contacte);
        $recipient = $this->trackedRecipient($campaign, $prospect);
        $url = route('crm.tracking.click', $recipient->links()->sole()->token);
        $mock = Mockery::mock(ProspectStatusService::class);
        $mock->shouldReceive('transition')->once()->andThrow(new RuntimeException('Temporary CRM error'));
        $this->app->instance(ProspectStatusService::class, $mock);
        $this->get($url)->assertRedirect();
        $first = $recipient->fresh()->first_clicked_at;
        $this->assertSame(1, $recipient->fresh()->click_count);
        $this->assertSame(ProspectStatus::Contacte, $prospect->fresh()->status);
        $this->app->forgetInstance(ProspectStatusService::class);
        $this->get($url)->assertRedirect();
        $this->assertSame(2, $recipient->fresh()->click_count);
        $this->assertTrue($first->equalTo($recipient->fresh()->first_clicked_at));
        $this->assertSame(ProspectStatus::Engage, $prospect->fresh()->status);
        $this->assertSame(1, $prospect->statusHistories()->where('source', 'campaign_click')->count());
        $this->assertArrayNotHasKey('tracking_token', $recipient->toArray());
    }

    public function test_tracking_dates_are_absolute_and_archived_prospects_are_not_modified(): void
    {
        $campaign = $this->campaign();
        $campaign->update(['cta_text' => 'Go', 'cta_url' => 'https://dalytrac.com']);
        $prospect = $this->prospect(status: ProspectStatus::Contacte);
        $recipient = $this->trackedRecipient($campaign, $prospect);
        $this->freezeTime();
        $at = now('UTC')->startOfSecond();
        DB::statement("SET TIME ZONE 'Asia/Tokyo'");
        try {
            $this->get(route('crm.tracking.open', $recipient->tracking_token))->assertOk();
            $prospect->delete();
            $this->get(route('crm.tracking.click', $recipient->links()->sole()->token))->assertRedirect();
            $this->assertTrue($at->equalTo($recipient->fresh()->first_opened_at));
            $this->assertTrue($at->equalTo($recipient->fresh()->first_clicked_at));
            $this->assertSame(ProspectStatus::Contacte, Prospect::withTrashed()->findOrFail($prospect->id)->status);
        } finally {
            DB::statement("SET TIME ZONE 'UTC'");
        }
    }

    public function test_global_rates_use_all_sent_campaigns_and_weight_recipients_beyond_pagination(): void
    {
        $first = $this->campaign();
        $first->update(['name' => 'Old sent campaign', 'status' => 'completed']);
        $second = $this->campaign();
        $second->update(['status' => 'completed_with_errors']);
        foreach ([$first, $second, $second, $second] as $index => $campaign) {
            $prospect = $this->prospect('global'.$index.'@example.test');
            $campaign->recipients()->create(['prospect_id' => $prospect->id, 'name' => $prospect->name, 'email' => $prospect->email, 'email_normalized' => $prospect->email,
                'status' => 'sent', 'sent_at' => now(), 'first_opened_at' => $index === 0 ? now() : null, 'open_count' => $index === 0 ? 30 : 0,
                'first_clicked_at' => $index < 2 ? now() : null, 'click_count' => $index < 2 ? 20 : 0]);
        }
        $draft = $this->campaign();
        $prospect = $this->prospect('draft-global@example.test');
        $draft->recipients()->create(['prospect_id' => $prospect->id, 'name' => 'Draft', 'email' => $prospect->email, 'email_normalized' => $prospect->email,
            'status' => 'sent', 'sent_at' => now(), 'first_opened_at' => now(), 'first_clicked_at' => now()]);
        $prospect = $this->prospect('failed-global@example.test');
        $second->recipients()->create(['prospect_id' => $prospect->id, 'name' => 'Failed', 'email' => $prospect->email, 'email_normalized' => $prospect->email,
            'status' => 'failed', 'first_opened_at' => now(), 'first_clicked_at' => now()]);
        for ($i = 0; $i < 21; $i++) {
            $this->campaign();
        }
        $this->actingAs($this->manager, 'manager');
        foreach ([1, 2] as $page) {
            $this->get(route('manager.campaigns.index', ['page' => $page]))->assertOk()
                ->assertViewHas('globalRates', ['Taux d’ouverture' => '25,0 %', 'Taux de clic' => '50,0 %']);
        }
        $this->get(route('manager.campaigns.index'))->assertViewHas('campaigns', fn ($rows) => ! $rows->contains('id', $first->id));
    }

    public function test_detail_has_exactly_six_kpis_and_empty_global_rates_are_zero(): void
    {
        $campaign = $this->campaign();
        $campaign->update(['status' => 'completed']);
        $this->actingAs($this->manager, 'manager');
        $this->get(route('manager.campaigns.index'))->assertOk()->assertViewHas('globalRates', ['Taux d’ouverture' => '0,0 %', 'Taux de clic' => '0,0 %']);
        $response = $this->get(route('manager.campaigns.show', $campaign))->assertOk()->assertDontSee('Taux d’ouverture')->assertDontSee('Taux de clic');
        $document = new \DOMDocument;
        @$document->loadHTML('<?xml encoding="utf-8" ?>'.$response->getContent());
        $labels = (new \DOMXPath($document))->query('//div[contains(concat(" ", normalize-space(@class), " "), " cc-detail-kpis ")]/a/span');
        $this->assertSame(['Total destinataires', 'Envoyés avec succès', 'En attente', 'Échecs', 'Ouverts', 'Cliqués'], array_map(fn ($node) => trim($node->textContent), iterator_to_array($labels)));
        $response->assertSee('Résultats par destinataire');
    }

    public function test_scheduled_campaign_waits_until_due_then_uses_existing_sender_once(): void
    {
        $this->travelTo(now('UTC')->startOfMinute());
        $campaign = $this->campaign()->fresh();
        $prospect = $this->prospect();
        $this->selectAll($campaign);
        $campaign->refresh();
        $due = now('UTC')->addHours(2);
        $this->actingAs($this->manager, 'manager');
        $data = ['wizard_final' => 1, 'confirm' => 1, 'revision' => $campaign->revision, 'content' => 'Scheduled message', 'schedule_enabled' => 1,
            'scheduled_date' => $due->format('Y-m-d'), 'scheduled_time' => $due->format('H:i')];
        $this->post(route('manager.campaigns.send', $campaign), $data)->assertSessionHasNoErrors()->assertRedirect();
        $this->post(route('manager.campaigns.send', $campaign), $data)->assertRedirect();
        $campaign->refresh();
        $this->assertSame('scheduled', $campaign->status);
        $this->assertNull($campaign->queued_at);
        $this->assertTrue($due->equalTo($campaign->scheduled_at));
        $this->assertSame('pending', $campaign->recipients()->sole()->status);
        $this->assertSame(ProspectStatus::Nouveau, $prospect->fresh()->status);
        $this->get(route('manager.campaigns.index'))->assertOk()->assertSee('Programmé')->assertSee($due->format('d/m/Y H:i'));
        $this->get(route('manager.campaigns.show', $campaign))->assertOk()->assertSee('Campagne programmée.')->assertSee('UTC')->assertDontSee('Envoi en cours :');
        $this->mailer();
        $this->assertSame(0, app(CrmCampaignSender::class)->batch());
        $this->assertSame('scheduled', $campaign->fresh()->status);
        $this->assertCount(0, $this->addresses);
        $this->travelTo($due->copy()->subSecond());
        $this->assertSame(0, app(CrmCampaignSender::class)->batch());
        $this->travelTo($due);
        $this->assertSame(1, app(CrmCampaignSender::class)->batch());
        $this->assertSame('completed', $campaign->fresh()->status);
        $this->assertTrue($due->equalTo($campaign->fresh()->queued_at));
        $this->assertSame(ProspectStatus::Contacte, $prospect->fresh()->status);
        $this->travel(61)->seconds();
        $this->assertSame(0, app(CrmCampaignSender::class)->batch());
        $this->assertCount(1, $this->addresses);
    }

    public function test_schedule_validation_preview_and_switch_off_preserve_immediate_behavior(): void
    {
        $campaign = $this->campaign()->fresh();
        $this->actingAs($this->manager, 'manager');
        $url = route('manager.campaigns.setup', $campaign);
        $data = ['step' => 3, 'revision' => $campaign->revision, 'content' => 'Message', 'schedule_enabled' => 1];
        $this->post($url, $data)->assertSessionHasErrors(['scheduled_date', 'scheduled_time']);
        $this->post($url, $data + ['scheduled_date' => now('UTC')->subDay()->format('Y-m-d'), 'scheduled_time' => '12:00'])->assertSessionHasErrors('scheduled_date');
        $this->post($url, $data + ['scheduled_date' => '2026-02-30', 'scheduled_time' => '25:00'])->assertSessionHasErrors(['scheduled_date', 'scheduled_time']);
        $due = now('UTC')->addDays(2)->startOfMinute();
        $this->post($url, $data + ['scheduled_date' => $due->format('Y-m-d'), 'scheduled_time' => $due->format('H:i')])->assertSessionHasNoErrors();
        $campaign->refresh();
        $this->assertSame('draft', $campaign->status);
        $this->assertTrue($due->equalTo($campaign->scheduled_at));
        $this->get(route('manager.campaigns.show', ['campaign' => $campaign, 'step' => 3]))->assertOk()->assertSee('Programmer l’envoi à une date ultérieure')->assertSee('Programmer la campagne');
        $this->get(route('manager.campaigns.preview', $campaign))->assertOk()->assertDontSee('/crm/t/');
        $this->post($url, ['step' => 3, 'revision' => $campaign->revision, 'content' => 'Immediate message', 'schedule_enabled' => 0,
            'scheduled_date' => 'invalid', 'scheduled_time' => 'invalid'])->assertSessionHasNoErrors();
        $this->assertNull($campaign->fresh()->scheduled_at);
        $this->prospect();
        $this->selectAll($campaign);
        $this->queue($campaign);
        $this->assertSame('queued', $campaign->fresh()->status);
        $this->assertNotNull($campaign->fresh()->queued_at);
    }

    public function test_scheduler_catches_up_overdue_campaigns_in_non_utc_database_without_releasing_future_campaigns(): void
    {
        $this->travelTo(now('UTC')->startOfMinute());
        $due = $this->campaign();
        $future = $this->campaign();
        $this->prospect();
        foreach ([$due, $future] as $campaign) {
            $this->selectAll($campaign);
        }
        $due->update(['scheduled_at' => now('UTC')->addMinute()]);
        $future->update(['scheduled_at' => now('UTC')->addDays(2)]);
        $this->queue($due);
        $future->update(['manager_id' => Manager::create(['name' => 'Second manager', 'email' => 'second@example.test'])->id]);
        $this->queue($future);
        $this->travel(3)->hours();
        DB::statement("SET TIME ZONE 'Asia/Tokyo'");
        try {
            $this->mailer();
            $this->artisan('crm:send-campaigns')->assertSuccessful();
            $this->assertSame('completed', $due->fresh()->status);
            $this->assertSame('scheduled', $future->fresh()->status);
            $this->assertCount(1, $this->addresses);
        } finally {
            DB::statement("SET TIME ZONE 'UTC'");
        }
    }

    public function test_expired_saved_schedule_cannot_be_confirmed_and_schedule_does_not_bypass_audience_checks(): void
    {
        $campaign = $this->campaign();
        $campaign->update(['scheduled_at' => now('UTC')->addMinute()]);
        $this->prospect();
        $this->selectAll($campaign);
        $this->travel(2)->minutes();
        $this->actingAs($this->manager, 'manager')->post(route('manager.campaigns.send', $campaign), ['confirm' => 1, 'revision' => $campaign->fresh()->revision])->assertSessionHasErrors('scheduled_date');
        $this->assertSame('draft', $campaign->fresh()->status);
        $empty = $this->campaign();
        $empty->update(['scheduled_at' => now('UTC')->addDay()]);
        $this->post(route('manager.campaigns.send', $empty), ['confirm' => 1, 'revision' => $empty->fresh()->revision])->assertSessionHasErrors('recipients');
        $this->assertSame('draft', $empty->fresh()->status);
    }

    public function test_campaign_greeting_is_personalized_escaped_and_precedes_message_in_both_email_parts(): void
    {
        $campaign = $this->campaign();
        $campaign->update(['content' => 'Votre message personnalisé.', 'cta_text' => 'Découvrir', 'cta_url' => 'https://dalytrac.com/produit']);
        $prospect = $this->prospect();
        $prospect->update(['name' => 'Amélie & <Client>']);
        $this->selectAll($campaign);
        $recipient = $campaign->recipients()->sole();
        $mail = new ProspectCampaignMail($campaign->subject, $campaign->content, null, $campaign->cta_text, $campaign->ctaLink(), null, null, $recipient);
        $sent = Mail::mailer('array')->to('recipient@example.test')->send($mail);
        $message = $sent->getSymfonySentMessage()->getOriginalMessage();
        $html = $message->getHtmlBody();
        $text = $message->getTextBody();
        $this->assertStringContainsString('Bonjour Amélie &amp; &lt;Client&gt;,', $html);
        $this->assertStringContainsString('Bonjour Amélie & <Client>,', $text);
        $this->assertLessThan(strpos($html, 'Votre message personnalisé.'), strpos($html, 'Bonjour Amélie'));
        $this->assertStringContainsString('L’équipe DalyTrac', $html);
        $this->assertStringContainsString('L’équipe DalyTrac', $text);
        $this->assertStringContainsString('aria-label="DalyTrac"', $html);
        $this->assertStringContainsString('/crm/t/c/', $html);
        $this->assertStringContainsString('/crm/t/o/', $html);
        $this->assertSame('Votre message personnalisé.', $campaign->fresh()->content);
        $this->assertSame(0, $recipient->fresh()->open_count);
    }

    public function test_preview_uses_selected_name_without_tracking_and_creation_explains_automatic_greeting(): void
    {
        $campaign = $this->campaign();
        $campaign->update(['content' => 'Message sans formule d’appel.']);
        $prospect = $this->prospect();
        $prospect->update(['name' => 'Sarah Dupont']);
        $this->selectAll($campaign);
        $this->actingAs($this->manager, 'manager');
        $this->get(route('manager.campaigns.preview', $campaign))->assertOk()->assertSee('Bonjour Sarah Dupont,')->assertSee('Message sans formule d’appel.')->assertSee('L’équipe DalyTrac')->assertDontSee('/crm/t/');
        $this->assertNull($campaign->recipients()->sole()->tracking_token);
        $this->get(route('manager.campaigns.show', ['campaign' => $campaign, 'step' => 3]))->assertOk()->assertSee('Bonjour + nom du prospect')->assertSee('automatiquement ajoutée');
    }

    public function test_sent_email_preview_uses_saved_recipient_and_media_without_tracking_or_resending(): void
    {
        \Illuminate\Support\Facades\Storage::fake('local');
        $file = $this->campaignImage();
        $path = $file->store('preview-test', 'local');
        $campaign = $this->campaign();
        $campaign->update(['content' => 'Message réellement enregistré.', 'cta_text' => 'Découvrir DalyTrac', 'cta_url' => 'https://dalytrac.com/produit',
            'media' => ['image' => ['path' => $path, 'name' => 'banner.png', 'size' => $file->getSize(), 'mime' => 'image/png']]]);
        $prospect = $this->prospect('sarah@example.test');
        $prospect->update(['name' => 'Sarah Dupont']);
        $recipient = $this->trackedRecipient($campaign, $prospect);
        $second = $this->trackedRecipient($campaign, $this->prospect('second-preview@example.test'));
        $failed = $this->trackedRecipient($campaign, $this->prospect('failed-preview@example.test'), 'failed');
        $campaign->update(['status' => 'completed']);
        $before = $recipient->fresh()->getRawOriginal();
        $linkCount = \App\Models\CrmCampaignLink::count();
        $historyCount = $prospect->statusHistories()->count();
        $prospect->update(['name' => 'Nom modifié après envoi']);
        Mail::fake();
        $this->actingAs($this->manager, 'manager');
        $this->get(route('manager.campaigns.show', $campaign))->assertOk()->assertSee('Voir l’email envoyé');
        $this->get(route('manager.campaigns.sent-email', ['campaign' => $campaign, 'recipient' => $recipient->id]))->assertOk()
            ->assertSee('Sarah Dupont')->assertDontSee('failed-preview@example.test')->assertSee('sandbox=""', false)->assertSee('recipient='.$recipient->id, false);
        $response = $this->get(route('manager.campaigns.sent-email.content', ['campaign' => $campaign, 'recipient' => $recipient->id]));
        $document = new \DOMDocument;
        @$document->loadHTML($response->getContent());
        foreach (['Message réellement enregistré.', 'Découvrir DalyTrac', 'L’équipe DalyTrac'] as $text) {
            $this->assertStringContainsString($text, $document->textContent);
        }
        $response->assertOk()->assertSee('Bonjour Sarah Dupont,')
            ->assertSee('data:image/png;base64,', false)->assertSee('aria-label="DalyTrac"', false)->assertSee('aria-disabled="true"', false)
            ->assertDontSee('href=', false)->assertDontSee('/crm/t/')->assertDontSee('Nom modifié après envoi');
        $this->get(route('manager.campaigns.sent-email.content', ['campaign' => $campaign, 'recipient' => $second->id]))->assertOk()->assertSee('Bonjour Contact,');
        $this->assertSame($before, $recipient->fresh()->getRawOriginal());
        $this->assertSame($linkCount, \App\Models\CrmCampaignLink::count());
        $this->assertSame($historyCount, $prospect->statusHistories()->count());
        Mail::assertNothingSent();
    }

    public function test_sent_preview_rejects_other_campaign_or_unsent_recipients_and_hides_button_until_sent(): void
    {
        $campaign = $this->campaign();
        $campaign->update(['status' => 'queued']);
        $pending = $campaign->recipients()->create(['prospect_id' => $this->prospect()->id, 'name' => 'Pending', 'email' => 'contact@example.test', 'email_normalized' => 'contact@example.test', 'status' => 'pending']);
        $otherCampaign = $this->campaign();
        $other = $this->trackedRecipient($otherCampaign, $this->prospect('outside@example.test'));
        $this->actingAs($this->manager, 'manager');
        $this->get(route('manager.campaigns.show', $campaign))->assertOk()->assertDontSee('Voir l’email envoyé');
        foreach (['manager.campaigns.sent-email', 'manager.campaigns.sent-email.content'] as $route) {
            $this->get(route($route, $campaign))->assertNotFound();
            $this->get(route($route, ['campaign' => $campaign, 'recipient' => $pending->id]))->assertNotFound();
            $this->get(route($route, ['campaign' => $campaign, 'recipient' => $other->id]))->assertNotFound();
        }
    }

    public function test_total_campaigns_card_counts_all_pages_and_excludes_deleted_campaigns(): void
    {
        for ($index = 0; $index < 23; $index++) {
            $this->campaign();
        }
        $removed = $this->campaign();
        $removed->delete();
        $response = $this->actingAs($this->manager, 'manager')->get(route('manager.campaigns.index'))->assertOk()->assertSee('Total campagnes')->assertSee('Supprimer la campagne')->assertSee('data-delete-campaign', false);
        $response->assertViewHas('campaigns', fn ($rows) => $rows->total() === 23 && $rows->count() === 20);
        $document = new \DOMDocument;
        @$document->loadHTML('<?xml encoding="utf-8" ?>'.$response->getContent());
        $value = (new \DOMXPath($document))->query('//div[contains(concat(" ", normalize-space(@class), " "), " cc-rate-total ")]/strong')->item(0)->textContent;
        $this->assertSame('23', trim($value));
    }

    public function test_delete_keeps_prospects_media_and_existing_tracking_links_but_removes_campaign_from_ui(): void
    {
        \Illuminate\Support\Facades\Storage::fake('local');
        \Illuminate\Support\Facades\Storage::disk('local')->put('campaign-delete/retained.txt', 'Retained private asset');
        $campaign = $this->campaign();
        $campaign->update(['cta_text' => 'Go', 'cta_url' => 'https://dalytrac.com/produit']);
        $prospect = $this->prospect(status: ProspectStatus::Contacte);
        $recipient = $this->trackedRecipient($campaign, $prospect);
        $link = $recipient->links()->sole();
        $campaign->update(['status' => 'completed', 'media' => ['attachment' => ['path' => 'campaign-delete/retained.txt']]]);
        $this->actingAs($this->manager, 'manager')->delete(route('manager.campaigns.destroy', $campaign))->assertRedirect(route('manager.campaigns.index'));
        $this->assertSoftDeleted('crm_campaigns', ['id' => $campaign->id]);
        $this->assertSame(0, CrmCampaign::count());
        $this->assertSame(1, CrmCampaignRecipient::count());
        $this->assertSame(1, Prospect::count());
        $this->assertSame(ProspectStatus::Contacte, $prospect->fresh()->status);
        $this->assertNotNull($recipient->fresh()->campaign);
        \Illuminate\Support\Facades\Storage::disk('local')->assertExists('campaign-delete/retained.txt');
        $this->get(route('manager.campaigns.show', $campaign))->assertNotFound();
        $this->post(route('manager.campaigns.send', $campaign), ['confirm' => 1, 'revision' => 0])->assertNotFound();
        $this->get(route('manager.campaigns.index'))->assertOk()->assertDontSee($campaign->name)->assertViewHas('globalRates', ['Taux d’ouverture' => '0,0 %', 'Taux de clic' => '0,0 %']);
        $this->get(route('crm.tracking.open', $recipient->tracking_token))->assertOk();
        $this->get(route('crm.tracking.click', $link->token))->assertRedirect($link->destination);
        $this->assertSame(1, $recipient->fresh()->open_count);
        $this->assertSame(1, $recipient->fresh()->click_count);
        $this->assertSame(ProspectStatus::Engage, $prospect->fresh()->status);
    }

    public function test_delete_stops_pending_and_scheduled_campaigns_without_affecting_other_sends(): void
    {
        $queued = $this->campaign();
        $scheduled = $this->campaign();
        $active = $this->campaign();
        $this->prospect();
        foreach ([$queued, $scheduled, $active] as $campaign) {
            $this->selectAll($campaign);
        }
        $scheduled->update(['scheduled_at' => now('UTC')->addHour()]);
        foreach ([$queued, $scheduled, $active] as $campaign) {
            $campaign->update(['manager_id' => Manager::create(['name' => 'Other manager', 'email' => 'other'.$campaign->id.'@example.test'])->id]);
            $this->queue($campaign);
        }
        $this->actingAs($this->manager, 'manager');
        foreach ([$queued, $scheduled] as $campaign) {
            $this->delete(route('manager.campaigns.destroy', $campaign))->assertRedirect();
        }
        $this->travel(2)->hours();
        $this->mailer();
        $this->assertSame(1, app(CrmCampaignSender::class)->batch());
        $this->assertSame('completed', $active->fresh()->status);
        $this->assertSame('pending', $queued->recipients()->sole()->status);
        $this->assertSame('pending', $scheduled->recipients()->sole()->status);
        $this->assertSame('scheduled', CrmCampaign::withTrashed()->findOrFail($scheduled->id)->status);
        $this->assertCount(1, $this->addresses);
    }

    public function test_campaign_deletion_is_rejected_during_an_active_smtp_batch(): void
    {
        $campaign = $this->campaign();
        config(['database.connections.crm_delete_lock_test' => config('database.connections.pgsql')]);
        $other = DB::connection('crm_delete_lock_test');
        $key = 'crm.campaign.sender:'.DB::connection()->getDatabaseName();
        $other->select('SELECT pg_advisory_lock(hashtextextended(?, 0))', [$key]);
        try {
            $this->actingAs($this->manager, 'manager')->delete(route('manager.campaigns.destroy', $campaign))->assertSessionHasErrors('campaign');
            $this->assertNotNull(CrmCampaign::find($campaign->id));
        } finally {
            $other->select('SELECT pg_advisory_unlock(hashtextextended(?, 0))', [$key]);
            DB::purge('crm_delete_lock_test');
        }
        $this->delete(route('manager.campaigns.destroy', $campaign))->assertRedirect();
        $this->assertSoftDeleted('crm_campaigns', ['id' => $campaign->id]);
    }

    public function test_detail_kpi_cards_filter_recipients_without_changing_counts_or_tracking(): void
    {
        $campaign = $this->campaign();
        $campaign->update(['status' => 'sending']);
        $expected = ['all' => [], 'sent' => [], 'pending' => [], 'failed' => [], 'opened' => [], 'clicked' => []];
        foreach (['sent', 'sent', 'sent', 'sent', 'selected', 'pending', 'sending', 'failed', 'uncertain', 'skipped'] as $index => $status) {
            $prospect = $this->prospect('filter'.$index.'@example.test');
            $row = $campaign->recipients()->create(['prospect_id' => $prospect->id, 'name' => 'Recipient '.$index, 'email' => $prospect->email, 'email_normalized' => $prospect->email,
                'status' => $status, 'sent_at' => $status === 'sent' ? now() : null,
                'first_opened_at' => in_array($index, [0, 2], true) ? now() : null, 'first_clicked_at' => in_array($index, [1, 2], true) ? now() : null]);
            $expected['all'][] = $row->id;
            if ($status === 'sent') {
                $expected['sent'][] = $row->id;
            }
            if (in_array($status, ['selected', 'pending', 'sending'], true)) {
                $expected['pending'][] = $row->id;
            }
            if ($status === 'failed') {
                $expected['failed'][] = $row->id;
            }
            if (in_array($index, [0, 2], true)) {
                $expected['opened'][] = $row->id;
            }
            if (in_array($index, [1, 2], true)) {
                $expected['clicked'][] = $row->id;
            }
        }
        $before = $campaign->recipients()->orderBy('id')->get()->map->getRawOriginal()->all();
        $this->actingAs($this->manager, 'manager');
        foreach ($expected as $filter => $ids) {
            $response = $this->get(route('manager.campaigns.show', ['campaign' => $campaign, 'result' => $filter]))->assertOk()
                ->assertViewHas('resultFilter', $filter)
                ->assertViewHas('results', fn ($rows) => $rows->pluck('id')->all() === $ids)
                ->assertViewHas('campaign', fn ($value) => $value->sent_count === 4 && $value->opened_count === 2 && $value->clicked_count === 2);
            $document = new \DOMDocument;
            @$document->loadHTML($response->getContent());
            $active = (new \DOMXPath($document))->query('//a[contains(concat(" ", normalize-space(@class), " "), " cc-kpi-filter ")][@aria-current="page"]');
            $this->assertSame(1, $active->length);
            $this->assertSame('10', (new \DOMXPath($document))->query('//a[contains(concat(" ", normalize-space(@class), " "), " cc-kpi-filter ")]/strong')->item(0)->textContent);
        }
        $this->assertSame($before, $campaign->recipients()->orderBy('id')->get()->map->getRawOriginal()->all());
        $this->getJson(route('manager.campaigns.show', ['campaign' => $campaign, 'result' => 'invalid']))->assertUnprocessable();
        $empty = $this->campaign();
        $empty->update(['status' => 'completed']);
        $this->get(route('manager.campaigns.show', ['campaign' => $empty, 'result' => 'clicked']))->assertOk()->assertSee('Aucun destinataire pour ce filtre.');
    }

    public function test_recipient_filter_persists_through_pagination_and_cards_reset_the_page(): void
    {
        $campaign = $this->campaign();
        $campaign->update(['status' => 'completed']);
        for ($index = 0; $index < 28; $index++) {
            $prospect = $this->prospect('pagefilter'.$index.'@example.test');
            $campaign->recipients()->create(['prospect_id' => $prospect->id, 'name' => 'Recipient '.$index, 'email' => $prospect->email, 'email_normalized' => $prospect->email,
                'status' => 'sent', 'sent_at' => now(), 'first_clicked_at' => now()]);
        }
        $response = $this->actingAs($this->manager, 'manager')->get(route('manager.campaigns.show', ['campaign' => $campaign, 'result' => 'clicked', 'results_page' => 2]))->assertOk()
            ->assertViewHas('results', fn ($rows) => $rows->count() === 3 && $rows->total() === 28 && $rows->currentPage() === 2);
        $document = new \DOMDocument;
        @$document->loadHTML($response->getContent());
        $links = (new \DOMXPath($document))->query('//a[contains(concat(" ", normalize-space(@class), " "), " cc-kpi-filter ")]');
        $this->assertSame(6, $links->length);
        foreach ($links as $link) {
            $this->assertStringNotContainsString('results_page', $link->getAttribute('href'));
        }
        $this->assertStringContainsString('result=clicked', $links->item(5)->getAttribute('href'));
        $this->assertStringNotContainsString('result=', $links->item(0)->getAttribute('href'));
    }

    private function bulkCampaign(int $size, ?int $managerId = null): CrmCampaign
    {
        $campaign = $this->campaign();
        if ($managerId) {
            $campaign->update(['manager_id' => $managerId]);
        }
        $users = [];
        for ($i = 0; $i < $size; $i++) {
            $users[] = ['name' => 'Recipient', 'email' => 'bulk'.$campaign->id.'-'.$i.'@example.test'];
        }
        DB::table('users')->insert($users);
        $rows = [];
        foreach (User::where('email', 'like', 'bulk'.$campaign->id.'-%')->get() as $user) {
            $rows[] = ['campaign_id' => $campaign->id, 'user_id' => $user->id,
                'name' => $user->name, 'email' => $user->email, 'email_normalized' => $user->email];
        }
        DB::table('crm_campaign_recipients')->insert($rows);

        return $campaign;
    }

    public static function hourlyVolumes(): array
    {
        return ['under limit' => [25], '200 recipients' => [200], '350 recipients' => [350]];
    }

    /** @dataProvider hourlyVolumes */
    public function test_hourly_cap_keeps_remaining_recipients_pending_and_scheduler_resumes(int $size): void
    {
        $start = \Carbon\CarbonImmutable::parse('2026-10-30T10:00:00Z');
        $this->travelTo($start);
        config(['crm.campaign_batch_size' => 100, 'crm.campaign_hourly_limit' => 900]); // Hard ceiling is still 150.
        $campaign = $this->bulkCampaign($size);
        $this->queue($campaign);
        $this->mailer();
        $total = 0;
        for ($hour = 0; $total < $size; $hour++) {
            $this->travelTo($start->addSeconds($hour * 3661));
            $this->artisan('crm:send-campaigns')->assertSuccessful();
            $this->travel(61)->seconds();
            $this->artisan('crm:send-campaigns')->assertSuccessful();
            $total = min($size, ($hour + 1) * 150);
            $this->assertSame($total, $campaign->recipients()->where('status', 'sent')->count());
            $this->assertSame($size - $total, $campaign->recipients()->where('status', 'pending')->count());
            $this->assertSame(0, $campaign->recipients()->whereIn('status', ['failed', 'uncertain'])->count());
            $this->assertSame($total === $size ? 'completed' : 'sending', $campaign->fresh()->status);
            if ($total < $size) {
                $this->assertNull($campaign->fresh()->completed_at);
                $this->travel(61)->seconds();
                $this->assertSame(0, app(CrmCampaignSender::class)->batch());
            }
        }
        $this->assertCount($size, array_unique($this->addresses));
        $this->assertCount($size, $this->addresses);
        $this->assertSame($size, DB::table('crm_campaign_smtp_attempts')->count());
    }

    public function test_account_hourly_budget_is_shared_by_campaigns_and_mailer_aliases(): void
    {
        config(['crm.campaign_batch_size' => 100]);
        $this->travelTo(now('UTC')->startOfHour());
        $first = $this->bulkCampaign(100);
        $manager = Manager::create(['name' => 'Other manager', 'email' => 'other@example.test']);
        $second = $this->bulkCampaign(100, $manager->id);
        $this->queue($first);
        $this->queue($second);
        $this->mailer();
        $this->assertSame(100, app(CrmCampaignSender::class)->batch());
        $this->travel(61)->seconds();
        config(['mail.mailers.alias' => config('mail.mailers.smtp'), 'mail.default' => 'alias']);
        $this->assertSame(50, (new CrmCampaignSender(app(ProspectStatusService::class)))->batch());
        $this->travel(61)->seconds();
        $this->assertSame(0, app(CrmCampaignSender::class)->batch());
        $this->assertSame(50, $second->recipients()->where('status', 'pending')->count());
        $this->assertSame(150, DB::table('crm_campaign_smtp_attempts')->count());
    }

    public function test_rolling_window_exact_boundary_and_functional_emails_are_not_limited(): void
    {
        $start = \Carbon\CarbonImmutable::parse('2026-10-30T10:23:45Z');
        $this->travelTo($start);
        $smtp = app(\App\Services\CrmCampaignSmtpBudget::class);
        $account = $smtp->accountKey();
        DB::table('crm_campaign_smtp_attempts')->insert(array_fill(0, 150, [
            'account_key' => $account, 'attempted_at' => $start->toISOString(),
        ]));
        $campaign = $this->bulkCampaign(1);
        $this->queue($campaign);
        $this->assertSame(0, app(CrmCampaignSender::class)->batch());
        $this->travelTo($start->addSeconds(3599));
        $this->assertSame(0, app(CrmCampaignSender::class)->batch());
        $this->assertNull($campaign->recipients()->sole()->attempted_at);
        // The CRM budget is not installed as a global mail middleware/listener.
        $functional = Mail::mailer('array');
        $message = $functional->raw('Important notification', fn ($mail) => $mail->to('owner@example.test')->subject('Notification'));
        $this->assertNotNull($message);
        $this->assertCount(1, $functional->getSymfonyTransport()->messages());
        $this->assertSame(150, DB::table('crm_campaign_smtp_attempts')->count());
        $this->travelTo($start->addHour());
        $this->mailer();
        $this->assertSame(1, app(CrmCampaignSender::class)->batch());
    }

    public function test_legacy_attempts_count_after_upgrade_and_account_changes_have_separate_budgets(): void
    {
        $campaign = $this->bulkCampaign(1);
        $recipient = $campaign->recipients()->sole();
        $recipient->update(['attempted_at' => now('UTC')]);
        $smtp = app(\App\Services\CrmCampaignSmtpBudget::class);
        $account = $smtp->accountKey();
        $this->assertSame(1, $smtp->attemptsSince($account, now('UTC')->subHour()->toISOString()));
        DB::transaction(fn () => $smtp->record($recipient, $account));
        $this->assertSame(1, $smtp->attemptsSince($account, now('UTC')->subHour()->toISOString()));
        config(['mail.mailers.smtp.username' => 'different-account@example.test']);
        $other = $smtp->accountKey();
        $this->assertNotSame($account, $other);
        $this->assertSame(0, $smtp->attemptsSince($other, now('UTC')->subHour()->toISOString()));
        $campaign->forceDelete();
        $this->assertSame(1, $smtp->attemptsSince($account, now('UTC')->subHour()->toISOString()));
    }

    public function test_daily_limit_blocks_send_and_schedule_and_survives_deletion(): void
    {
        $this->travelTo(\Carbon\CarbonImmutable::parse('2026-10-30T23:59:00Z'));
        DB::statement("SET TIME ZONE 'Asia/Tokyo'");
        $first = $this->bulkCampaign(1);
        $second = $this->bulkCampaign(1);
        $this->actingAs($this->manager, 'manager');
        $this->postJson(route('manager.campaigns.send', $first), ['confirm' => 1, 'revision' => 0])->assertRedirect();
        // Replaying the same confirmation must be idempotent.
        $this->postJson(route('manager.campaigns.send', $first), ['confirm' => 1, 'revision' => 0])->assertRedirect();
        $this->postJson(route('manager.campaigns.send', $second), ['confirm' => 1, 'revision' => 0])
            ->assertUnprocessable()->assertJsonValidationErrors('campaign')->assertSee('une seule campagne');
        $second->update(['scheduled_at' => now('UTC')->addDays(2)]);
        $this->postJson(route('manager.campaigns.send', $second), ['confirm' => 1, 'revision' => 0])->assertUnprocessable();
        $first->forceDelete();
        $this->postJson(route('manager.campaigns.send', $second), ['confirm' => 1, 'revision' => 0])->assertUnprocessable();
        $this->assertSame('selected', $second->recipients()->sole()->status);
        $this->assertSame(1, DB::table('crm_campaign_daily_launches')->count());
        $this->travel(1)->minutes(); // Midnight UTC, even though DB is in Tokyo.
        $this->postJson(route('manager.campaigns.send', $second), ['confirm' => 1, 'revision' => 0])->assertRedirect();
        $this->assertSame('scheduled', $second->fresh()->status);
        $this->assertSame(2, DB::table('crm_campaign_daily_launches')->count());
    }

    public function test_scheduling_consumes_today_and_launch_limit_belongs_to_actual_manager(): void
    {
        $first = $this->bulkCampaign(1);
        $first->update(['scheduled_at' => now('UTC')->addDays(2)]);
        $second = $this->bulkCampaign(1);
        $this->actingAs($this->manager, 'manager');
        $this->postJson(route('manager.campaigns.send', $first), ['confirm' => 1, 'revision' => 0])->assertRedirect();
        $this->postJson(route('manager.campaigns.send', $second), ['confirm' => 1, 'revision' => 0])->assertUnprocessable();
        $other = Manager::create(['name' => 'Other', 'email' => 'other@example.test']);
        $this->actingAs($other, 'manager');
        $this->postJson(route('manager.campaigns.send', $second), ['confirm' => 1, 'revision' => 0])->assertRedirect();
        $this->assertSame($other->id, (int) DB::table('crm_campaign_daily_launches')->where('campaign_id', $second->id)->value('manager_id'));
    }

    public function test_quota_refusal_pauses_account_and_retries_only_unaccepted_recipient_after_one_hour(): void
    {
        $this->travelTo(\Carbon\CarbonImmutable::parse('2026-10-30T10:00:00Z'));
        config(['crm.campaign_batch_size' => 100]);
        $first = $this->bulkCampaign(3);
        $otherManager = Manager::create(['name' => 'Other', 'email' => 'other@example.test']);
        $second = $this->bulkCampaign(1, $otherManager->id);
        $this->queue($first);
        $this->queue($second);
        $recipients = $first->recipients()->orderBy('id')->get();
        $attempts = [];
        $accepted = [];
        Mail::shouldReceive('to')->andReturnUsing(function ($address) use (&$attempts, &$accepted, $recipients) {
            $attempts[$address] = ($attempts[$address] ?? 0) + 1;
            $pending = Mockery::mock();
            $pending->shouldReceive('send')->once()->andReturnUsing(function () use ($address, &$attempts, &$accepted, $recipients) {
                if ($address === $recipients[1]->email && $attempts[$address] <= 2) {
                    throw new \Symfony\Component\Mailer\Exception\UnexpectedResponseException('Expected response code "250" but got code "550": 550-5.7.0 Mail quota exceeded for sender@example.test: You have exceeded the limit of 200 messages per hour and per account. Please, re-try later.', 550);
                }
                $accepted[] = $address;

                return (object) ['accepted' => true];
            });

            return $pending;
        });
        $this->assertSame(1, app(CrmCampaignSender::class)->batch());
        $retry = $recipients[1]->fresh();
        $this->assertSame('pending', $retry->status);
        $this->assertSame(550, $retry->last_smtp_code);
        $this->assertTrue($retry->next_attempt_at->equalTo(now('UTC')->addHour()));
        $this->assertSame('pending', $recipients[2]->fresh()->status);
        $this->assertNull($recipients[2]->fresh()->attempted_at);
        $this->assertSame('queued', $second->fresh()->status);
        $this->assertSame(0, app(CrmCampaignSender::class)->batch());
        $this->travel(3599)->seconds();
        $this->assertSame(0, app(CrmCampaignSender::class)->batch());
        $this->travel(1)->seconds();
        $this->assertSame(0, app(CrmCampaignSender::class)->batch()); // Quota still exceeded: postpone one more hour.
        $this->assertSame('pending', $retry->fresh()->status);
        $this->travel(1)->hours();
        $this->assertSame(3, app(CrmCampaignSender::class)->batch());
        $this->assertCount(4, $accepted);
        $this->assertCount(4, array_unique($accepted));
        $this->assertSame(1, $attempts[$recipients[0]->email]);
        $this->assertSame(3, $retry->fresh()->attempts_count);
        $this->assertNull($retry->fresh()->next_attempt_at);
        $this->assertSame('completed', $first->fresh()->status);
        $this->assertSame('completed', $second->fresh()->status);
        $this->assertSame(6, DB::table('crm_campaign_smtp_attempts')->count());
    }

    public static function nonQuotaFailures(): array
    {
        return [
            'nonexistent address' => [new \Symfony\Component\Mailer\Exception\UnexpectedResponseException('550 5.1.1 User unknown', 550), 'failed'],
            'invalid address' => [new \Symfony\Component\Mailer\Exception\UnexpectedResponseException('553 5.1.3 Bad recipient address', 553), 'failed'],
            'recipient mailbox full' => [new \Symfony\Component\Mailer\Exception\UnexpectedResponseException('552 5.2.2 Mailbox quota exceeded', 552), 'failed'],
            'ambiguous timeout' => [new \Symfony\Component\Mailer\Exception\TransportException('Timed out after DATA'), 'uncertain'],
            'quota words without SMTP refusal' => [new RuntimeException('Mail quota exceeded per hour'), 'failed'],
        ];
    }

    /** @dataProvider nonQuotaFailures */
    public function test_permanent_or_uncertain_failures_are_never_retried(\Throwable $error, string $status): void
    {
        $campaign = $this->bulkCampaign(1);
        $this->queue($campaign);
        $pending = Mockery::mock();
        $pending->shouldReceive('send')->once()->andThrow($error);
        Mail::shouldReceive('to')->once()->andReturn($pending);
        $this->assertSame(0, app(CrmCampaignSender::class)->batch());
        $this->travel(2)->hours();
        $this->assertSame(0, app(CrmCampaignSender::class)->batch());
        $this->assertSame($status, $campaign->recipients()->sole()->status);
        $this->assertNull($campaign->recipients()->sole()->next_attempt_at);
        $this->assertSame(0, DB::table('crm_campaign_smtp_pauses')->count());
    }

    private function limitsWorker(string $mode, int $campaignId = 0): array
    {
        $process = proc_open([PHP_BINARY, base_path('tests/Support/crm-campaign-limits-worker.php'),
            $this->schema, config('database.connections.pgsql.database'), $mode, (string) $campaignId],
            [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, base_path());
        $this->assertIsResource($process);
        fclose($pipes[0]);

        return [$process, $pipes];
    }

    private function limitsWorkerResult(array $worker): int
    {
        [$process, $pipes] = $worker;
        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $this->assertSame(0, proc_close($process), $stderr);
        $this->assertContains($stdout, ['0', '1']);

        return (int) $stdout;
    }

    public function test_concurrent_processes_share_last_hourly_slot(): void
    {
        config(['mail.mailers.smtp.host' => 'smtp.example.test', 'mail.mailers.smtp.username' => 'campaigns@example.test']);
        $first = $this->bulkCampaign(1);
        $second = $this->bulkCampaign(1, Manager::create(['name' => 'Other', 'email' => 'other@example.test'])->id);
        $this->queue($first);
        $this->queue($second);
        DB::table('crm_campaign_smtp_attempts')->insert(array_fill(0, 149, [
            'account_key' => app(\App\Services\CrmCampaignSmtpBudget::class)->accountKey(),
            'attempted_at' => now('UTC')->subMinutes(5)->toISOString(),
        ]));
        $workers = [$this->limitsWorker('send'), $this->limitsWorker('send')];
        $this->assertSame(1, $this->limitsWorkerResult($workers[0]) + $this->limitsWorkerResult($workers[1]));
        $this->assertSame(150, DB::table('crm_campaign_smtp_attempts')->count());
        $this->assertSame(1, CrmCampaignRecipient::where('status', 'sent')->count());
        $this->assertSame(1, CrmCampaignRecipient::where('status', 'pending')->count());
    }

    public function test_concurrent_daily_launches_allow_exactly_one_campaign(): void
    {
        $first = $this->bulkCampaign(1);
        $second = $this->bulkCampaign(1);
        $workers = [$this->limitsWorker('launch', $first->id), $this->limitsWorker('launch', $second->id)];
        $this->assertSame(1, $this->limitsWorkerResult($workers[0]) + $this->limitsWorkerResult($workers[1]));
        $this->assertSame(1, DB::table('crm_campaign_daily_launches')->count());
        $this->assertSame(1, CrmCampaign::where('status', 'queued')->count());
        $this->assertSame(1, CrmCampaign::where('status', 'draft')->count());
    }

    public function test_deferred_quota_recipient_rechecks_consent_before_retry(): void
    {
        $this->travelTo(now('UTC')->startOfMinute());
        $campaign = $this->bulkCampaign(1);
        $this->queue($campaign);
        $recipient = $campaign->recipients()->sole();
        $pending = Mockery::mock();
        $pending->shouldReceive('send')->once()->andThrow(new \Symfony\Component\Mailer\Exception\UnexpectedResponseException(
            '550 Mail quota exceeded: limit of 200 messages per hour and per account. Please, re-try later.', 550));
        Mail::shouldReceive('to')->once()->andReturn($pending);
        $this->assertSame(0, app(CrmCampaignSender::class)->batch());
        $this->assertSame('pending', $recipient->fresh()->status);
        $prospect = Prospect::create(['name' => 'Unsubscribed', 'email' => $recipient->email]);
        $prospect->forceFill(['unsubscribed_at' => now('UTC')])->save();
        $this->travel(1)->hours();
        $this->assertSame(0, app(CrmCampaignSender::class)->batch());
        $this->assertSame('skipped', $recipient->fresh()->status);
        $this->assertSame(1, DB::table('crm_campaign_smtp_attempts')->count());
    }

    public function test_composite_mailers_share_underlying_account_budget(): void
    {
        $smtp = app(\App\Services\CrmCampaignSmtpBudget::class);
        $key = $smtp->accountKey();
        DB::table('crm_campaign_smtp_attempts')->insert(array_fill(0, 150, [
            'account_key' => $key, 'attempted_at' => now('UTC')->toISOString(),
        ]));
        config(['mail.mailers.secondary' => array_merge(config('mail.mailers.smtp'), ['username' => 'second@example.test']),
            'mail.mailers.pool' => ['transport' => 'roundrobin', 'mailers' => ['smtp', 'secondary']], 'mail.default' => 'pool']);
        $this->assertContains($key, $smtp->accountKeys());
        $this->assertFalse($smtp->available($smtp->accountKeys()));
    }

    public function test_limit_upgrade_preserves_historical_launch_reservations(): void
    {
        $migration = require database_path('migrations/2026_10_30_200000_add_crm_campaign_smtp_limits.php');
        $migration->down();
        $first = $this->bulkCampaign(1);
        $second = $this->bulkCampaign(1);
        $first->update(['status' => 'completed', 'queued_at' => now('UTC'), 'completed_at' => now('UTC')]);
        $first->delete();
        $second->update(['status' => 'scheduled', 'scheduled_at' => now('UTC')->addDay()]);
        $migration->up();
        $this->assertSame(1, DB::table('crm_campaign_daily_launches')->count());
        $this->assertSame(2, CrmCampaign::withTrashed()->count());
        $third = $this->bulkCampaign(1);
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $this->queue($third);
    }

    public function test_wizard_daily_rejection_rolls_back_composition_and_schedule(): void
    {
        $first = $this->bulkCampaign(1);
        $second = $this->bulkCampaign(1);
        $this->actingAs($this->manager, 'manager');
        $this->queue($first);
        $original = $second->content;
        $this->postJson(route('manager.campaigns.send', $second), [
            'wizard_final' => 1, 'confirm' => 1, 'revision' => 0,
            'content' => 'Replacement content', 'schedule_enabled' => 1,
            'scheduled_date' => now('UTC')->addDays(2)->toDateString(), 'scheduled_time' => '12:00',
        ])->assertUnprocessable()->assertJsonValidationErrors('campaign');
        $this->assertSame('draft', $second->fresh()->status);
        $this->assertSame($original, $second->fresh()->content);
        $this->assertNull($second->fresh()->scheduled_at);
        $this->assertSame(0, (int) $second->fresh()->revision);
        $this->assertSame('selected', $second->recipients()->sole()->status);
        $this->assertSame(1, DB::table('crm_campaign_daily_launches')->count());
    }

    public function test_invalid_confirmation_does_not_consume_daily_allowance(): void
    {
        $first = $this->bulkCampaign(1);
        $first->update(['content' => '']);
        try {
            $this->queue($first);
            $this->fail('Empty content must be rejected.');
        } catch (\Illuminate\Validation\ValidationException $error) {
            $this->assertArrayHasKey('content', $error->errors());
        }
        $this->assertSame(0, DB::table('crm_campaign_daily_launches')->count());
        $second = $this->bulkCampaign(1);
        $this->queue($second);
        $this->assertSame('queued', $second->fresh()->status);
    }
}
