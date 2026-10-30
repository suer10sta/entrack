<?php

namespace Tests\Feature;

use App\Enums\ContactPriority;
use App\Enums\ContactSource;
use App\Enums\SurveyActionType;
use App\Enums\SurveyStatus;
use App\Enums\SurveyType;
use App\Mail\D6SurveyMail;
use App\Models\Contact;
use App\Models\Entreprise;
use App\Models\Manager;
use App\Models\SurveyAnswer;
use App\Models\SurveyResponse;
use App\Models\User;
use App\Services\ContactManagementService;
use App\Services\Surveys\D6ActionService;
use App\Services\Surveys\D6Eligibility;
use App\Services\Surveys\D6Lifecycle;
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

class SurveyD6Test extends TestCase
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
                    $table->string('stripe_id')->nullable();
                    $table->string('stripe_price')->nullable();
                    $table->integer('quantity')->nullable();
                    $table->timestampTz('trial_ends_at')->nullable();
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
        (require database_path('migrations/2026_10_30_100008_add_subscription_churn_evidence.php'))->up();
        (require database_path('migrations/2026_10_30_100009_add_d6_signal_action_types.php'))->up();
        (require database_path('migrations/2019_05_03_000003_create_subscription_items_table.php'))->up();
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
        $company = Entreprise::create(['name' => 'D6 company', 'trial_ends_at' => now('UTC')->subHours(24)]);
        $company->forceFill(['created_at' => now('UTC')->subYears(2)])->save();
        $user = User::create(['name' => 'Trial owner', 'email' => 'owner'.$company->id.'@example.test',
            'entreprise_id' => $company->id, 'locale' => $locale]);
        $user->forceFill(['created_at' => now('UTC')->subDays(3), 'email_verified_at' => now('UTC')->subDay()])->save();

        $company->forceFill(['stripe_id' => 'cus_'.$company->id])->save();
        if ($paid) {
            DB::table('entreprises')->where('id', $company->id)->update(['first_paid_at' => now('UTC')->subDays(90)->toIso8601String(), 'first_paid_invoice_id' => 'in_first_'.$company->id, 'type_compt' => 'solo']);
        }

        DB::table('subscriptions')->insert(['entreprise_id' => $company->id, 'stripe_id' => 'sub_'.$company->id, 'stripe_status' => 'canceled', 'ends_at' => now('UTC')->subDays(7)->toIso8601String()]);

        return $user;
    }

    private function sent(string $locale = 'fr'): SurveyResponse
    {
        $owner = $this->owner($locale);
        $this->assertSame('sent', app(D6Lifecycle::class)->process($owner->entreprise_id));

        return SurveyResponse::where('entreprise_id', $owner->entreprise_id)->where('survey_type', SurveyType::D6)->sole();
    }

    private function link(SurveyResponse $survey, string $choice = 'Mon activité a baissé ou est en pause', string $locale = 'fr'): string
    {
        return app(SurveyLink::class)->url($survey, $locale, $choice);
    }

    private function finish(SurveyResponse $survey, array $answers): void
    {
        $this->post($this->link($survey), ['intent' => 'complete', 'answers' => $answers])->assertOk();
    }

    private function state(): \App\Services\Surveys\SubscriptionChurnState
    {
        return app(\App\Services\Surveys\SubscriptionChurnState::class);
    }

    private function subscription(User $u): object
    {
        return DB::table('subscriptions')->where('entreprise_id', $u->entreprise_id)->orderByDesc('id')->first();
    }

    private function resume(SurveyResponse $s): void
    {
        DB::table('subscriptions')->where('entreprise_id', $s->entreprise_id)->update(['stripe_status' => 'active', 'ends_at' => null, 'survey_ended_at' => null]);
        DB::table('entreprises')->where('id', $s->entreprise_id)->update(['survey_resumed_at' => now('UTC')->toIso8601String()]);
    }

    public function test_only_proven_paid_customers_with_effective_end_are_eligible_at_exact_j7(): void
    {
        $owner = $this->owner();
        $s = $this->subscription($owner);
        $this->assertTrue(app(D6Eligibility::class)->eligible($owner->entreprise_id));
        foreach ([null, now('UTC')->addDays(4)->toIso8601String(), now('UTC')->toIso8601String(), now('UTC')->subDays(7)->addSecond()->toIso8601String()] as $end) {
            DB::table('subscriptions')->where('id', $s->id)->update(['ends_at' => $end]);
            $this->assertSame('ineligible', app(D6Lifecycle::class)->process($owner->entreprise_id));
        }
        DB::table('subscriptions')->where('id', $s->id)->update(['ends_at' => now('UTC')->subDays(7)->toIso8601String()]);
        $this->assertSame('sent', app(D6Lifecycle::class)->process($owner->entreprise_id));
        $unpaid = $this->owner('fr', false);
        $this->assertFalse(app(D6Eligibility::class)->eligible($unpaid->entreprise_id));
    }

    public function test_active_grace_period_and_temporary_payment_failure_do_not_create_false_churn(): void
    {
        $owner = $this->owner();
        $id = $this->subscription($owner)->id;
        foreach (['active', 'trialing', 'past_due', 'unpaid', 'incomplete'] as $status) {
            DB::table('subscriptions')->where('id', $id)->update(['stripe_status' => $status, 'ends_at' => null]);
            $this->assertSame('ineligible', app(D6Lifecycle::class)->process($owner->entreprise_id));
        }
        DB::table('subscriptions')->where('id', $id)->update(['stripe_status' => 'active', 'ends_at' => now('UTC')->addDays(30)->toIso8601String()]);
        $this->assertFalse(app(D6Eligibility::class)->eligible($owner->entreprise_id));
        $owner->entreprise->update(['account_status' => 'inactive', 'failed_payment_count' => 3]);
        DB::table('subscriptions')->where('id', $id)->update(['stripe_status' => 'past_due', 'ends_at' => null]);
        $this->assertFalse(app(D6Eligibility::class)->eligible($owner->entreprise_id));
        $this->assertSame(0, SurveyResponse::count());
        Mail::assertNothingSent();
    }

    public function test_cashier_scheduled_cancellation_uses_effective_access_end_not_request_or_updated_at(): void
    {
        $owner = $this->owner();
        $id = $this->subscription($owner)->id;
        DB::table('subscriptions')->where('id', $id)->update(['stripe_status' => 'active', 'ends_at' => now('UTC')->addDays(30)->toIso8601String(), 'updated_at' => now('UTC')->subDays(100)]);
        $this->assertNull($this->state()->currentEnd($owner->entreprise_id));
        $this->travel(30)->days();
        $this->assertNotNull($this->state()->currentEnd($owner->entreprise_id));
        $this->assertFalse(app(D6Eligibility::class)->eligible($owner->entreprise_id));
        $this->travel(7)->days();
        $this->assertSame('sent', app(D6Lifecycle::class)->process($owner->entreprise_id));
    }

    public function test_initial_window_is_j7_to_j10_and_72_hour_gate_can_defer_but_never_send_late(): void
    {
        $owner = $this->owner();
        SurveyResponse::create(['entreprise_id' => $owner->entreprise_id, 'email' => $owner->email, 'survey_type' => SurveyType::D5, 'survey_version' => 1, 'sent_at' => now('UTC')->subDays(2)]);
        $this->assertSame('rate_limited', app(D6Lifecycle::class)->process($owner->entreprise_id));
        $this->travel(1)->days();
        $this->assertSame('sent', app(D6Lifecycle::class)->process($owner->entreprise_id));
        $late = $this->owner();
        $this->travel(3)->days();
        $this->assertTrue(app(D6Eligibility::class)->eligible($late->entreprise_id));
        $this->travel(1)->seconds();
        $this->assertFalse(app(D6Eligibility::class)->eligible($late->entreprise_id));
        $this->assertSame('ineligible', app(D6Lifecycle::class)->process($late->entreprise_id));
    }

    public function test_unsent_d6_is_cancelled_when_send_window_has_elapsed(): void
    {
        $owner = $this->owner();
        $end = $this->state()->currentEnd($owner->entreprise_id);
        $s = SurveyResponse::create(['entreprise_id' => $owner->entreprise_id, 'user_id' => $owner->id, 'email' => $owner->email, 'survey_type' => SurveyType::D6, 'survey_version' => 1, 'subscription_id' => $end['subscription_id'], 'cycle_key' => $end['cycle_key']]);
        $this->travel(4)->days();
        $this->assertSame('closed', app(D6Lifecycle::class)->process($owner->entreprise_id));
        $this->assertSame(SurveyStatus::Cancelled, $s->fresh()->status);
        $this->assertNull($s->fresh()->sent_at);
        Mail::assertNothingSent();
    }

    public function test_permanent_survey_stop_and_unverified_owner_prevent_d6(): void
    {
        $u = $this->owner();
        $u->forceFill(['email_verified_at' => null])->save();
        $this->assertFalse(app(D6Eligibility::class)->eligible($u->entreprise_id));
        $u->forceFill(['email_verified_at' => now('UTC')])->save();
        $stop = SurveyResponse::create(['entreprise_id' => $u->entreprise_id, 'email' => $u->email, 'survey_type' => SurveyType::D1, 'survey_version' => 1]);
        $stop->answers()->create(['question_id' => 'q1', 'value' => 'abandon']);
        $this->assertSame('ineligible', app(D6Lifecycle::class)->process($u->entreprise_id));
        Mail::assertNothingSent();
    }

    public function test_cycle_key_and_subscription_link_are_stable_and_initial_email_is_unique(): void
    {
        $s = $this->sent();
        $end = $this->state()->currentEnd($s->entreprise_id);
        $this->assertSame($end['cycle_key'], $s->cycle_key);
        $this->assertSame($end['subscription_id'], $s->subscription_id);
        $this->assertNotSame('once', $s->cycle_key);
        $this->assertSame('waiting', app(D6Lifecycle::class)->process($s->entreprise_id));
        $this->artisan('surveys:process-d6')->assertSuccessful();
        $this->assertSame(1, SurveyResponse::count());
        Mail::assertSentCount(1);
    }

    public function test_resume_before_creation_or_after_creation_before_send_never_sends(): void
    {
        $u = $this->owner();
        $end = $this->state()->currentEnd($u->entreprise_id);
        $s = SurveyResponse::create(['entreprise_id' => $u->entreprise_id, 'user_id' => $u->id, 'email' => $u->email, 'survey_type' => SurveyType::D6, 'survey_version' => 1, 'subscription_id' => $end['subscription_id'], 'cycle_key' => $end['cycle_key']]);
        $this->resume($s);
        $this->assertSame('ineligible', app(D6Lifecycle::class)->process($u->entreprise_id));
        $this->assertSame(SurveyStatus::Cancelled, $s->fresh()->status);
        $this->assertNull($s->fresh()->sent_at);
        $this->assertSame(0, Contact::count());
        Mail::assertNothingSent();
    }

    public function test_resume_after_send_closes_old_link_without_mutation_or_new_actions(): void
    {
        $s = $this->sent();
        $before = $s->fresh()->toArray();
        $actions = $s->actions()->count();
        $this->resume($s);
        $this->get($this->link($s))->assertStatus(410);
        $this->post($this->link($s), ['answers' => ['q1' => 'Je rencontre un problème technique']])->assertStatus(410);
        $this->assertSame($before, $s->fresh()->toArray());
        $this->assertSame($actions, $s->actions()->count());
        $this->assertSame(0, SurveyAnswer::count());
        $this->assertSame('ineligible', app(D6Lifecycle::class)->process($s->entreprise_id));
        $this->assertSame(SurveyStatus::Cancelled, $s->fresh()->status);
        $this->assertSame(0, Contact::count());
    }

    public function test_resume_before_action_retry_preserves_answers_and_prevents_contact_or_signals(): void
    {
        $s = $this->sent();
        $mock = \Mockery::mock(D6ActionService::class);
        $mock->shouldReceive('processSafely')->once()->andReturn(true);
        $this->app->instance(D6ActionService::class, $mock);
        $this->finish($s, ['q1' => 'Je rencontre un problème technique']);
        $this->app->forgetInstance(D6ActionService::class);
        $this->resume($s);
        app(D6ActionService::class)->process($s);
        $this->assertNotNull($s->fresh()->completed_at);
        $this->assertSame(1, $s->answers()->count());
        $this->assertSame(SurveyStatus::Cancelled, $s->fresh()->status);
        $this->assertSame(0, Contact::count());
        $this->assertSame(0, $s->actions()->where('action_type', SurveyActionType::Ticket)->count());
    }

    public function test_two_churn_cycles_for_same_subscription_are_distinct_without_duplicate_first_cycle(): void
    {
        $first = $this->sent();
        $this->finish($first, ['q1' => 'Autre (texte libre)']);
        $key = $first->cycle_key;
        $this->resume($first);
        $this->assertSame('ineligible', app(D6Lifecycle::class)->process($first->entreprise_id));
        $this->travel(30)->days();
        DB::table('subscriptions')->where('id', $first->subscription_id)->update(['stripe_status' => 'canceled', 'ends_at' => now('UTC')->toIso8601String()]);
        $this->travel(7)->days();
        $this->assertSame('sent', app(D6Lifecycle::class)->process($first->entreprise_id));
        $this->assertSame(2, SurveyResponse::count());
        $latest = SurveyResponse::where('cycle_key', '!=', $key)->sole();
        $this->assertNotSame($key, $latest->cycle_key);
        $this->assertSame($first->subscription_id, $latest->subscription_id);
        $this->assertSame('waiting', app(D6Lifecycle::class)->process($first->entreprise_id));
    }

    public function test_new_subscription_blocks_old_churn_then_can_generate_its_own_cycle(): void
    {
        $first = $this->sent();
        $id = DB::table('subscriptions')->insertGetId(['entreprise_id' => $first->entreprise_id, 'stripe_id' => 'sub_new', 'stripe_status' => 'active']);
        $this->assertSame('ineligible', app(D6Lifecycle::class)->process($first->entreprise_id));
        $this->assertSame(SurveyStatus::Cancelled, $first->fresh()->status);
        $this->travel(30)->days();
        DB::table('subscriptions')->where('id', $id)->update(['stripe_status' => 'canceled', 'ends_at' => now('UTC')->toIso8601String()]);
        $this->travel(7)->days();
        $this->assertSame('sent', app(D6Lifecycle::class)->process($first->entreprise_id));
        $this->assertSame(2, SurveyResponse::count());
    }

    public function test_resume_after_answer_invalidates_recall_but_preserves_contact_answers_and_signal_history(): void
    {
        $s = $this->sent();
        $this->finish($s, ['q1' => 'Je rencontre un problème technique', 'q2' => 'Oui, certainement', 'q4' => 'Dans un mois']);
        $contact = $s->fresh()->contact;
        $this->assertTrue($this->state()->recallApplicable($s->fresh()));
        $this->resume($s);
        $this->assertFalse($this->state()->recallApplicable($s->fresh()));
        $this->artisan('surveys:process-d6')->assertSuccessful();
        $this->assertNull($s->fresh()->recall_due_at);
        $this->assertSame(SurveyStatus::Answered, $s->fresh()->status);
        $this->assertSame($contact->id, $s->fresh()->contact_id);
        $this->assertSame(3, $s->answers()->count());
        $this->assertSame(1, $s->actions()->where('action_type', SurveyActionType::RecallPlanned)->count());
        $this->assertSame(1, $s->actions()->where('action_type', SurveyActionType::RecallObsolete)->count());
    }

    public function test_email_has_eight_signed_q1_links_and_get_does_not_write(): void
    {
        $s = $this->sent('en');
        Mail::assertSent(D6SurveyMail::class, function ($mail) use ($s) {
            $html = $mail->render();
            $this->assertStringContainsString('subscription has now ended', $html);
            preg_match_all('/href="([^"]+)"/', $html, $m);
            $this->assertCount(8, $m[1]);
            foreach ($m[1] as $url) {
                $r = \Illuminate\Http\Request::create(html_entity_decode($url));
                $this->assertTrue($r->hasValidSignature());
                $this->assertSame($s->sent_at->addDays(15)->timestamp, (int) $r->query('expires'));
            }

            return true;
        });
        $before = $s->fresh()->toArray();
        $actions = $s->actions()->count();
        $this->get($this->link($s))->assertOk();
        $this->assertSame($before, $s->fresh()->toArray());
        $this->assertSame($actions, $s->actions()->count());
        $this->assertSame(0, SurveyAnswer::count());
    }

    public function test_q1_is_immutable_and_finalization_is_unique(): void
    {
        $s = $this->sent();
        $url = $this->link($s);
        $this->post($url, ['intent' => 'confirm_q1', 'answers' => ['q1' => 'Mon activité a baissé ou est en pause']])->assertOk();
        $this->post($url, ['intent' => 'confirm_q1', 'answers' => ['q1' => 'Je rencontre un problème technique']])->assertConflict();
        $this->finish($s, ['q1' => 'Mon activité a baissé ou est en pause', 'q2' => 'Probablement', 'q4' => 'Dans un mois']);
        $this->post($url, ['answers' => ['q1' => 'Mon activité a baissé ou est en pause']])->assertConflict();
        $this->assertSame(1, $s->actions()->where('action_type', SurveyActionType::Q1Clicked)->count());
        $this->assertSame(1, $s->actions()->where('action_type', SurveyActionType::Answered)->count());
    }

    public function test_q3_q4_conditions_no_js_hidden_answers_and_strict_validation(): void
    {
        $s = $this->sent();
        $this->post($this->link($s), ['intent' => 'preview', 'answers' => ['q1' => 'Le prix de mon forfait ne me convient plus', 'q2' => 'Probablement']])->assertOk()->assertViewHas('visible', fn ($ids) => $ids === ['q1', 'q2', 'q3', 'q4']);
        $this->post($this->link($s), ['intent' => 'preview', 'answers' => ['q1' => 'Autre (texte libre)', 'q2' => 'Non']])->assertOk()->assertViewHas('visible', fn ($ids) => $ids === ['q1', 'q1_autre', 'q2']);
        foreach ([[], ['q1' => 'bad'], ['q1' => ['bad']], ['q1' => 'Autre (texte libre)', 'q2' => 'bad'], ['q1' => 'Le prix de mon forfait ne me convient plus', 'q3' => 'bad'], ['q1' => 'Autre (texte libre)', 'q2' => 'Probablement', 'q4' => 'bad'], ['q1' => 'Autre (texte libre)', 'q1_autre' => str_repeat('é', 2001)]] as $a) {
            $this->post($this->link($s), ['answers' => $a])->assertStatus(422);
        }
        $this->finish($s, ['q1' => 'Autre (texte libre)', 'q1_autre' => str_repeat('é', 2000), 'q2' => 'Non', 'q3' => ['bad'], 'q4' => ['bad']]);
        $this->assertSame(str_repeat('é', 2000), $s->answers()->where('question_id', 'q1_autre')->value('value'));
        $this->assertSame(0, $s->answers()->whereIn('question_id', ['q3', 'q4'])->count());
        $this->assertNull($s->fresh()->recall_due_at);
    }

    public function test_each_human_reason_creates_one_normal_contact_and_deletion_does_not_recreate(): void
    {
        foreach (['Je rencontre un problème technique', 'Dalytrac ne répond pas suffisamment à mes besoins', 'Une fonctionnalité essentielle me manque'] as $reason) {
            $s = $this->sent();
            $this->finish($s, ['q1' => $reason]);
            $c = $s->fresh()->contact;
            $this->assertNotNull($c);
            $this->assertSame(ContactPriority::Normal, $c->priority);
            $this->assertSame(ContactSource::D6Survey, $c->source);
            $this->assertSame($s->user_id, $c->user_id);
            $this->assertSame($s->entreprise_id, $c->entreprise_id);
            $this->assertStringNotContainsString($s->token, $c->message);
            app(D6ActionService::class)->process($s);
            $this->assertSame(1, $s->actions()->where('action_type', SurveyActionType::Ticket)->count());
            $c->delete();
            app(D6ActionService::class)->process($s);
            $this->assertNull($s->fresh()->contact_id);
        } $this->assertSame(0, Contact::count());
    }

    public function test_pause_positive_intent_and_return_planned_signals_without_contact(): void
    {
        foreach (['Mon activité a baissé ou est en pause' => SurveyActionType::BusinessPaused, 'Je prévois de reprendre prochainement' => SurveyActionType::ReturnPlanned] as $reason => $type) {
            $s = $this->sent();
            $this->finish($s, ['q1' => $reason, 'q2' => 'Probablement', 'q4' => 'Dans trois mois']);
            app(D6ActionService::class)->process($s);
            $this->assertSame(1, $s->actions()->where('action_type', $type)->count());
            $this->assertNotNull($s->fresh()->recall_due_at);
        } $this->assertSame(0, Contact::count());
        Mail::assertSentCount(2);
    }

    public function test_recall_dates_use_completion_date_not_retry_date_and_clamp_calendar_months(): void
    {
        foreach (['Dans un mois' => 1, 'Dans trois mois' => 3, 'Dans six mois' => 6] as $choice => $months) {
            $s = $this->sent();
            $this->finish($s, ['q1' => 'Je prévois de reprendre prochainement', 'q2' => 'Oui, certainement', 'q4' => $choice]);
            $due = $s->fresh()->completed_at->addMonthsNoOverflow($months);
            $this->assertTrue($s->fresh()->recall_due_at->equalTo($due));
            $this->travel(1)->days();
            app(D6ActionService::class)->process($s);
            $this->assertTrue($s->fresh()->recall_due_at->equalTo($due));
            $this->assertSame(1, $s->actions()->where('action_type', SurveyActionType::RecallPlanned)->count());
        }
    }

    public function test_price_needs_competitor_and_alternative_plan_signals_have_correct_scope(): void
    {
        $price = $this->sent();
        $this->finish($price, ['q1' => 'Le prix de mon forfait ne me convient plus', 'q3' => 'Oui, un forfait plus léger']);
        $this->assertSame(1, $price->actions()->where('action_type', SurveyActionType::PriceBarrier)->count());
        $this->assertSame(1, $price->actions()->where('action_type', SurveyActionType::AlternativePlan)->count());
        $this->assertNull($price->fresh()->contact_id);
        $needs = $this->sent();
        $this->finish($needs, ['q1' => 'Dalytrac ne répond pas suffisamment à mes besoins', 'q3' => 'Oui, un forfait plus complet']);
        $this->assertSame(1, $needs->actions()->where('action_type', SurveyActionType::NeedsNotCovered)->count());
        $this->assertSame(1, $needs->actions()->where('action_type', SurveyActionType::AlternativePlan)->count());
        $this->assertNotNull($needs->fresh()->contact_id);
        $competitor = $this->sent();
        $this->finish($competitor, ['q1' => 'J’utilise désormais une autre solution', 'q2' => 'Probablement', 'q4' => 'Dans un mois']);
        $this->assertSame(1, $competitor->actions()->where('action_type', SurveyActionType::CompetitorChosen)->count());
        $this->assertNull($competitor->fresh()->recall_due_at);
        $this->assertSame(1, $competitor->actions()->where('action_type', SurveyActionType::StopCommercial)->count());
    }

    public function test_negative_intent_and_on_request_only_stop_d6_followup_not_all_surveys(): void
    {
        foreach ([['Probablement pas', 'Dans un mois'], ['Non', 'Dans six mois'], ['Oui, certainement', 'Uniquement à ma demande']] as [$intent,$when]) {
            $s = $this->sent();
            $this->finish($s, ['q1' => 'Mon activité a baissé ou est en pause', 'q2' => $intent, 'q4' => $when]);
            $this->assertNull($s->fresh()->recall_due_at);
            $stop = $s->actions()->where('action_type', SurveyActionType::StopCommercial)->sole();
            $this->assertSame('D6', json_decode($stop->detail, true)['scope']);
            $this->assertFalse(app(\App\Services\Surveys\D1Eligibility::class)->optedOut($s->entreprise_id));
            $this->assertSame(0, $s->actions()->where('action_type', SurveyActionType::RecallPlanned)->count());
        } $this->assertSame(0, Contact::count());
    }

    public function test_one_survey_reminder_is_separate_from_future_commercial_recall_and_expires_normally(): void
    {
        $s = $this->sent();
        $expires = $s->sent_at->addDays(15)->timestamp;
        $this->travel(3)->days();
        $this->assertSame('reminded', app(D6Lifecycle::class)->process($s->entreprise_id));
        $this->assertSame('waiting', app(D6Lifecycle::class)->process($s->entreprise_id));
        parse_str(parse_url($this->link($s), PHP_URL_QUERY), $q);
        $this->assertSame($expires, (int) $q['expires']);
        $this->travel(10)->days();
        $this->assertSame('expired', app(D6Lifecycle::class)->process($s->entreprise_id));
        $this->get($this->link($s))->assertStatus(410);
        $this->assertNull($s->fresh()->recall_due_at);
    }

    public function test_global_gate_defers_reminder_to_day_five_and_late_reminders_never_send(): void
    {
        $s = $this->sent();
        $this->travel(2)->days();
        SurveyResponse::create(['entreprise_id' => $s->entreprise_id, 'email' => $s->email, 'survey_type' => SurveyType::D5, 'survey_version' => 1, 'sent_at' => now('UTC')]);
        $this->travel(1)->days();
        $this->assertSame('rate_limited', app(D6Lifecycle::class)->process($s->entreprise_id));
        $this->travel(2)->days();
        $this->assertSame('reminded', app(D6Lifecycle::class)->process($s->entreprise_id));
        $late = $this->sent();
        $this->travel(6)->days();
        $this->assertSame('waiting', app(D6Lifecycle::class)->process($late->entreprise_id));
        $this->travel(9)->days();
        $this->assertSame('expired', app(D6Lifecycle::class)->process($late->entreprise_id));
    }

    public function test_all_d6_translations_and_reminder_are_present_without_fallback(): void
    {
        foreach (['fr', 'en', 'nl', 'es'] as $locale) {
            $s = $this->sent($locale);
            $this->get($this->link($s, 'Autre (texte libre)', $locale))->assertOk()->assertSee(__('surveys.d6_ui.title', [], $locale));
            foreach (app(SurveyCatalog::class)->definition(SurveyType::D6)['questions'] as $q) {
                $this->assertTrue(Lang::hasForLocale($q['label_key'], $locale));
                foreach ($q['choices'] as $c) {
                    $this->assertTrue(Lang::hasForLocale($c['label_key'], $locale));
                }
            }foreach (array_keys(Lang::get('surveys.ui', [], 'fr')) as $key) {
                $this->assertTrue(Lang::hasForLocale('surveys.d6_ui.'.$key, $locale));
            }$mail = new D6SurveyMail($s, $locale, true);
            $this->assertStringContainsString(__('surveys.d6_ui.email_reminder', [], $locale), $mail->render());
        }
    }

    public function test_scheduler_order_places_d6_before_d5_and_preserves_existing_tasks(): void
    {
        $commands = array_values(array_map(fn ($e) => $e->command ?? '', app(Schedule::class)->events()));
        $positions = [];
        foreach (['d6', 'd5', 'd4', 'd3', 'd2', 'd1'] as $type) {
            $matches = array_keys(array_filter($commands, fn ($c) => str_contains($c, 'surveys:process-'.$type)));
            $this->assertCount(1, $matches);
            $positions[] = $matches[0];
        }$sorted = $positions;
        sort($sorted);
        $this->assertSame($sorted, $positions);
        $this->owner();
        $this->artisan('surveys:process-d6')->assertSuccessful();
        $this->artisan('surveys:process-d6')->assertSuccessful();
        $this->assertSame(1, SurveyResponse::count());
        Mail::assertSentCount(1);
    }

    public function test_concurrent_initial_final_and_actions_create_one_ticket_and_recall(): void
    {
        $u = $this->owner();
        $r = $this->compete('process', (string) $u->entreprise_id);
        $this->assertSame(1, array_sum(array_column($r, 'emails')));
        $s = SurveyResponse::sole();
        $this->compete('answer', $s->id);
        $this->compete('actions', $s->id);
        foreach ([SurveyActionType::Answered, SurveyActionType::Ticket, SurveyActionType::RecallPlanned] as $type) {
            $this->assertSame(1, $s->actions()->where('action_type', $type)->count());
        }$this->assertSame(1, Contact::count());
        $this->assertNotNull($s->fresh()->recall_due_at);
        $s->fresh()->contact->delete();
        $this->compete('actions', $s->id);
        $this->assertSame(0, Contact::count());
    }

    public function test_manager_support_can_filter_view_reply_close_reopen_and_delete_d6_ticket(): void
    {
        $survey = $this->sent();
        $this->finish($survey, ['q1' => 'Je rencontre un problème technique']);
        $contact = $survey->fresh()->contact;
        $manager = Manager::create(['name' => 'Manager', 'email' => 'manager@example.test']);
        $this->actingAs($manager, 'manager')->get(route('manager.support.index', ['source' => ContactSource::D6Survey->value, 'priority' => 'normal']))
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
        app(D6ActionService::class)->process($survey);
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
            throw new \RuntimeException('Simulated D6 action failure');
        });
        $this->app->instance(\App\Services\TicketService::class, $failing);
        $this->finish($survey, ['q1' => 'Je rencontre un problème technique', 'q2' => 'Probablement', 'q4' => 'Dans un mois']);
        $this->assertNotNull($survey->fresh()->completed_at);
        $this->assertSame(3, $survey->answers()->count());
        $this->assertSame(0, Contact::count());
        $this->assertSame(0, $survey->actions()->where('action_key', D6ActionService::PROCESSED_KEY)->count());
        $this->app->instance(\App\Services\TicketService::class, $real);
        $this->artisan('surveys:process-d6')->assertSuccessful();
        $this->artisan('surveys:process-d6')->assertSuccessful();
        $this->assertSame(1, Contact::count());
        $this->assertSame('DT-000001', Contact::sole()->ticket_num);
        $this->assertSame(ContactPriority::Normal, Contact::sole()->priority);
    }

    public function test_unconfirmed_initial_send_is_reserved_once_and_never_blindly_retried(): void
    {
        $owner = $this->owner();
        Mail::swap(Mail::getFacadeRoot()->manager);
        \Illuminate\Support\Facades\Event::listen(\Illuminate\Mail\Events\MessageSending::class, fn () => false);
        $this->assertSame('unconfirmed', app(D6Lifecycle::class)->process($owner->entreprise_id));
        $this->travel(3)->days();
        $this->assertSame('already_attempted', app(D6Lifecycle::class)->process($owner->entreprise_id));
        $survey = SurveyResponse::sole();
        $this->assertNull($survey->sent_at);
        $this->assertSame(1, $survey->actions()->where('action_type', SurveyActionType::EmailUnconfirmed)->count());
    }

    public function test_real_deleted_webhook_persists_ended_at_not_cancellation_request_or_receipt_time(): void
    {
        $u = $this->owner();
        $sub = $this->subscription($u);
        DB::table('subscriptions')->where('id', $sub->id)->update(['stripe_status' => 'active', 'ends_at' => now('UTC')->addDays(30)->toIso8601String()]);
        $payload = ['created' => now('UTC')->timestamp, 'data' => ['object' => ['id' => $sub->stripe_id, 'customer' => 'cus_'.$u->entreprise_id, 'status' => 'canceled', 'ended_at' => now('UTC')->subDays(7)->timestamp, 'canceled_at' => now('UTC')->subDays(40)->timestamp]]];
        $controller = app(\App\Http\Controllers\StripeWebhookController::class);
        $method = new \ReflectionMethod($controller, 'handleCustomerSubscriptionDeleted');
        $this->assertSame(200, $method->invoke($controller, $payload)->getStatusCode());
        $end = $this->state()->currentEnd($u->entreprise_id);
        $this->assertSame(now('UTC')->subDays(7)->timestamp, $end['ended_at']->timestamp);
        $key = $end['cycle_key'];
        $this->assertSame('sent', app(D6Lifecycle::class)->process($u->entreprise_id));
        $this->travel(1)->days();
        $method->invoke($controller, $payload);
        $this->assertSame($key, $this->state()->currentEnd($u->entreprise_id)['cycle_key']);
        $this->assertSame(1, SurveyResponse::count());
    }

    public function test_terminal_unpaid_event_is_required_beyond_temporary_payment_failures(): void
    {
        $u = $this->owner();
        $s = $this->subscription($u);
        $u->entreprise->update(['account_status' => 'inactive', 'failed_payment_count' => 3]);
        DB::table('subscriptions')->where('id', $s->id)->update(['stripe_status' => 'past_due', 'ends_at' => null]);
        $payload = ['created' => now('UTC')->timestamp, 'data' => ['object' => ['id' => $s->stripe_id, 'customer' => 'cus_'.$u->entreprise_id, 'status' => 'past_due']]];
        app(\App\Services\Surveys\SubscriptionChurnSync::class)->event($payload, 'updated');
        $this->assertNull($this->state()->currentEnd($u->entreprise_id));
        $payload['data']['object']['status'] = 'unpaid';
        DB::table('subscriptions')->where('id', $s->id)->update(['stripe_status' => 'unpaid']);
        app(\App\Services\Surveys\SubscriptionChurnSync::class)->event($payload, 'updated');
        $this->assertSame(now('UTC')->timestamp, $this->state()->currentEnd($u->entreprise_id)['ended_at']->timestamp);
        $this->assertFalse(app(D6Eligibility::class)->eligible($u->entreprise_id));
        $this->travel(7)->days();
        $this->assertSame('sent', app(D6Lifecycle::class)->process($u->entreprise_id));
    }

    public function test_unpaid_without_confirmed_access_block_and_unknown_end_do_not_invent_churn_dates(): void
    {
        $u = $this->owner();
        $s = $this->subscription($u);
        DB::table('subscriptions')->where('id', $s->id)->update(['stripe_status' => 'unpaid', 'ends_at' => null]);
        $payload = ['created' => now('UTC')->timestamp, 'data' => ['object' => ['id' => $s->stripe_id, 'customer' => 'cus_'.$u->entreprise_id, 'status' => 'unpaid']]];
        app(\App\Services\Surveys\SubscriptionChurnSync::class)->event($payload, 'updated');
        $this->assertNull($this->state()->currentEnd($u->entreprise_id));
        $this->assertSame(0, SurveyResponse::count());
    }

    public function test_real_subscription_updated_hook_and_old_deleted_replay_cannot_reopen_old_cycle(): void
    {
        $s = $this->sent();
        $sub = DB::table('subscriptions')->where('id', $s->subscription_id)->first();
        $at = now('UTC')->timestamp;
        $payload = ['created' => $at, 'data' => ['object' => ['id' => $sub->stripe_id, 'customer' => 'cus_'.$s->entreprise_id, 'status' => 'active', 'cancel_at_period_end' => false, 'items' => ['data' => [['id' => 'si_test', 'quantity' => 1, 'price' => ['id' => 'price_test', 'product' => 'prod_test']]]]]]];
        $controller = app(\App\Http\Controllers\StripeWebhookController::class);
        $method = new \ReflectionMethod($controller, 'handleCustomerSubscriptionUpdated');
        $this->assertSame(200, $method->invoke($controller, $payload)->getStatusCode());
        $this->assertSame('active', DB::table('subscriptions')->where('id', $sub->id)->value('stripe_status'));
        $this->assertSame(SurveyStatus::Cancelled, $s->fresh()->status);
        $this->assertNull($this->state()->currentEnd($s->entreprise_id));
        app(\App\Services\Surveys\SubscriptionChurnSync::class)->event(['created' => $at - 1, 'data' => ['object' => ['id' => $sub->stripe_id, 'customer' => 'cus_'.$s->entreprise_id, 'status' => 'canceled', 'ended_at' => $at - 86400]]], 'deleted');
        $this->assertSame('active', DB::table('subscriptions')->where('id', $sub->id)->value('stripe_status'));
        $this->assertNull($this->state()->currentEnd($s->entreprise_id));
    }

    public function test_confirmed_checkout_closes_old_d6_before_new_subscription_row_is_synchronized(): void
    {
        $s = $this->sent();
        $this->finish($s, ['q1' => 'Je rencontre un problème technique', 'q2' => 'Probablement', 'q4' => 'Dans un mois']);
        $id = $s->fresh()->contact_id;
        $session = \Stripe\Checkout\Session::constructFrom(['payment_status' => 'paid', 'mode' => 'subscription', 'amount_total' => 2900, 'customer' => 'cus_'.$s->entreprise_id, 'metadata' => ['entreprise_id' => (string) $s->entreprise_id]]);
        app(\App\Services\Surveys\SubscriptionChurnSync::class)->checkout($s->entreprise, $session);
        $this->assertNull($this->state()->currentEnd($s->entreprise_id));
        $this->assertNull($s->fresh()->recall_due_at);
        $this->assertSame($id, $s->fresh()->contact_id);
        $this->assertSame(1, $s->actions()->where('action_type', SurveyActionType::RecallObsolete)->count());
        $this->get($this->link($s))->assertStatus(410);
    }

    public function test_old_paid_invoice_and_unhealthy_payment_do_not_fake_a_resumption(): void
    {
        $s = $this->sent();
        $invoice = ['status' => 'paid', 'amount_paid' => 2900, 'customer' => 'cus_'.$s->entreprise_id, 'subscription' => 'sub_first', 'status_transitions' => ['paid_at' => now('UTC')->subDays(8)->timestamp]];
        $sync = app(\App\Services\Surveys\SubscriptionChurnSync::class);
        $sync->invoice($s->entreprise, $invoice, true);
        $this->assertNotNull($this->state()->currentEnd($s->entreprise_id));
        $invoice['status_transitions']['paid_at'] = now('UTC')->timestamp;
        $sync->invoice($s->entreprise, $invoice, false);
        $this->assertNotNull($this->state()->currentEnd($s->entreprise_id));
        $sync->invoice($s->entreprise, $invoice, true);
        $this->assertNull($this->state()->currentEnd($s->entreprise_id));
        $this->assertSame(SurveyStatus::Cancelled, $s->fresh()->status);
    }

    public function test_valid_signature_cannot_pair_another_surveys_token(): void
    {
        $a = $this->sent();
        $b = $this->sent();
        $url = URL::temporarySignedRoute('surveys.d6', $a->sent_at->addDays(15), ['locale' => 'fr', 'survey' => $a->id, 'token' => $b->token]);
        $this->get($url)->assertForbidden();
        $this->post($url, ['answers' => ['q1' => 'Autre (texte libre)']])->assertForbidden();
        $this->assertSame(0, SurveyAnswer::count());
    }

    public function test_calendar_month_end_is_clamped_and_no_stripe_request_is_made_by_d6(): void
    {
        Carbon::setTestNow(Carbon::parse('2027-01-31 12:00:00', 'UTC'));
        $client = \Mockery::mock(\Stripe\StripeClient::class);
        $client->shouldNotReceive('getService');
        $this->app->bind(\Stripe\StripeClient::class, fn () => $client);
        $s = $this->sent();
        $this->finish($s, ['q1' => 'Je prévois de reprendre prochainement', 'q2' => 'Probablement', 'q4' => 'Dans un mois']);
        $this->assertSame('2027-02-28T12:00:00.000000Z', $s->fresh()->recall_due_at->toISOString());
        $this->assertSame(0, Contact::count());
    }

    public function test_local_cashier_resume_overrides_old_terminal_evidence_immediately(): void
    {
        $s = $this->sent();
        DB::table('subscriptions')->where('id', $s->subscription_id)->update(['survey_ended_at' => now('UTC')->subDays(7)->toIso8601String()]);
        DB::table('subscriptions')->where('id', $s->subscription_id)->update(['stripe_status' => 'active', 'ends_at' => null]);
        $this->assertNull($this->state()->currentEnd($s->entreprise_id));
        $this->get($this->link($s))->assertStatus(410);
    }

    public function test_resumption_transaction_wins_against_concurrent_final_answers(): void
    {
        $s = $this->sent();
        $process = proc_open([PHP_BINARY, base_path('tests/Support/survey-d6-worker.php'), $this->schema, config('database.connections.pgsql.database'), (string) microtime(true), 'resume', (string) $s->entreprise_id, now('UTC')->toIso8601String()], [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, base_path());
        $this->assertIsResource($process);
        fclose($pipes[0]);
        try {
            $this->assertSame('locked', trim(fgets($pipes[1])));
            $this->compete('answer', $s->id);
            $out = stream_get_contents($pipes[1]);
            $err = stream_get_contents($pipes[2]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            $this->assertSame(0, proc_close($process), $err);
            $process = null;
            $this->assertSame('resumed', json_decode($out, true, 512, JSON_THROW_ON_ERROR)['result']);
        } finally {
            if (is_resource($process)) {
                proc_terminate($process);
                foreach ($pipes as $pipe) {
                    if (is_resource($pipe)) {
                        fclose($pipe);
                    }
                }proc_close($process);
            }
        }
        $this->assertSame(0, SurveyAnswer::count());
        $this->assertSame(0, Contact::count());
        $this->assertSame(SurveyStatus::Cancelled, $s->fresh()->status);
        $this->assertNull($s->fresh()->recall_due_at);
    }

    public function test_later_terminal_events_do_not_shift_the_same_unpaid_churn_cycle(): void
    {
        $u = $this->owner();
        $s = $this->subscription($u);
        $u->entreprise->update(['account_status' => 'inactive', 'failed_payment_count' => 3]);
        DB::table('subscriptions')->where('id', $s->id)->update(['stripe_status' => 'unpaid', 'ends_at' => null]);
        $payload = ['created' => now('UTC')->timestamp, 'data' => ['object' => ['id' => $s->stripe_id, 'customer' => 'cus_'.$u->entreprise_id, 'status' => 'unpaid']]];
        $sync = app(\App\Services\Surveys\SubscriptionChurnSync::class);
        $sync->event($payload, 'updated');
        $key = $this->state()->currentEnd($u->entreprise_id)['cycle_key'];
        $this->travel(7)->days();
        $payload['created'] = now('UTC')->timestamp;
        $sync->event($payload, 'updated');
        $this->assertSame($key, $this->state()->currentEnd($u->entreprise_id)['cycle_key']);
        $this->assertSame('sent', app(D6Lifecycle::class)->process($u->entreprise_id));
        $payload['data']['object']['status'] = 'canceled';
        $payload['data']['object']['ended_at'] = now('UTC')->timestamp;
        $sync->event($payload, 'deleted');
        $this->assertSame($key, $this->state()->currentEnd($u->entreprise_id)['cycle_key']);
        $this->assertSame(1, SurveyResponse::count());
    }

    private function compete(string $mode, string $parent): array
    {
        $processes = [];
        $start = (string) (microtime(true) + 1);
        try {
            for ($i = 0; $i < 2; $i++) {
                $process = proc_open([PHP_BINARY, base_path('tests/Support/survey-d6-worker.php'), $this->schema,
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
