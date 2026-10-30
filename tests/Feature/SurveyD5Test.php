<?php

namespace Tests\Feature;

use App\Enums\ContactPriority;
use App\Enums\ContactSource;
use App\Enums\SurveyActionType;
use App\Enums\SurveyStatus;
use App\Enums\SurveyType;
use App\Mail\D5SurveyMail;
use App\Models\Contact;
use App\Models\Entreprise;
use App\Models\Manager;
use App\Models\SurveyAnswer;
use App\Models\SurveyResponse;
use App\Models\User;
use App\Services\ContactManagementService;
use App\Services\Surveys\D5ActionService;
use App\Services\Surveys\D5Eligibility;
use App\Services\Surveys\D5Lifecycle;
use App\Services\Surveys\SurveyCatalog;
use App\Services\Surveys\SurveyLink;
use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Tests\TestCase;

class SurveyD5Test extends TestCase
{
    private ?string $schema = null;

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'pgsql', 'mail.default' => 'array']);
        $this->guardAgainstNonTestingDatabase();
        $this->schema = 'survey_test_'.bin2hex(random_bytes(8));
        DB::statement('CREATE SCHEMA "'.$this->schema.'"');
        DB::statement('SET search_path TO "'.$this->schema.'"');
        foreach (['entreprises', 'users', 'managers', 'subscriptions'] as $name) {
            Schema::create($name, function ($table) use ($name) {
                $table->id();
                $table->string('name')->nullable();
                $table->string('email')->nullable();
                if ($name === 'entreprises') {
                    $table->string('type_compt')->nullable();
                    $table->string('stripe_id')->nullable();
                    $table->string('account_status')->nullable();
                    $table->boolean('auto_renew')->default(true);
                    $table->unsignedInteger('failed_payment_count')->default(0);
                    $table->timestamp('last_payment_failed_at')->nullable();
                    $table->timestamp('trial_ends_at')->nullable();
                    $table->string('invoice_prefix')->default('F');
                    $table->string('invoice_number_format')->default('year_month_number');
                }
                if ($name === 'users') {
                    $table->foreignId('entreprise_id')->constrained('entreprises');
                    $table->timestamp('email_verified_at')->nullable();
                    $table->string('locale')->nullable();
                }
                if ($name === 'subscriptions') {
                    $table->unsignedBigInteger('entreprise_id');
                    $table->string('type')->default('default');
                    $table->string('stripe_status');
                    $table->timestampTz('ends_at')->nullable();
                }
                $table->timestamps();
            });
        }
        foreach (['projects', 'employees', 'clients', 'tasks_timer'] as $name) {
            Schema::create($name, function ($table) use ($name) {
                $table->id();
                if ($name === 'projects') {
                    $table->string('entreprise_id');
                } else {
                    $table->unsignedBigInteger('entreprise_id');
                }
                if ($name === 'employees') {
                    $table->unsignedBigInteger('user_id')->nullable();
                }
                $table->timestamps();
            });
        }
        foreach (glob(database_path('migrations/2026_10_29_00000*_*.php')) as $path) {
            (require $path)->up();
        }
        foreach (glob(database_path('migrations/2026_10_29_10000*_*.php')) as $path) {
            (require $path)->up();
        }
        (require database_path('migrations/2026_10_30_100004_add_d3_signal_action_types.php'))->up();
        (require database_path('migrations/2026_10_30_100005_add_d4_signal_action_types.php'))->up();
        (require database_path('migrations/2026_10_30_100006_add_first_paid_at_to_entreprises.php'))->up();
        (require database_path('migrations/2026_10_30_100007_add_d5_signal_action_types.php'))->up();
        Carbon::setTestNow(Carbon::parse('2026-10-29 12:00:00', 'UTC'));
        Mail::fake();
        Notification::fake();
    }

    protected function tearDown(): void
    {
        try {
            Carbon::setTestNow();
            if ($this->schema) {
                DB::statement('SET search_path TO public');
                DB::statement('DROP SCHEMA "'.$this->schema.'" CASCADE');
            }
        } finally {
            parent::tearDown();
        }
    }

    private function owner(string $locale = 'fr', bool $paid = true): User
    {
        $company = Entreprise::create(['name' => 'D5 company', 'trial_ends_at' => now('UTC')->subHours(24)]);
        $company->forceFill(['created_at' => now('UTC')->subYears(2)])->save();
        $user = User::create(['name' => 'Trial owner', 'email' => 'owner'.$company->id.'@example.test',
            'entreprise_id' => $company->id, 'locale' => $locale]);
        $user->forceFill(['created_at' => now('UTC')->subDays(3), 'email_verified_at' => now('UTC')->subDay()])->save();

        $company->forceFill(['stripe_id' => 'cus_'.$company->id])->save();
        if ($paid) {
            DB::table('entreprises')->where('id', $company->id)->update(['first_paid_at' => now('UTC')->subDays(7)->toIso8601String(), 'first_paid_invoice_id' => 'in_first_'.$company->id, 'type_compt' => 'solo']);
        }

        return $user;
    }

    private function sent(string $locale = 'fr'): SurveyResponse
    {
        $owner = $this->owner($locale);
        $this->assertSame('sent', app(D5Lifecycle::class)->process($owner->entreprise_id));

        return SurveyResponse::where('entreprise_id', $owner->entreprise_id)->where('survey_type', SurveyType::D5)->sole();
    }

    private function link(SurveyResponse $survey, string $choice = 'Le gain de temps', string $locale = 'fr'): string
    {
        return app(SurveyLink::class)->url($survey, $locale, $choice);
    }

    private function finish(SurveyResponse $survey, array $answers): void
    {
        $this->post($this->link($survey), ['intent' => 'complete', 'answers' => $answers])->assertOk();
    }

    private function invoice(User $owner, array $overrides = []): array
    {
        return array_replace_recursive(['id' => 'in_paid_'.$owner->entreprise_id, 'object' => 'invoice', 'customer' => 'cus_'.$owner->entreprise_id, 'subscription' => 'sub_first', 'status' => 'paid', 'paid' => true, 'amount_paid' => 2900, 'billing_reason' => 'subscription_create', 'status_transitions' => ['paid_at' => now('UTC')->subDays(7)->timestamp]], $overrides);
    }

    private function stripeInvoices(array $invoices, int $calls = 1, bool $fail = false)
    {
        $service = \Mockery::mock();
        $expect = $service->shouldReceive('all')->times($calls)->with(\Mockery::on(fn ($args) => $args['status'] === 'paid' && $args['limit'] === 100 && isset($args['customer'])));
        if ($fail) {
            $expect->andThrow(new \RuntimeException('Simulated Stripe failure'));
        } else {
            $expect->andReturn(\Stripe\Collection::constructFrom(['object' => 'list', 'data' => $invoices, 'has_more' => false]));
        }
        $client = \Mockery::mock(\Stripe\StripeClient::class);
        $client->shouldReceive('getService')->with('invoices')->andReturn($service);
        $this->app->bind(\Stripe\StripeClient::class, fn () => $client);

        return $client;
    }

    public function test_no_confirmed_payment_means_no_d5_even_with_an_active_plan_or_old_company(): void
    {
        $owner = $this->owner('fr', false);
        $owner->entreprise->update(['type_compt' => 'solo']);
        $this->assertSame('ineligible', app(D5Lifecycle::class)->process($owner->entreprise_id));
        $this->assertSame(0, SurveyResponse::count());
        Mail::assertNothingSent();
    }

    public function test_real_paid_invoice_time_is_persisted_once_not_invoice_creation_or_current_time(): void
    {
        $owner = $this->owner('fr', false);
        $invoice = $this->invoice($owner, ['created' => now('UTC')->subDays(10)->timestamp]);
        $this->stripeInvoices([$invoice]);
        app(\App\Services\Surveys\FirstSubscriptionPayment::class)->fromInvoice($owner->entreprise, $invoice);
        $company = $owner->entreprise->fresh();
        $this->assertSame(now('UTC')->subDays(7)->toISOString(), $company->first_paid_at->toISOString());
        $this->assertSame($invoice['id'], $company->first_paid_invoice_id);
        $this->travel(2)->days();
        app(\App\Services\Surveys\FirstSubscriptionPayment::class)->fromInvoice($company, $this->invoice($owner, ['id' => 'in_renewal', 'billing_reason' => 'subscription_cycle', 'status_transitions' => ['paid_at' => now('UTC')->timestamp]]));
        $this->assertSame($company->first_paid_at->toISOString(), $company->fresh()->first_paid_at->toISOString());
    }

    public function test_history_scan_selects_first_real_positive_payment_across_previous_subscriptions(): void
    {
        $owner = $this->owner('fr', false);
        $current = $this->invoice($owner, ['id' => 'in_current', 'billing_reason' => 'subscription_cycle', 'status_transitions' => ['paid_at' => now('UTC')->timestamp]]);
        $old = $this->invoice($owner, ['id' => 'in_original', 'subscription' => 'sub_old', 'status_transitions' => ['paid_at' => now('UTC')->subDays(90)->timestamp]]);
        $this->stripeInvoices([$current, $this->invoice($owner, ['id' => 'in_zero', 'amount_paid' => 0, 'status_transitions' => ['paid_at' => now('UTC')->subDays(120)->timestamp]]), $old]);
        app(\App\Services\Surveys\FirstSubscriptionPayment::class)->fromInvoice($owner->entreprise, $current);
        $this->assertSame('in_original', $owner->entreprise->fresh()->first_paid_invoice_id);
        $this->assertSame(now('UTC')->subDays(90)->toISOString(), $owner->entreprise->fresh()->first_paid_at->toISOString());
    }

    public function test_failed_unpaid_zero_non_subscription_and_missing_actual_timestamp_never_create_proof(): void
    {
        $owner = $this->owner('fr', false);
        $this->stripeInvoices([], 0);
        foreach ([['status' => 'open'], ['amount_paid' => 0], ['subscription' => null], ['customer' => 'cus_other'], ['status_transitions' => ['paid_at' => null]], ['status_transitions' => ['paid_at' => now('UTC')->addDay()->timestamp]]] as $bad) {
            app(\App\Services\Surveys\FirstSubscriptionPayment::class)->fromInvoice($owner->entreprise, $this->invoice($owner, $bad));
        }
        $this->assertNull($owner->entreprise->fresh()->first_paid_at);
    }

    public function test_history_lookup_failure_does_not_guess_date_and_can_retry_on_confirmed_event(): void
    {
        $owner = $this->owner('fr', false);
        $invoice = $this->invoice($owner);
        $this->stripeInvoices([], 1, true);
        app(\App\Services\Surveys\FirstSubscriptionPayment::class)->fromInvoice($owner->entreprise, $invoice);
        $this->assertNull($owner->entreprise->fresh()->first_paid_at);
        $this->stripeInvoices([$invoice]);
        app(\App\Services\Surveys\FirstSubscriptionPayment::class)->fromInvoice($owner->entreprise, $invoice);
        $this->assertNotNull($owner->entreprise->fresh()->first_paid_at);
    }

    public function test_database_enforces_immutability_and_proof_pair(): void
    {
        $owner = $this->owner();
        $at = $owner->entreprise->fresh()->first_paid_at;
        try {
            DB::transaction(fn () => DB::table('entreprises')->where('id', $owner->entreprise_id)->update(['first_paid_at' => now('UTC')]));
            $this->fail('Immutable field must reject replacement');
        } catch (\Illuminate\Database\QueryException $e) {
            $this->assertSame('23514', $e->errorInfo[0]);
        }
        $this->assertTrue($owner->entreprise->fresh()->first_paid_at->equalTo($at));
        $unpaid = $this->owner('fr', false);
        try {
            DB::transaction(fn () => DB::table('entreprises')->where('id', $unpaid->entreprise_id)->update(['first_paid_at' => now('UTC')]));
            $this->fail('Missing proof should fail');
        } catch (\Illuminate\Database\QueryException $e) {
            $this->assertSame('23514', $e->errorInfo[0]);
        }
    }

    public function test_before_j7_then_exact_j7_sends_once_without_stripe_lookup(): void
    {
        $owner = $this->owner();
        $this->travel(-1)->seconds();
        $this->stripeInvoices([], 0);
        $this->assertSame('ineligible', app(D5Lifecycle::class)->process($owner->entreprise_id));
        $this->travel(1)->seconds();
        $this->assertSame('sent', app(D5Lifecycle::class)->process($owner->entreprise_id));
        $this->assertSame('waiting', app(D5Lifecycle::class)->process($owner->entreprise_id));
        $this->assertSame('once', SurveyResponse::sole()->cycle_key);
        Mail::assertSentCount(1);
    }

    public function test_renewal_upgrade_downgrade_and_scheduler_replay_never_reset_first_purchase_cycle(): void
    {
        $survey = $this->sent();
        $company = $survey->entreprise;
        $first = $company->first_paid_at->toISOString();
        $this->stripeInvoices([], 0);
        foreach (['premium', 'pro', 'solo'] as $plan) {
            $company->update(['type_compt' => $plan]);
            app(\App\Services\Surveys\FirstSubscriptionPayment::class)->fromInvoice($company, $this->invoice($survey->user, ['id' => 'in_'.$plan]));
            $this->artisan('surveys:process-d5')->assertSuccessful();
            $this->assertSame($first, $company->fresh()->first_paid_at->toISOString());
        }
        $this->assertSame(1, SurveyResponse::count());
        Mail::assertSentCount(1);
    }

    public function test_rapid_cancellation_keeps_d5_pertinent_as_first_purchase_experience(): void
    {
        $owner = $this->owner();
        DB::table('subscriptions')->insert(['entreprise_id' => $owner->entreprise_id, 'stripe_status' => 'canceled', 'ends_at' => now('UTC')->subDay()]);
        $owner->entreprise->update(['type_compt' => null, 'account_status' => 'inactive']);
        $this->assertTrue(app(D5Eligibility::class)->eligible($owner->entreprise_id));
        $this->assertSame('sent', app(D5Lifecycle::class)->process($owner->entreprise_id));
        $this->travel(3)->days();
        $this->assertSame('reminded', app(D5Lifecycle::class)->process($owner->entreprise_id));
    }

    public function test_payment_immediately_neutralizes_trial_surveys_and_old_d4_link_without_actions(): void
    {
        $owner = $this->owner('fr', false);
        $trials = [];
        foreach ([SurveyType::D1, SurveyType::D2, SurveyType::D3, SurveyType::D4] as $type) {
            $trials[] = SurveyResponse::create(['entreprise_id' => $owner->entreprise_id, 'user_id' => $owner->id, 'email' => $owner->email, 'survey_type' => $type, 'survey_version' => 1, 'sent_at' => now('UTC')->subDay()]);
        }
        $d4 = end($trials);
        $url = app(SurveyLink::class)->url($d4, 'fr', 'Autre (texte libre)');
        $invoice = $this->invoice($owner, ['status_transitions' => ['paid_at' => now('UTC')->timestamp]]);
        $this->stripeInvoices([$invoice]);
        app(\App\Services\Surveys\FirstSubscriptionPayment::class)->fromInvoice($owner->entreprise, $invoice);
        foreach ($trials as $s) {
            $this->assertSame(SurveyStatus::Cancelled, $s->fresh()->status);
            $this->assertNotNull($s->fresh()->sent_at);
        }
        $this->get($url)->assertStatus(410);
        $this->post($url, ['answers' => ['q1' => 'La prise en main était trop difficile']])->assertStatus(410);
        $this->assertSame(0, Contact::count());
        $this->assertFalse(app(\App\Services\Surveys\D1Eligibility::class)->eligible($owner->entreprise_id));
        $this->assertFalse(app(\App\Services\Surveys\D2Eligibility::class)->eligible($owner->entreprise_id));
        $this->assertFalse(app(\App\Services\Surveys\D3Eligibility::class)->eligible($owner->entreprise_id));
        $this->assertFalse(app(\App\Services\Surveys\D4Eligibility::class)->eligible($owner->entreprise_id));
        $this->assertSame(0, SurveyResponse::where('survey_type', SurveyType::D5)->count());
    }

    public function test_completed_trial_history_is_untouched_and_payment_does_not_create_a_survey_early(): void
    {
        $owner = $this->owner('fr', false);
        $d4 = SurveyResponse::create(['entreprise_id' => $owner->entreprise_id, 'email' => $owner->email, 'survey_type' => SurveyType::D4, 'survey_version' => 1, 'status' => SurveyStatus::Answered, 'completed_at' => now('UTC')]);
        $d4->answers()->create(['question_id' => 'q1', 'value' => 'Autre (texte libre)']);
        $before = $d4->fresh()->toArray();
        $invoice = $this->invoice($owner, ['status_transitions' => ['paid_at' => now('UTC')->timestamp]]);
        $this->stripeInvoices([$invoice]);
        app(\App\Services\Surveys\FirstSubscriptionPayment::class)->fromInvoice($owner->entreprise, $invoice);
        $this->assertSame($before, $d4->fresh()->toArray());
        $this->assertSame(0, $d4->actions()->count());
        $this->assertSame('ineligible', app(D5Lifecycle::class)->process($owner->entreprise_id));
    }

    public function test_survey_stop_recipient_validation_and_global_72_hours_are_respected(): void
    {
        $owner = $this->owner();
        $owner->forceFill(['email_verified_at' => null])->save();
        $this->assertFalse(app(D5Eligibility::class)->eligible($owner->entreprise_id));
        $owner->forceFill(['email_verified_at' => now('UTC')])->save();
        $d3 = SurveyResponse::create(['entreprise_id' => $owner->entreprise_id, 'email' => $owner->email, 'survey_type' => SurveyType::D3, 'survey_version' => 1, 'sent_at' => now('UTC')->subDays(2)]);
        $this->assertSame('rate_limited', app(D5Lifecycle::class)->process($owner->entreprise_id));
        $this->travel(1)->days();
        $this->assertSame('sent', app(D5Lifecycle::class)->process($owner->entreprise_id));
        $other = $this->owner();
        $stop = SurveyResponse::create(['entreprise_id' => $other->entreprise_id, 'email' => $other->email, 'survey_type' => SurveyType::D1, 'survey_version' => 1]);
        $stop->answers()->create(['question_id' => 'q1', 'value' => 'abandon']);
        $this->assertSame('ineligible', app(D5Lifecycle::class)->process($other->entreprise_id));
    }

    public function test_email_has_seven_signed_choices_and_get_is_read_only(): void
    {
        $survey = $this->sent('en');
        Mail::assertSent(D5SurveyMail::class, function ($mail) use ($survey) {
            $html = $mail->render();
            $this->assertStringContainsString('first purchase', $html);
            preg_match_all('/href="([^"]+)"/', $html, $m);
            $this->assertCount(7, $m[1]);
            foreach ($m[1] as $url) {
                $r = \Illuminate\Http\Request::create(html_entity_decode($url));
                $this->assertTrue($r->hasValidSignature());
                $this->assertSame($survey->sent_at->addDays(15)->timestamp, (int) $r->query('expires'));
            }

            return true;
        });
        $before = $survey->fresh()->toArray();
        $actions = $survey->actions()->count();
        $this->get($this->link($survey))->assertOk();
        $this->assertSame($before, $survey->fresh()->toArray());
        $this->assertSame($actions, $survey->actions()->count());
        $this->assertSame(0, SurveyAnswer::count());
    }

    public function test_q1_is_confirmed_once_and_final_answers_are_unique(): void
    {
        $survey = $this->sent();
        $url = $this->link($survey);
        $this->post($url, ['intent' => 'confirm_q1', 'answers' => ['q1' => 'Le gain de temps']])->assertOk();
        $this->post($url, ['intent' => 'confirm_q1', 'answers' => ['q1' => 'Le gain de temps']])->assertOk();
        $this->post($url, ['intent' => 'confirm_q1', 'answers' => ['q1' => 'Le suivi des employés']])->assertConflict();
        $this->assertSame(0, Contact::count());
        $this->finish($survey, ['q1' => 'Le gain de temps', 'q2' => 'Oui, sans difficulté', 'q3' => 'Oui, parfaitement', 'q4' => 'Aucune']);
        $this->post($url, ['answers' => ['q1' => 'Le gain de temps']])->assertConflict();
        $this->assertSame(1, $survey->actions()->where('action_type', SurveyActionType::Q1Clicked)->count());
        $this->assertSame(1, $survey->actions()->where('action_type', SurveyActionType::Answered)->count());
        $this->assertSame(0, Contact::count());
    }

    public function test_conditions_without_js_hidden_answers_strict_validation_and_unicode(): void
    {
        $survey = $this->sent();
        $this->post($this->link($survey), ['intent' => 'preview', 'answers' => ['q1' => 'Autre (texte libre)']])->assertOk()->assertViewHas('visible', fn ($ids) => $ids === ['q1', 'q1_autre', 'q2', 'q3', 'q4']);
        foreach ([[], ['q1' => 'invalid'], ['q1' => ['bad']], ['q1' => 'Le gain de temps', 'q2' => 'bad'], ['q1' => 'Le gain de temps', 'q3' => 'bad'], ['q1' => 'Le gain de temps', 'q4' => 'bad'], ['q1' => 'Autre (texte libre)', 'q1_autre' => str_repeat('é', 2001)]] as $a) {
            $this->post($this->link($survey), ['answers' => $a])->assertStatus(422);
        }
        $this->finish($survey, ['q1' => 'Autre (texte libre)', 'q1_autre' => str_repeat('é', 2000)]);
        $this->assertSame(str_repeat('é', 2000), $survey->answers()->where('question_id', 'q1_autre')->value('value'));
        $hidden = $this->sent();
        $this->finish($hidden, ['q1' => 'Le gain de temps', 'q1_autre' => ['bad']]);
        $this->assertSame(0, $hidden->answers()->where('question_id', 'q1_autre')->count());
    }

    public function test_all_human_triggers_consolidate_and_technical_priority_wins(): void
    {
        foreach ([['Non, à cause d’un problème technique', null, ContactPriority::High], ['Oui, avec quelques difficultés', null, ContactPriority::Normal], ['Oui, sans difficulté', 'Un conseil d’utilisation', ContactPriority::Normal], ['Oui, sans difficulté', 'Une assistance technique', ContactPriority::Normal], ['Oui, sans difficulté', 'Une démonstration complémentaire', ContactPriority::Normal], ['Non, mon équipe n’a pas encore commencé', 'Une aide pour faire adopter l’outil à mon équipe', ContactPriority::Normal], ['Non, à cause d’un problème technique', 'Une assistance technique', ContactPriority::High]] as [$usage,$help,$priority]) {
            $s = $this->sent();
            $this->finish($s, array_filter(['q1' => 'Le gain de temps', 'q2' => $usage, 'q3' => 'Il me manque des possibilités', 'q4' => $help], fn ($v) => $v !== null));
            $c = $s->fresh()->contact;
            $this->assertNotNull($c);
            $this->assertSame($priority, $c->priority);
            $this->assertSame(ContactSource::D5Survey, $c->source);
            $this->assertSame($s->user_id, $c->user_id);
            $this->assertSame($s->entreprise_id, $c->entreprise_id);
            $this->assertStringContainsString('forfait', $c->message);
            $this->assertStringNotContainsString($s->token, $c->message);
            $this->assertSame(1, $s->actions()->where('action_type', SurveyActionType::Ticket)->count());
            app(D5ActionService::class)->process($s);
            $c->delete();
            app(D5ActionService::class)->process($s);
            $this->assertNull($s->fresh()->contact_id);
        }$this->assertSame(0, Contact::count());
    }

    public function test_adoption_upsell_and_renewal_signals_do_not_change_plan_or_create_contact_alone(): void
    {
        $s = $this->sent();
        $plan = $s->entreprise->type_compt;
        $paid = $s->entreprise->first_paid_at->toISOString();
        $this->finish($s, ['q1' => 'Le gain de temps', 'q2' => 'Non, mon équipe n’a pas encore commencé', 'q3' => 'Il me manque des possibilités', 'q4' => 'Aucune']);
        app(D5ActionService::class)->process($s);
        $this->assertSame(1, $s->actions()->where('action_type', SurveyActionType::AdoptionRisk)->count());
        $this->assertSame(1, $s->actions()->where('action_type', SurveyActionType::Upsell)->count());
        $this->assertSame($plan, $s->entreprise->fresh()->type_compt);
        $this->assertSame($paid, $s->entreprise->fresh()->first_paid_at->toISOString());
        $over = $this->sent();
        $this->finish($over, ['q1' => 'Le gain de temps', 'q3' => 'Il est plus complet que nécessaire']);
        app(D5ActionService::class)->process($over);
        $this->assertSame(1, $over->actions()->where('action_type', SurveyActionType::RenewalRisk)->count());
        $this->assertSame(0, Contact::count());
        $this->assertSame(0, DB::table('subscriptions')->count());
        Mail::assertSentCount(2);
    }

    public function test_matching_or_unknown_plan_and_no_help_do_not_create_false_negative_signal(): void
    {
        foreach (['Oui, parfaitement', 'Je ne sais pas encore'] as $fit) {
            $s = $this->sent();
            $this->finish($s, ['q1' => 'Le gain de temps', 'q2' => 'Oui, sans difficulté', 'q3' => $fit, 'q4' => 'Aucune']);
            $this->assertSame(0, $s->actions()->whereIn('action_type', [SurveyActionType::AdoptionRisk, SurveyActionType::Upsell, SurveyActionType::RenewalRisk, SurveyActionType::Ticket])->count());
        } $this->assertSame(0, Contact::count());
    }

    public function test_one_reminder_expiry_and_no_reminder_after_completion(): void
    {
        $s = $this->sent();
        $expires = $s->sent_at->addDays(15)->timestamp;
        $this->travel(3)->days();
        $this->assertSame('reminded', app(D5Lifecycle::class)->process($s->entreprise_id));
        $this->assertSame('waiting', app(D5Lifecycle::class)->process($s->entreprise_id));
        parse_str(parse_url($this->link($s), PHP_URL_QUERY), $q);
        $this->assertSame($expires, (int) $q['expires']);
        $this->travel(10)->days();
        $this->assertSame('expired', app(D5Lifecycle::class)->process($s->entreprise_id));
        $this->get($this->link($s))->assertStatus(410);
        $done = $this->sent();
        $this->finish($done, ['q1' => 'Le gain de temps']);
        $this->travel(3)->days();
        $this->assertSame('closed', app(D5Lifecycle::class)->process($done->entreprise_id));
    }

    public function test_all_translations_and_reminder_are_available_in_four_languages(): void
    {
        foreach (['fr', 'en', 'nl', 'es'] as $locale) {
            $s = $this->sent($locale);
            $this->get($this->link($s, 'Le gain de temps', $locale))->assertOk()->assertSee(__('surveys.d5_ui.title', [], $locale));
            foreach (app(SurveyCatalog::class)->definition(SurveyType::D5)['questions'] as $q) {
                $this->assertTrue(Lang::hasForLocale($q['label_key'], $locale));
                foreach ($q['choices'] as $c) {
                    $this->assertTrue(Lang::hasForLocale($c['label_key'], $locale));
                }
            }foreach (array_keys(Lang::get('surveys.ui', [], 'fr')) as $key) {
                $this->assertTrue(Lang::hasForLocale('surveys.d5_ui.'.$key, $locale));
            }$mail = new D5SurveyMail($s, $locale, true);
            $this->assertStringContainsString(__('surveys.d5_ui.email_reminder', [], $locale), $mail->render());
        }
    }

    public function test_scheduler_order_is_d5_to_d1_and_paid_step_is_first(): void
    {
        $this->owner();
        $this->artisan('surveys:process-d5')->assertSuccessful();
        $this->artisan('surveys:process-d5')->assertSuccessful();
        $this->assertSame(1, SurveyResponse::count());
        $commands = array_values(array_map(fn ($e) => $e->command ?? '', app(Schedule::class)->events()));
        $positions = [];
        foreach (['d5', 'd4', 'd3', 'd2', 'd1'] as $type) {
            $matches = array_keys(array_filter($commands, fn ($c) => str_contains($c, 'surveys:process-'.$type)));
            $this->assertCount(1, $matches);
            $positions[] = $matches[0];
        }$sorted = $positions;
        sort($sorted);
        $this->assertSame($sorted, $positions);
        Mail::assertSentCount(1);
    }

    public function test_concurrent_initial_final_and_actions_are_unique_even_after_contact_deletion(): void
    {
        $owner = $this->owner();
        $r = $this->compete('process', (string) $owner->entreprise_id);
        $this->assertSame(1, array_sum(array_column($r, 'emails')));
        $s = SurveyResponse::sole();
        $this->compete('answer', $s->id);
        $this->compete('actions', $s->id);
        foreach ([SurveyActionType::Ticket, SurveyActionType::Answered, SurveyActionType::Upsell] as $type) {
            $this->assertSame(1, $s->actions()->where('action_type', $type)->count());
        }$this->assertSame(ContactPriority::High, $s->fresh()->contact->priority);
        $s->fresh()->contact->delete();
        $this->compete('actions', $s->id);
        $this->assertSame(0, Contact::count());
    }

    public function test_manager_support_can_filter_view_reply_close_reopen_and_delete_d5_ticket(): void
    {
        $survey = $this->sent();
        $this->finish($survey, ['q1' => 'Le gain de temps', 'q2' => 'Non, à cause d’un problème technique', 'q4' => 'Une assistance technique']);
        $contact = $survey->fresh()->contact;
        $manager = Manager::create(['name' => 'Manager', 'email' => 'manager@example.test']);
        $this->actingAs($manager, 'manager')->get(route('manager.support.index', ['source' => ContactSource::D5Survey->value, 'priority' => 'high']))
            ->assertOk()->assertSee($contact->ticket_num)->assertSee($contact->subject)->assertSee($contact->email);
        $this->get(route('manager.support.show', $contact))->assertOk()
            ->assertSee($contact->ticket_num)->assertSee($survey->email);
        $service = app(ContactManagementService::class);
        $log = $service->reply($contact, $manager, 'Support reply', (string) Str::uuid());
        $this->assertSame(1, $contact->replies()->count());
        $this->assertSame($contact->id, $log->contact_id);
        $service->close($contact, $manager);
        $this->assertSame(\App\Enums\ContactStatus::Closed, $contact->fresh()->status);
        $service->reopen($contact, $manager);
        $this->assertSame(\App\Enums\ContactStatus::Pending, $contact->fresh()->status);
        $service->delete($contact, $manager, $contact->ticket_num);
        app(D5ActionService::class)->process($survey);
        $this->assertSame(0, Contact::count());
    }

    public function test_post_requires_csrf_and_does_not_remove_existing_web_protection(): void
    {
        $survey = $this->sent();
        $this->app->bind(\App\Http\Middleware\VerifyCsrfToken::class, function ($app) {
            return new class($app, $app['encrypter']) extends \App\Http\Middleware\VerifyCsrfToken
            {
                protected function runningUnitTests()
                {
                    return false;
                }
            };
        });
        $this->post($this->link($survey), ['answers' => ['q1' => 'Autre (texte libre)']])->assertStatus(419);
        $this->assertSame(0, SurveyAnswer::count());
    }

    public function test_tampered_token_signature_type_and_expired_link_are_rejected(): void
    {
        $survey = $this->sent();
        $url = $this->link($survey);
        $this->get(str_replace($survey->token, str_repeat('0', 64), $url))->assertForbidden();
        $this->get($url.'&bad=1')->assertForbidden();
        $wrong = URL::temporarySignedRoute('surveys.d1', $survey->sent_at->addDays(15), ['locale' => 'fr', 'survey' => $survey->id, 'token' => $survey->token]);
        $this->get($wrong)->assertForbidden();
        $this->travel(16)->days();
        $this->get($url)->assertForbidden();
        $this->assertSame(0, SurveyAnswer::count());
    }

    public function test_action_failure_preserves_answers_and_command_retries_without_duplicate_number(): void
    {
        $survey = $this->sent();
        $real = new \App\Services\TicketService;
        $failing = \Mockery::mock(\App\Services\TicketService::class);
        $failing->shouldReceive('create')->once()->andReturnUsing(function (...$args) use ($real) {
            $real->create(...$args);
            throw new \RuntimeException('Simulated D5 action failure');
        });
        $this->app->instance(\App\Services\TicketService::class, $failing);
        $this->finish($survey, ['q1' => 'Le gain de temps', 'q2' => 'Non, à cause d’un problème technique', 'q3' => 'Il me manque des possibilités']);
        $this->assertNotNull($survey->fresh()->completed_at);
        $this->assertSame(3, $survey->answers()->count());
        $this->assertSame(0, Contact::count());
        $this->assertSame(0, $survey->actions()->where('action_key', D5ActionService::PROCESSED_KEY)->count());
        $this->app->instance(\App\Services\TicketService::class, $real);
        $this->artisan('surveys:process-d5')->assertSuccessful();
        $this->artisan('surveys:process-d5')->assertSuccessful();
        $this->assertSame(1, Contact::count());
        $this->assertSame('DT-000001', Contact::sole()->ticket_num);
        $this->assertSame(ContactPriority::High, Contact::sole()->priority);
    }

    public function test_unconfirmed_initial_send_is_reserved_once_and_never_blindly_retried(): void
    {
        $owner = $this->owner();
        Mail::swap(Mail::getFacadeRoot()->manager);
        \Illuminate\Support\Facades\Event::listen(\Illuminate\Mail\Events\MessageSending::class, fn () => false);
        $this->assertSame('unconfirmed', app(D5Lifecycle::class)->process($owner->entreprise_id));
        $this->travel(3)->days();
        $this->assertSame('already_attempted', app(D5Lifecycle::class)->process($owner->entreprise_id));
        $survey = SurveyResponse::sole();
        $this->assertNull($survey->sent_at);
        $this->assertSame(1, $survey->actions()->where('action_type', SurveyActionType::EmailUnconfirmed)->count());
    }

    public function test_real_payment_success_hook_records_invoice_paid_time_and_refresh_is_immutable(): void
    {
        $owner = $this->owner('fr', false);
        $invoice = $this->invoice($owner);
        $client = $this->stripeInvoices([$invoice]);
        $session = \Stripe\Checkout\Session::constructFrom(['id' => 'cs_confirmed', 'payment_status' => 'paid', 'mode' => 'subscription', 'amount_total' => 2900, 'customer' => 'cus_'.$owner->entreprise_id, 'subscription' => 'sub_first', 'invoice' => $invoice['id'], 'metadata' => ['entreprise_id' => (string) $owner->entreprise_id, 'plan_name' => 'solo']]);
        $sessions = \Mockery::mock();
        $sessions->shouldReceive('retrieve')->once()->with('cs_confirmed')->andReturn($session);
        $client->shouldReceive('getService')->with('checkout')->andReturn((object) ['sessions' => $sessions]);
        $company = \Mockery::mock(Entreprise::class)->makePartial();
        $company->setRawAttributes(Entreprise::findOrFail($owner->entreprise_id)->getAttributes(), true);
        $company->exists = true;
        $company->setTable('entreprises');
        $company->shouldReceive('hasActivePaidSubscription')->andReturn(false);
        $company->shouldReceive('currentSubscription')->andReturn(null);
        $company->shouldReceive('invoicesIncludingPending')->andReturn([]);
        $owner->setRelation('entreprise', $company);
        $this->actingAs($owner);
        $crm = \Mockery::mock(\App\Services\CrmSubscriptionSync::class);
        $crm->shouldReceive('paid')->once();
        $this->app->instance(\App\Services\CrmSubscriptionSync::class, $crm);
        $controller = app(\App\Http\Controllers\admin\SubscriptionController::class);
        $request = \Illuminate\Http\Request::create('/payment/success/solo', 'GET', ['session_id' => 'cs_confirmed']);
        $this->assertSame(302, $controller->paymentSuccess($request, 'solo')->getStatusCode());
        $first = Entreprise::findOrFail($owner->entreprise_id)->first_paid_at->toISOString();
        $this->assertSame(now('UTC')->subDays(7)->toISOString(), $first);
        $this->assertSame('solo', Entreprise::findOrFail($owner->entreprise_id)->type_compt);
        $this->artisan('surveys:process-d5')->assertSuccessful();
        $this->travel(1)->days();
        $this->assertSame(302, $controller->paymentSuccess($request, 'solo')->getStatusCode());
        $this->assertSame($first, Entreprise::findOrFail($owner->entreprise_id)->first_paid_at->toISOString());
        $this->artisan('surveys:process-d5')->assertSuccessful();
        $this->assertSame(1, SurveyResponse::count());
    }

    public function test_real_webhook_hook_records_first_payment_without_browser_return(): void
    {
        $owner = $this->owner('fr', false);
        $invoice = $this->invoice($owner);
        $client = $this->stripeInvoices([$invoice]);
        $subscriptions = \Mockery::mock();
        $subscriptions->shouldReceive('retrieve')->once()->with('sub_first')->andReturn((object) ['status' => 'active']);
        $client->shouldReceive('getService')->with('subscriptions')->andReturn($subscriptions);
        $analytics = \Mockery::mock(\App\Services\AnalyticsBusinessEvents::class);
        $analytics->shouldReceive('payment')->once();
        $this->app->instance(\App\Services\AnalyticsBusinessEvents::class, $analytics);
        $service = \Mockery::mock(\App\Services\SubscriptionInvoiceService::class);
        $service->shouldReceive('createFromStripeInvoice')->once()->andReturn('test-invoice.pdf');
        $this->app->instance(\App\Services\SubscriptionInvoiceService::class, $service);
        $sender = \Mockery::mock(\App\Services\SubscriptionInvoiceSender::class);
        $sender->shouldReceive('sendAutomatic')->once();
        $this->app->instance(\App\Services\SubscriptionInvoiceSender::class, $sender);
        $controller = app(\App\Http\Controllers\StripeWebhookController::class);
        $method = new \ReflectionMethod($controller, 'handleInvoicePaymentSucceeded');
        $response = $method->invoke($controller, ['data' => ['object' => $invoice]]);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame($invoice['id'], $owner->entreprise->fresh()->first_paid_invoice_id);
        $this->assertSame(now('UTC')->subDays(7)->toISOString(), $owner->entreprise->fresh()->first_paid_at->toISOString());
    }

    public function test_checkout_lookup_failure_preserves_actual_payment_activation(): void
    {
        $owner = $this->owner('fr', false);
        $owner->entreprise->update(['type_compt' => 'solo']);
        $this->stripeInvoices([], 1, true);
        $session = \Stripe\Checkout\Session::constructFrom(['payment_status' => 'paid', 'mode' => 'subscription', 'amount_total' => 2900, 'customer' => 'cus_'.$owner->entreprise_id, 'metadata' => ['entreprise_id' => (string) $owner->entreprise_id]]);
        app(\App\Services\Surveys\FirstSubscriptionPayment::class)->fromCheckout($owner->entreprise->fresh(), $session);
        $this->assertSame('solo', $owner->entreprise->fresh()->type_compt);
        $this->assertNull($owner->entreprise->fresh()->first_paid_at);
    }

    public function test_valid_signature_cannot_pair_one_d5_with_another_token(): void
    {
        $a = $this->sent();
        $b = $this->sent();
        $url = URL::temporarySignedRoute('surveys.d5', $a->sent_at->addDays(15), ['locale' => 'fr', 'survey' => $a->id, 'token' => $b->token]);
        $this->get($url)->assertForbidden();
        $this->post($url, ['answers' => ['q1' => 'Le gain de temps']])->assertForbidden();
        $this->assertSame(0, SurveyAnswer::count());
    }

    public function test_concurrent_payment_receipts_converge_on_one_immutable_first_purchase(): void
    {
        $owner = $this->owner('fr', false);
        $r = $this->compete('payment', (string) $owner->entreprise_id);
        $this->assertSame(['in_concurrent_first', 'in_concurrent_first'], array_column($r, 'result'));
        $this->assertSame('in_concurrent_first', $owner->entreprise->fresh()->first_paid_invoice_id);
        $this->assertSame(now('UTC')->subDays(7)->toISOString(), $owner->entreprise->fresh()->first_paid_at->toISOString());
        $this->assertSame(0, SurveyResponse::count());
    }

    public function test_pending_payment_lookup_is_recovered_without_scanning_unpaid_companies(): void
    {
        $owner = $this->owner('fr', false);
        $this->owner('fr', false);
        $invoice = $this->invoice($owner);
        $this->stripeInvoices([], 1, true);
        app(\App\Services\Surveys\FirstSubscriptionPayment::class)->fromInvoice($owner->entreprise, $invoice);
        $this->assertSame('cus_'.$owner->entreprise_id, $owner->entreprise->fresh()->first_paid_lookup_customer_id);
        $this->assertNull($owner->entreprise->fresh()->first_paid_at);
        $this->assertSame('ineligible', app(D5Lifecycle::class)->process($owner->entreprise_id));
        $this->stripeInvoices([$invoice]);
        $this->artisan('surveys:sync-first-payments')->assertSuccessful();
        $this->assertNull($owner->entreprise->fresh()->first_paid_lookup_customer_id);
        $this->assertSame($invoice['id'], $owner->entreprise->fresh()->first_paid_invoice_id);
        $this->artisan('surveys:sync-first-payments')->assertSuccessful();
    }

    public function test_pending_receipt_identity_conflict_and_empty_history_do_not_guess_dates(): void
    {
        $owner = $this->owner('fr', false);
        $invoice = $this->invoice($owner);
        $this->stripeInvoices([]);
        app(\App\Services\Surveys\FirstSubscriptionPayment::class)->fromInvoice($owner->entreprise, $invoice);
        $this->assertNull($owner->entreprise->fresh()->first_paid_at);
        $owner->entreprise->update(['stripe_id' => 'cus_changed']);
        $this->stripeInvoices([], 0);
        $this->artisan('surveys:sync-first-payments')->assertFailed();
        $this->assertNull($owner->entreprise->fresh()->first_paid_at);
    }

    private function compete(string $mode, string $parent): array
    {
        $processes = [];
        $start = (string) (microtime(true) + 1);
        try {
            for ($i = 0; $i < 2; $i++) {
                $process = proc_open([PHP_BINARY, base_path('tests/Support/survey-d5-worker.php'), $this->schema,
                    config('database.connections.pgsql.database'), $start, $mode, $parent, now('UTC')->toIso8601String()],
                    [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, base_path());
                $this->assertIsResource($process);
                fclose($pipes[0]);
                $processes[] = [$process, $pipes];
            }
            $results = [];
            foreach ($processes as [$process, $pipes]) {
                $stdout = stream_get_contents($pipes[1]);
                $stderr = stream_get_contents($pipes[2]);
                fclose($pipes[1]);
                fclose($pipes[2]);
                $this->assertSame(0, proc_close($process), $stderr);
                $results[] = json_decode($stdout, true, 512, JSON_THROW_ON_ERROR);
            }
            $processes = [];

            return $results;
        } finally {
            foreach ($processes as [$process, $pipes]) {
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

    public function test_email_choice_is_automatically_confirmed_before_form_completion(): void
    {
        $survey = $this->sent();
        $questions = app(\App\Services\Surveys\SurveyCatalog::class)->definition($survey->survey_type, $survey->survey_version)['questions'];
        $choices = collect($questions)->firstWhere('id', 'q1')['choices'];
        $choice = $choices[0]['value'];
        $url = $this->link($survey, $choice);
        $this->get(app(\App\Services\Surveys\SurveyLink::class)->url($survey, 'fr'))->assertOk()->assertViewHas('autoConfirmQ1', false);
        $this->get($this->link($survey, 'invalid-choice'))->assertOk()->assertViewHas('autoConfirmQ1', false);
        $landing = $this->get($url)->assertOk()->assertViewHas('autoConfirmQ1', true)
            ->assertSee('data-email-answer="pending"', false);
        $this->assertSame(0, $survey->answers()->count());
        // This is the CSRF-protected confirmation POST automatically submitted by the browser.
        $confirmed = $this->post($url, ['intent' => 'confirm_q1', 'answers' => ['q1' => $choice]])
            ->assertOk()->assertViewHas('autoConfirmQ1', false)->assertViewHas('locked', true);
        if (getenv('SURVEY_EMAIL_QA_DIR')) {
            file_put_contents(getenv('SURVEY_EMAIL_QA_DIR').'/'.$survey->survey_type->value.'-landing.html', $landing->getContent());
            file_put_contents(getenv('SURVEY_EMAIL_QA_DIR').'/'.$survey->survey_type->value.'-confirmed.html', $confirmed->getContent());
        }
        $this->assertSame($choice, $survey->answers()->sole()->value);
        $this->assertNotNull($survey->fresh()->clicked_at);
        $this->assertNull($survey->fresh()->completed_at);
        $this->assertSame(\App\Enums\SurveyStatus::Q1Clicked, $survey->fresh()->status);
        $this->assertSame(1, $survey->actions()->where('action_type', 'Q1_CLICKED')->count());
        $this->assertSame(0, $survey->actions()->whereIn('action_type', ['ANSWERED', 'TICKET', 'ACTIONS_PROCESSED'])->count());
        $before = $survey->fresh()->toArray();
        $this->get($this->link($survey, $choices[1]['value']))->assertOk()->assertViewHas('autoConfirmQ1', false)->assertViewHas('locked', true);
        $this->post($url, ['intent' => 'confirm_q1', 'answers' => ['q1' => $choice]])->assertOk();
        $this->assertSame($before, $survey->fresh()->toArray());
        $this->assertSame(1, $survey->answers()->count());
        $this->assertSame(1, $survey->actions()->where('action_type', 'Q1_CLICKED')->count());
    }
}
