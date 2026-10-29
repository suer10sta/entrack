<?php

namespace Tests\Feature;

use App\Enums\ProspectStatus;
use App\Mail\ProspectCampaignMail;
use App\Models\CrmCampaign;
use App\Models\CrmCampaignLink;
use App\Models\CrmCampaignRecipient;
use App\Models\Manager;
use App\Models\Prospect;
use App\Models\User;
use App\Services\CrmCampaignAudience;
use App\Services\CrmCampaignConsent;
use App\Services\CrmCampaignSender;
use App\Services\CrmCampaignService;
use App\Services\CrmCampaignUnsubscribe;
use App\Services\ProspectStatusService;
use DomainException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

class CrmCampaignUnsubscribeTest extends TestCase
{
    private ?string $schema = null;

    private Manager $manager;

    private $renderer;

    private array $delivered = [];

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'pgsql', 'cache.default' => 'array', 'session.driver' => 'array']);
        $this->guardAgainstNonTestingDatabase();
        $this->schema = 'crm_unsubscribe_test_'.bin2hex(random_bytes(8));
        DB::statement('CREATE SCHEMA "'.$this->schema.'"');
        DB::statement('SET search_path TO "'.$this->schema.'"');
        foreach (['managers', 'entreprises', 'users'] as $name) {
            Schema::create($name, function ($table) use ($name) {
                $table->id();
                $table->string('name');
                $table->string('email')->nullable();
                if ($name === 'users') {
                    $table->unsignedBigInteger('entreprise_id')->nullable();
                }
                if ($name === 'entreprises') {
                    $table->string('type_compt')->nullable();
                    $table->timestampTz('trial_ends_at')->nullable();
                }
                $table->timestamps();
            });
        }
        foreach (glob(database_path('migrations/2026_10_27_00000*_*.php')) as $path) {
            (require $path)->up();
        }
        foreach (['2026_10_27_000100_create_crm_campaigns.php', '2026_10_27_000102_add_campaign_composition_fields.php',
            '2026_10_27_000103_add_campaign_media.php', '2026_10_27_000104_add_campaign_tracking.php',
            '2026_10_28_000000_add_campaign_scheduling.php', '2026_10_28_000001_add_campaign_soft_deletes.php',
            '2026_10_28_000002_add_campaign_user_audiences.php', '2026_10_29_200000_add_crm_campaign_opt_out.php'] as $file) {
            (require database_path('migrations/'.$file))->up();
        }
        Schema::create('subscriptions', function ($table) {
            $table->id();
            $table->foreignId('entreprise_id');
            $table->string('stripe_status');
            $table->string('type')->default('default');
            $table->timestampTz('ends_at')->nullable();
            $table->timestamps();
        });
        config(['mail.default' => 'smtp', 'crm.campaign_interval_ms' => 0, 'crm.campaign_batch_size' => 100]);
        $this->renderer = Mail::mailer('array');
        $this->manager = Manager::create(['name' => 'CRM Manager', 'email' => 'manager@example.test']);
        $this->withoutVite();
        Http::preventStrayRequests();
    }

    protected function tearDown(): void
    {
        try {
            if ($this->schema) {
                DB::statement('SET search_path TO public');
                DB::statement('DROP SCHEMA "'.$this->schema.'" CASCADE');
            }
        } finally {
            parent::tearDown();
        }
    }

    private function recipient(?Prospect $prospect = null): CrmCampaignRecipient
    {
        $prospect ??= Prospect::create(['name' => 'Prospect', 'email' => 'person'.bin2hex(random_bytes(4)).'@example.test']);
        $campaign = CrmCampaign::create(['name' => 'Campaign', 'subject' => 'DalyTrac', 'content' => 'Discover DalyTrac', 'manager_id' => $this->manager->id]);

        return $campaign->recipients()->create(['prospect_id' => $prospect->id, 'name' => $prospect->name,
            'email' => $prospect->email, 'email_normalized' => $prospect->email_normalized]);
    }

    private function url(CrmCampaignRecipient $recipient, string $locale = 'fr'): string
    {
        return app(CrmCampaignUnsubscribe::class)->url($recipient, $locale);
    }

    private function token(string $url): string
    {
        return basename(parse_url($url, PHP_URL_PATH));
    }

    private function stubSender(): void
    {
        Mail::shouldReceive('mailer')->andReturn($this->renderer);
        Mail::shouldReceive('to')->andReturnUsing(function ($address) {
            $this->delivered[] = $address;
            $pending = Mockery::mock();
            $pending->shouldReceive('send')->once()->with(Mockery::type(ProspectCampaignMail::class))->andReturn((object) ['accepted' => true]);

            return $pending;
        });
    }

    public function test_both_email_parts_have_personal_footer_link_outside_marketing_tracking(): void
    {
        $recipient = $this->recipient();
        $other = $this->recipient();
        $mail = new ProspectCampaignMail('Campaign', 'Read https://example.test/product', trackingRecipient: $recipient);
        $html = $mail->render();
        $plain = view('emails.prospect-campaign-text', get_object_vars($mail))->render();
        $url = $mail->unsubscribeUrl;
        $this->assertNotNull($url);
        $this->assertStringContainsString('Se désabonner', $html);
        $this->assertStringContainsString($url, $plain);
        $this->assertGreaterThan(strpos($html, 'Simplifiez'), strpos($html, 'Se désabonner'));
        $this->assertGreaterThan(strpos($plain, 'Simplifiez'), strpos($plain, 'Se désabonner'));
        $this->assertNotSame($url, $this->url($other));
        $this->assertStringNotContainsString($recipient->email, $url);
        $this->assertStringNotContainsString($recipient->prospect->public_token, $url);
        $this->assertStringNotContainsString('prospect_id', $url);
        $this->assertStringNotContainsString('/crm/t/', $url);
        $this->assertSame(1, CrmCampaignLink::count());
        $this->assertSame(0, CrmCampaignLink::where('destination', $url)->count());
        $mail->render();
        $this->assertSame($url, $mail->unsubscribeUrl);
    }

    public function test_scanner_get_does_not_change_consent_status_timeline_or_click_counts(): void
    {
        $recipient = $this->recipient();
        $before = $recipient->prospect->toArray();
        $this->get($this->url($recipient))->assertOk()->assertSee('Se désabonner')
            ->assertSee($recipient->email)->assertSee('Continuer à recevoir les emails')
            ->assertHeader('Referrer-Policy', 'no-referrer');
        $this->assertSame($before, $recipient->prospect->fresh()->toArray());
        $this->assertSame(0, DB::table('prospect_activities')->count());
        $this->assertSame(0, DB::table('prospect_status_history')->count());
        $this->assertSame(0, $recipient->fresh()->click_count);
    }

    public function test_post_uses_status_service_and_creates_one_explicit_automatic_timeline_entry(): void
    {
        $recipient = $this->recipient();
        $service = Mockery::mock(ProspectStatusService::class)->makePartial();
        $service->shouldReceive('unsubscribeFromCampaigns')->once()->passthru();
        $this->app->instance(ProspectStatusService::class, $service);
        $this->post($this->url($recipient))->assertOk()->assertSee('Vous êtes désabonné(e)');
        $prospect = $recipient->prospect->fresh();
        $this->assertNotNull($prospect->unsubscribed_at);
        $this->assertSame(ProspectStatus::Perdu, $prospect->status);
        $history = $prospect->statusHistories()->sole();
        $this->assertSame('campaign_unsubscribe', $history->source);
        $this->assertNull($history->manager_id);
        $this->assertSame('Prospect désabonné des campagnes email', $history->note);
        $activity = $prospect->activities()->sole();
        $this->assertSame('Prospect désabonné des campagnes email', $activity->subject);
        $this->assertSame('campaign_unsubscribe', $activity->metadata['event']);
        $this->actingAs($this->manager, 'manager')->get(route('manager.prospects.show', $prospect))
            ->assertOk()->assertSee('Prospect désabonné des campagnes email')->assertSee('Désabonnement campagne')
            ->assertViewHas('timeline', fn ($timeline) => $timeline->total() === 1);
    }

    public function test_repeated_confirmation_and_later_link_reuse_do_not_duplicate_or_reapply_status(): void
    {
        $recipient = $this->recipient();
        $url = $this->url($recipient);
        $this->post($url)->assertOk();
        $at = $recipient->prospect->fresh()->unsubscribed_at;
        $this->post($url)->assertOk();
        $this->get($url)->assertOk()->assertSee('Vous êtes désabonné(e)')
            ->assertDontSee('Continuer à recevoir les emails')->assertSee('Vous ne recevrez plus les communications marketing de DalyTrac.');
        $this->assertSame(1, $recipient->prospect->activities()->count());
        $this->assertSame(1, $recipient->prospect->statusHistories()->count());
        app(ProspectStatusService::class)->transition($recipient->prospect, ProspectStatus::Engage, manager: $this->manager);
        $this->post($url)->assertOk();
        $this->assertSame(ProspectStatus::Engage, $recipient->prospect->fresh()->status);
        $this->assertEquals($at, $recipient->prospect->fresh()->unsubscribed_at);
        $this->assertSame(1, $recipient->prospect->activities()->count());
        $this->assertTrue(app(CrmCampaignConsent::class)->blocked($recipient->prospect_id, null, $recipient->email_normalized));
    }

    public function test_already_lost_prospect_gets_consent_without_another_status_transition(): void
    {
        $recipient = $this->recipient();
        app(ProspectStatusService::class)->transition($recipient->prospect, ProspectStatus::Perdu, manager: $this->manager);
        $this->post($this->url($recipient))->assertOk();
        $this->assertSame(1, $recipient->prospect->statusHistories()->count());
        $this->assertSame(1, $recipient->prospect->activities()->count());
        $this->assertNotNull($recipient->prospect->fresh()->unsubscribed_at);
        $this->actingAs($this->manager, 'manager')->get(route('manager.prospects.show', $recipient->prospect))
            ->assertOk()->assertSee('Désabonnement campagne');
    }

    public function test_unsubscribe_exception_does_not_allow_general_automatic_loss(): void
    {
        $recipient = $this->recipient();
        foreach (['campaign', 'stripe', 'campaign_unsubscribe'] as $source) {
            try {
                app(ProspectStatusService::class)->transition($recipient->prospect, ProspectStatus::Perdu, $source);
                $this->fail('General automatic loss must stay forbidden.');
            } catch (DomainException) {
                $this->assertSame(ProspectStatus::Nouveau, $recipient->prospect->fresh()->status);
            }
        }
    }

    public function test_invalid_tampered_missing_and_mismatched_targets_never_change_another_prospect(): void
    {
        $first = $this->recipient();
        $second = $this->recipient();
        $token = $this->token($this->url($first));
        $this->get(route('crm.unsubscribe', ['locale' => 'fr', 'token' => 'invalid']))->assertNotFound();
        $tampered = ($token[0] === 'a' ? 'b' : 'a').substr($token, 1);
        $this->post(route('crm.unsubscribe', ['locale' => 'fr', 'token' => $tampered]))->assertNotFound();
        foreach ([999999, $second->prospect_id] as $id) {
            $payload = ['purpose' => 'crm_campaign_unsubscribe_v1', 'recipient_id' => $first->id,
                'prospect_id' => $id, 'prospect_token' => $second->prospect->public_token,
                'email_hash' => hash('sha256', $first->email_normalized)];
            $bad = rtrim(strtr(Crypt::encryptString(json_encode($payload)), '+/', '-_'), '=');
            $this->post(route('crm.unsubscribe', ['locale' => 'fr', 'token' => $bad]))->assertNotFound();
        }
        $this->post($this->url($first), ['prospect_id' => $second->prospect_id, 'email' => $second->email])->assertOk();
        $this->assertNull($second->prospect->fresh()->unsubscribed_at);
        $this->assertSame(ProspectStatus::Nouveau, $second->prospect->fresh()->status);
    }

    public function test_missing_recipient_is_a_localized_invalid_link(): void
    {
        $recipient = $this->recipient();
        $url = $this->url($recipient, 'en');
        $recipient->delete();
        $this->get($url)->assertNotFound()->assertSee('Invalid link');
    }

    public function test_new_audiences_and_custom_selection_exclude_opt_out_but_not_ordinary_lost_prospects(): void
    {
        $recipient = $this->recipient();
        $this->post($this->url($recipient))->assertOk();
        $lost = $this->recipient();
        app(ProspectStatusService::class)->transition($lost->prospect, ProspectStatus::Perdu, manager: $this->manager);
        $campaign = $this->recipient()->campaign;
        $campaign->recipients()->delete();
        $result = app(CrmCampaignService::class)->select($campaign, ['mode' => 'page',
            'visible_ids' => [$recipient->prospect_id, $lost->prospect_id], 'selected_ids' => [$recipient->prospect_id, $lost->prospect_id]]);
        $this->assertSame(1, $result['added']);
        $this->assertSame([$lost->prospect_id], $campaign->recipients()->pluck('prospect_id')->all());
        $audience = app(CrmCampaignAudience::class)->recipients('prospects');
        $this->assertNotContains($recipient->prospect_id, array_column($audience, 'prospect_id'));
        $this->assertContains($lost->prospect_id, array_column($audience, 'prospect_id'));
    }

    public function test_already_prepared_and_scheduled_campaigns_recheck_consent_at_send_time(): void
    {
        $first = $this->recipient();
        $pending = $this->recipient($first->prospect);
        $scheduled = $this->recipient($first->prospect);
        app(CrmCampaignService::class)->enqueue($pending->campaign);
        $scheduled->campaign->update(['scheduled_at' => now('UTC')->addDay()]);
        app(CrmCampaignService::class)->enqueue($scheduled->campaign->fresh());
        $this->post($this->url($first))->assertOk();
        $this->stubSender();
        $this->assertSame(0, app(CrmCampaignSender::class)->batch());
        $this->assertSame('skipped', $pending->fresh()->status);
        $this->travel(2)->days();
        $this->assertSame(0, app(CrmCampaignSender::class)->batch());
        $this->assertSame('skipped', $scheduled->fresh()->status);
        $this->assertSame([], $this->delivered);
    }

    public function test_user_audiences_and_duplicate_email_prospects_cannot_bypass_opt_out(): void
    {
        $recipient = $this->recipient();
        $user = User::create(['name' => 'Trial', 'email' => $recipient->email]);
        $company = DB::table('entreprises')->insertGetId(['name' => 'Trial company', 'trial_ends_at' => now('UTC')->addDays(10)->toISOString()]);
        $user->update(['entreprise_id' => $company]);
        $recipient->prospect->update(['linked_user_id' => $user->id]);
        $other = $this->recipient();
        $other->prospect->update(['email' => $recipient->email]);
        $accountRecipient = $other->campaign->recipients()->create(['user_id' => $user->id, 'prospect_id' => null,
            'name' => $user->name, 'email' => $user->email, 'email_normalized' => $user->email]);
        $this->assertNotNull(app(CrmCampaignUnsubscribe::class)->url($accountRecipient));
        $this->post($this->url($recipient))->assertOk();
        $this->assertSame([], app(CrmCampaignAudience::class)->recipients('trials'));
        $this->assertTrue(app(CrmCampaignConsent::class)->blocked($other->prospect_id, null, $recipient->email_normalized));
        app(CrmCampaignService::class)->enqueue($other->campaign);
        $this->stubSender();
        $this->assertSame(0, app(CrmCampaignSender::class)->batch());
        $this->assertSame('skipped', $accountRecipient->fresh()->status);
        $user->update(['email' => 'different@example.test']);
        $this->assertTrue(app(CrmCampaignConsent::class)->blocked(null, $user->id, 'different@example.test'));
    }

    public function test_final_send_check_catches_unsubscribe_after_recipient_was_claimed(): void
    {
        $recipient = $this->recipient();
        app(CrmCampaignService::class)->enqueue($recipient->campaign);
        $unsubscribe = app(CrmCampaignUnsubscribe::class);
        $token = $this->token($this->url($recipient));
        $gate = Mockery::mock(CrmCampaignConsent::class)->makePartial();
        $gate->shouldReceive('withRecipient')->once()->andReturnUsing(function ($record, $work) use ($unsubscribe, $token) {
            $this->assertSame('sending', $record->fresh()->status);
            $unsubscribe->confirm($token);

            return $work();
        });
        $this->app->instance(CrmCampaignConsent::class, $gate);
        $this->stubSender();
        $this->assertSame(0, app(CrmCampaignSender::class)->batch());
        $this->assertSame([], $this->delivered);
        $this->assertSame('skipped', $recipient->fresh()->status);
    }

    public function test_unsubscribe_get_and_post_do_not_count_as_marketing_clicks(): void
    {
        $recipient = $this->recipient();
        $recipient->update(['status' => 'sent', 'sent_at' => now('UTC')]);
        $mail = new ProspectCampaignMail('Campaign', 'Read https://example.test/product', trackingRecipient: $recipient);
        $mail->render();
        $this->get($mail->unsubscribeUrl)->assertOk();
        $this->post($mail->unsubscribeUrl)->assertOk();
        $this->assertSame(0, $recipient->fresh()->click_count);
        $this->assertNull($recipient->fresh()->first_clicked_at);
        $this->assertSame(0, CrmCampaignLink::sum('click_count'));
    }

    public function test_archived_prospect_can_still_record_opt_out_and_status_history(): void
    {
        $recipient = $this->recipient();
        $url = $this->url($recipient);
        $recipient->prospect->delete();
        $this->post($url)->assertOk();
        $prospect = Prospect::withTrashed()->findOrFail($recipient->prospect_id);
        $this->assertTrue($prospect->trashed());
        $this->assertNotNull($prospect->unsubscribed_at);
        $this->assertSame(ProspectStatus::Perdu, $prospect->status);
    }

    public function test_four_locales_render_confirmation_success_and_invalid_link_without_login(): void
    {
        foreach (['fr', 'en', 'nl', 'es'] as $locale) {
            $recipient = $this->recipient();
            $this->get($this->url($recipient, $locale))->assertOk()->assertSee(__('crm-unsubscribe.confirm_button', [], $locale));
            $this->post($this->url($recipient, $locale))->assertOk()->assertSee(__('crm-unsubscribe.done_title', [], $locale));
            $this->get(route('crm.unsubscribe', ['locale' => $locale, 'token' => 'invalid']))->assertNotFound()->assertSee(__('crm-unsubscribe.invalid_title', [], $locale));
        }
    }

    public function test_public_post_keeps_csrf_protection(): void
    {
        $recipient = $this->recipient();
        $this->app->bind(\App\Http\Middleware\VerifyCsrfToken::class, function ($app) {
            return new class($app, $app['encrypter']) extends \App\Http\Middleware\VerifyCsrfToken
            {
                protected function runningUnitTests()
                {
                    return false;
                }
            };
        });
        $this->post($this->url($recipient))->assertStatus(419);
        $this->assertNull($recipient->prospect->fresh()->unsubscribed_at);
    }

    public function test_consent_timestamp_preserves_utc_instant_in_non_utc_database_session(): void
    {
        $recipient = $this->recipient();
        DB::statement("SET TIME ZONE 'Europe/Paris'");
        try {
            $before = now('UTC')->timestamp;
            $this->post($this->url($recipient))->assertOk();
            $this->assertEqualsWithDelta($before, $recipient->prospect->fresh()->unsubscribed_at->timestamp, 2);
        } finally {
            DB::statement('RESET TIME ZONE');
        }
    }

    public function test_unsubscribe_is_the_specific_loss_exception_for_every_current_prospect_status(): void
    {
        foreach (ProspectStatus::cases() as $status) {
            $recipient = $this->recipient();
            app(ProspectStatusService::class)->transition($recipient->prospect, $status, manager: $this->manager);
            $this->post($this->url($recipient))->assertOk();
            $this->assertSame(ProspectStatus::Perdu, $recipient->prospect->fresh()->status);
            $this->assertNotNull($recipient->prospect->fresh()->unsubscribed_at);
        }
    }

    public function test_user_only_recipient_link_targets_its_known_prospect_without_creating_crm_records(): void
    {
        $original = $this->recipient();
        $user = User::create(['name' => 'Known account', 'email' => $original->email]);
        $original->prospect->update(['linked_user_id' => $user->id]);
        $recipient = $this->recipient()->campaign->recipients()->create(['user_id' => $user->id, 'prospect_id' => null,
            'name' => $user->name, 'email' => $user->email, 'email_normalized' => $user->email]);
        $count = Prospect::count();
        $this->post($this->url($recipient))->assertOk();
        $this->assertNotNull($original->prospect->fresh()->unsubscribed_at);
        $this->assertSame($count, Prospect::count());
    }

    public function test_preference_status_and_timeline_are_rolled_back_together_on_failure(): void
    {
        $recipient = $this->recipient();
        DB::unprepared("CREATE FUNCTION fail_unsubscribe_activity() RETURNS trigger LANGUAGE plpgsql AS $$ BEGIN RAISE EXCEPTION 'Test-only failure'; END; $$;
            CREATE TRIGGER fail_activity BEFORE INSERT ON prospect_activities FOR EACH ROW EXECUTE FUNCTION fail_unsubscribe_activity();");
        try {
            app(CrmCampaignUnsubscribe::class)->confirm($this->token($this->url($recipient)));
            $this->fail('The transaction should fail.');
        } catch (\Illuminate\Database\QueryException) {
            $this->assertNull($recipient->prospect->fresh()->unsubscribed_at);
            $this->assertSame(ProspectStatus::Nouveau, $recipient->prospect->fresh()->status);
            $this->assertSame(0, DB::table('prospect_status_history')->count());
            $this->assertSame(0, DB::table('crm_lifecycle_events')->count());
        }
    }

    public function test_two_processes_confirm_once_and_post_waits_for_existing_dispatch_gate(): void
    {
        $recipient = $this->recipient();
        $token = $this->token($this->url($recipient));
        $workers = [];
        try {
            $hold = $this->worker('hold', $token);
            $workers[] = $hold;
            $this->assertSame("locked\n", fgets($hold[1][1]));
            $start = microtime(true);
            app(CrmCampaignUnsubscribe::class)->confirm($token);
            $this->assertGreaterThan(0.3, microtime(true) - $start);
            $this->finishWorker($hold);
            $workers = [];
            $other = $this->recipient();
            $token = $this->token($this->url($other));
            $workers[] = $this->worker('confirm', $token);
            $workers[] = $this->worker('confirm', $token);
            foreach ($workers as $worker) {
                $this->finishWorker($worker);
            }
            $workers = [];
            $this->assertSame(1, $other->prospect->activities()->count());
            $this->assertSame(1, $other->prospect->statusHistories()->count());
        } finally {
            foreach ($workers as [$process, $pipes]) {
                if (is_resource($process)) {
                    proc_terminate($process);
                    foreach ($pipes as $pipe) {
                        if (is_resource($pipe)) {
                            fclose($pipe);
                        }
                    }
                    proc_close($process);
                }
            }
        }
    }

    private function worker(string $mode, string $token): array
    {
        $process = proc_open([PHP_BINARY, base_path('tests/Support/crm-unsubscribe-worker.php'), $this->schema,
            config('database.connections.pgsql.database'), $mode, $token],
            [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, base_path());
        $this->assertIsResource($process);
        fclose($pipes[0]);

        return [$process, $pipes];
    }

    private function finishWorker(array $worker): void
    {
        [$process, $pipes] = $worker;
        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $this->assertSame(0, proc_close($process), $stderr);
        $this->assertSame('ok', $stdout);
    }
}
