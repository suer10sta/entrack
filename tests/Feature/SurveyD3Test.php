<?php

namespace Tests\Feature;

use App\Enums\ContactPriority;
use App\Enums\ContactSource;
use App\Enums\SurveyActionType;
use App\Enums\SurveyType;
use App\Mail\D3SurveyMail;
use App\Models\Contact;
use App\Models\Entreprise;
use App\Models\Manager;
use App\Models\SurveyAnswer;
use App\Models\SurveyResponse;
use App\Models\User;
use App\Services\ContactManagementService;
use App\Services\Surveys\D3ActionService;
use App\Services\Surveys\D3Eligibility;
use App\Services\Surveys\D3Lifecycle;
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

class SurveyD3Test extends TestCase
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

    private function owner(string $locale = 'fr'): User
    {
        $company = Entreprise::create(['name' => 'D3 company', 'trial_ends_at' => now('UTC')->addDays(5)]);
        $company->forceFill(['created_at' => now('UTC')->subYears(2)])->save();
        $user = User::create(['name' => 'Trial owner', 'email' => 'owner'.$company->id.'@example.test',
            'entreprise_id' => $company->id, 'locale' => $locale]);
        $user->forceFill(['created_at' => now('UTC')->subDays(3), 'email_verified_at' => now('UTC')->subDay()])->save();

        DB::table('projects')->insert(['entreprise_id' => (string) $company->id]);

        return $user;
    }

    private function sent(string $locale = 'fr'): SurveyResponse
    {
        $owner = $this->owner($locale);
        $this->assertSame('sent', app(D3Lifecycle::class)->process($owner->entreprise_id));

        return SurveyResponse::where('entreprise_id', $owner->entreprise_id)->where('survey_type', SurveyType::D3)->sole();
    }

    private function link(SurveyResponse $survey, string $choice = '4', string $locale = 'fr'): string
    {
        return app(SurveyLink::class)->url($survey, $locale, $choice);
    }

    private function finish(SurveyResponse $survey, array $answers): void
    {
        $this->post($this->link($survey), ['intent' => 'complete', 'answers' => $answers])->assertOk();
    }

    public function test_j10_uses_trial_end_minus_five_not_creation_and_requires_active_trial(): void
    {
        $owner = $this->owner();
        $company = $owner->entreprise;
        $this->assertTrue(app(D3Eligibility::class)->eligible($company->id));
        $company->update(['trial_ends_at' => now('UTC')->addDays(5)->addSecond()]);
        $this->assertSame('ineligible', app(D3Lifecycle::class)->process($company->id));
        foreach ([null, now('UTC'), now('UTC')->subDay()] as $end) {
            $company->update(['trial_ends_at' => $end]);
            $this->assertFalse(app(D3Eligibility::class)->eligible($company->id));
        }
        $this->assertSame(0, SurveyResponse::count());
        Mail::assertNothingSent();
    }

    public function test_usage_requires_real_activity_or_confirmed_d2_real_use(): void
    {
        $owner = $this->owner();
        DB::table('projects')->delete();
        $this->assertFalse(app(D3Eligibility::class)->eligible($owner->entreprise_id));
        $d2 = SurveyResponse::create(['entreprise_id' => $owner->entreprise_id, 'user_id' => $owner->id, 'email' => $owner->email, 'survey_type' => SurveyType::D2, 'survey_version' => 1]);
        $d2->answers()->create(['question_id' => 'q1', 'value' => 'decouverte']);
        $this->assertFalse(app(D3Eligibility::class)->eligible($owner->entreprise_id));
        $d2->answers()->where('question_id', 'q1')->update(['value' => 'usage_reel']);
        $this->assertTrue(app(D3Eligibility::class)->eligible($owner->entreprise_id));
        $this->assertSame('sent', app(D3Lifecycle::class)->process($owner->entreprise_id));
        $this->assertSame(0, $d2->actions()->count());
    }

    public function test_all_existing_activity_categories_work_and_owner_seed_is_excluded(): void
    {
        $owner = $this->owner();
        DB::table('projects')->delete();
        DB::table('employees')->insert(['entreprise_id' => $owner->entreprise_id, 'user_id' => $owner->id, 'created_at' => $owner->created_at]);
        $this->assertFalse(app(D3Eligibility::class)->eligible($owner->entreprise_id));
        foreach (['projects', 'employees', 'clients', 'tasks_timer'] as $table) {
            $id = DB::table($table)->insertGetId(['entreprise_id' => $owner->entreprise_id]);
            $this->assertTrue(app(D3Eligibility::class)->eligible($owner->entreprise_id));
            DB::table($table)->where('id', $id)->delete();
        }
    }

    public function test_trial_plan_subscription_optout_and_recipient_rules(): void
    {
        $owner = $this->owner();
        $company = $owner->entreprise;
        $company->update(['type_compt' => 'solo']);
        $this->assertFalse(app(D3Eligibility::class)->eligible($company->id));
        $company->update(['type_compt' => null]);
        foreach (['active', 'trialing', 'past_due', 'unpaid'] as $status) {
            DB::table('subscriptions')->delete();
            DB::table('subscriptions')->insert(['entreprise_id' => $company->id, 'stripe_status' => $status]);
            $this->assertFalse(app(D3Eligibility::class)->eligible($company->id));
        }
        DB::table('subscriptions')->delete();
        $owner->forceFill(['email_verified_at' => null])->save();
        $this->assertFalse(app(D3Eligibility::class)->eligible($company->id));
        $owner->forceFill(['email_verified_at' => now('UTC')])->save();
        $stop = SurveyResponse::create(['entreprise_id' => $company->id, 'email' => $owner->email, 'survey_type' => SurveyType::D1, 'survey_version' => 1]);
        $stop->answers()->create(['question_id' => 'q1', 'value' => 'abandon']);
        $this->assertSame('ineligible', app(D3Lifecycle::class)->process($company->id));
        Mail::assertNothingSent();
    }

    public function test_shared_72_hour_gate_defers_creation_without_touching_d2_history(): void
    {
        $owner = $this->owner();
        $d2 = SurveyResponse::create(['entreprise_id' => $owner->entreprise_id, 'email' => $owner->email, 'survey_type' => SurveyType::D2, 'survey_version' => 1, 'sent_at' => now('UTC')->subDays(2)]);
        $before = $d2->fresh()->toArray();
        $this->assertSame('rate_limited', app(D3Lifecycle::class)->process($owner->entreprise_id));
        $this->assertSame(0, SurveyResponse::where('survey_type', SurveyType::D3)->count());
        $this->travel(1)->days();
        $this->assertSame('sent', app(D3Lifecycle::class)->process($owner->entreprise_id));
        $this->assertSame('waiting', app(D3Lifecycle::class)->process($owner->entreprise_id));
        $this->assertSame($before, $d2->fresh()->toArray());
        $this->assertSame(0, $d2->actions()->count());
        $this->assertSame('once', SurveyResponse::where('survey_type', SurveyType::D3)->sole()->cycle_key);
        Mail::assertSentCount(1);
    }

    public function test_email_contains_five_signed_scale_links_and_get_does_not_write(): void
    {
        $survey = $this->sent('en');
        Mail::assertSent(D3SurveyMail::class, function ($mail) use ($survey) {
            $html = $mail->render();
            preg_match_all('/href="([^"]+)"/', $html, $matches);
            $this->assertCount(5, $matches[1]);
            foreach ($matches[1] as $url) {
                $request = \Illuminate\Http\Request::create(html_entity_decode($url));
                $this->assertTrue($request->hasValidSignature());
                $this->assertContains($request->query('q1'), ['1', '2', '3', '4', '5']);
                $this->assertSame($survey->sent_at->addDays(15)->timestamp, (int) $request->query('expires'));
            }

            return true;
        });
        $before = $survey->toArray();
        $actions = $survey->actions()->count();
        $this->get($this->link($survey, '5'))->assertOk();
        $this->assertSame($before, $survey->fresh()->toArray());
        $this->assertSame($actions, $survey->actions()->count());
        $this->assertSame(0, SurveyAnswer::count());
    }

    public function test_q1_scale_confirmation_is_immutable_and_final_submission_is_unique(): void
    {
        $survey = $this->sent();
        $url = $this->link($survey, '4');
        $this->post($url, ['intent' => 'confirm_q1', 'answers' => ['q1' => '4']])->assertOk();
        $this->post($url, ['intent' => 'confirm_q1', 'answers' => ['q1' => '4']])->assertOk();
        $this->post($url, ['intent' => 'confirm_q1', 'answers' => ['q1' => '5']])->assertConflict();
        $this->finish($survey, ['q1' => '4', 'q2' => 'Devis', 'q3' => 'Rien, je poursuis l’évaluation', 'q4' => 'Incertain', 'q5' => ['invalid']]);
        $this->post($url, ['answers' => ['q1' => '4']])->assertConflict();
        $this->assertSame(4, $survey->answers()->count());
        $this->assertSame(0, $survey->answers()->where('question_id', 'q5')->count());
        $this->assertSame(1, $survey->actions()->where('action_type', SurveyActionType::Q1Clicked)->count());
        $this->assertSame(1, $survey->actions()->where('action_type', SurveyActionType::Answered)->count());
        $this->assertSame(0, Contact::count());
    }

    public function test_q5_and_other_conditions_work_without_javascript_and_validate_strictly(): void
    {
        $survey = $this->sent();
        foreach (['Très probable', 'Plutôt probable'] as $intent) {
            $this->post($this->link($survey), ['intent' => 'preview', 'answers' => ['q1' => '4', 'q4' => $intent, 'q2' => 'Autre (texte libre)']])->assertOk()->assertViewHas('visible', fn ($ids) => $ids === ['q1', 'q2', 'q2_autre', 'q3', 'q4', 'q5']);
        }
        $this->post($this->link($survey), ['intent' => 'preview', 'answers' => ['q1' => '4', 'q4' => 'Incertain']])->assertOk()->assertViewHas('visible', fn ($ids) => $ids === ['q1', 'q2', 'q3', 'q4']);
        foreach ([[], ['q1' => '0'], ['q1' => '6'], ['q1' => ['4']], ['q1' => '4', 'q2' => 'invalid'], ['q1' => '4', 'q3' => 'invalid'], ['q1' => '4', 'q4' => 'invalid'], ['q1' => '4', 'q4' => 'Très probable', 'q5' => 'invalid'], ['q1' => '4', 'q2' => 'Autre (texte libre)', 'q2_autre' => str_repeat('é', 2001)]] as $answers) {
            $this->post($this->link($survey), ['answers' => $answers])->assertStatus(422);
        }
        $this->assertSame(0, SurveyAnswer::count());
        $this->finish($survey, ['q1' => '4', 'q2' => 'Autre (texte libre)', 'q2_autre' => str_repeat('é', 2000), 'q4' => 'Très probable', 'q5' => 'Pro']);
        $this->assertSame('Pro', $survey->answers()->where('question_id', 'q5')->value('value'));
        $this->assertSame(str_repeat('é', 2000), $survey->answers()->where('question_id', 'q2_autre')->value('value'));
    }

    public function test_each_contact_rule_and_consolidation_use_normal_priority(): void
    {
        foreach ([['1', null, null], ['2', null, null], ['4', 'Un problème technique', null], ['4', 'Une fonctionnalité manque', null], ['4', 'Le prix des forfaits', 'Très probable'], ['2', 'Un problème technique', 'Très probable']] as [$gain,$barrier,$intent]) {
            $survey = $this->sent();
            $this->finish($survey, array_filter(['q1' => $gain, 'q3' => $barrier, 'q4' => $intent], fn ($v) => $v !== null));
            $contact = $survey->fresh()->contact;
            $this->assertNotNull($contact);
            $this->assertSame(ContactPriority::Normal, $contact->priority);
            $this->assertSame(ContactSource::D3Survey, $contact->source);
            $this->assertSame($survey->user_id, $contact->user_id);
            $this->assertSame($survey->entreprise_id, $contact->entreprise_id);
            $this->assertStringNotContainsString($survey->token, $contact->message);
            $this->assertSame(1, $survey->actions()->where('action_type', SurveyActionType::Ticket)->count());
            app(D3ActionService::class)->process($survey);
            $contact->delete();
            app(D3ActionService::class)->process($survey);
            $this->assertNull($survey->fresh()->contact_id);
        }
        $this->assertSame(0, Contact::count());
    }

    public function test_strong_intent_records_plan_and_sends_plans_once_for_both_thresholds(): void
    {
        foreach (['Très probable', 'Plutôt probable'] as $intent) {
            $survey = $this->sent();
            $this->finish($survey, ['q1' => '5', 'q4' => $intent, 'q5' => 'Solo']);
            app(D3ActionService::class)->process($survey);
            $this->artisan('surveys:process-d3')->assertSuccessful();
            $signal = $survey->actions()->where('action_type', SurveyActionType::PurchaseIntent)->sole();
            $this->assertSame('Solo', json_decode($signal->detail, true)['plan']);
            $this->assertSame(1, $survey->actions()->where('action_type', SurveyActionType::AutoEmail)->count());
            $this->assertSame(0, $survey->actions()->where('action_type', SurveyActionType::Ticket)->count());
        }
        Mail::assertSent(\App\Mail\D3BusinessMail::class, 2);
        $this->assertSame(0, Contact::count());
    }

    public function test_price_without_strong_intent_is_signal_only_and_high_gain_does_not_create_contact(): void
    {
        $survey = $this->sent();
        $this->finish($survey, ['q1' => '5', 'q3' => 'Le prix des forfaits', 'q4' => 'Incertain']);
        $this->assertSame(1, $survey->actions()->where('action_type', SurveyActionType::PriceBarrier)->count());
        $this->assertSame(0, Contact::count());
        Mail::assertNotSent(\App\Mail\D3BusinessMail::class);
    }

    public function test_decision_approval_email_and_plans_email_are_independently_idempotent(): void
    {
        $survey = $this->sent();
        $this->finish($survey, ['q1' => '4', 'q3' => 'Je dois obtenir l’accord d’un responsable', 'q4' => 'Très probable']);
        $this->assertSame(0, Contact::count());
        $this->assertSame(1, $survey->actions()->where('action_type', SurveyActionType::DecisionApproval)->count());
        app(D3ActionService::class)->process($survey);
        $this->assertSame(2, $survey->actions()->where('action_type', SurveyActionType::AutoEmail)->count());
        Mail::assertSent(\App\Mail\D3BusinessMail::class, 2);
    }

    public function test_failed_business_delivery_keeps_answers_signals_and_contact_and_is_not_blindly_resent(): void
    {
        $survey = $this->sent();
        Mail::swap(Mail::getFacadeRoot()->manager);
        \Illuminate\Support\Facades\Event::listen(\Illuminate\Mail\Events\MessageSending::class, fn () => false);
        $this->finish($survey, ['q1' => '2', 'q3' => 'Un problème technique', 'q4' => 'Très probable']);
        $this->assertNotNull($survey->fresh()->completed_at);
        $this->assertSame(1, Contact::count());
        $this->assertSame(1, $survey->actions()->where('action_type', SurveyActionType::EmailUnconfirmed)->count());
        app(D3ActionService::class)->process($survey);
        $this->artisan('surveys:process-d3')->assertSuccessful();
        $this->assertSame(1, $survey->actions()->where('action_key', 'D3:PLANS:ATTEMPT')->count());
        $this->assertSame(0, $survey->actions()->where('action_type', SurveyActionType::AutoEmail)->count());
    }

    public function test_pre_attempt_render_failure_is_retryable_without_duplicate_actions(): void
    {
        $survey = $this->sent();
        $bad = config('view.paths');
        config(['view.paths' => ['missing-d3-views']]);
        app('view')->getFinder()->setPaths(['missing-d3-views']);
        app('view')->getFinder()->flush();
        app(\App\Services\Surveys\D3AnswerService::class)->submit($survey, ['q1' => '4', 'q4' => 'Très probable'], false);
        $this->assertNotNull($survey->fresh()->completed_at);
        $this->assertSame(0, $survey->actions()->where('action_key', 'D3:PLANS:ATTEMPT')->count());
        config(['view.paths' => $bad]);
        app('view')->getFinder()->setPaths($bad);
        app('view')->getFinder()->flush();
        $this->artisan('surveys:process-d3')->assertSuccessful();
        $this->artisan('surveys:process-d3')->assertSuccessful();
        $this->assertSame(1, $survey->actions()->where('action_type', SurveyActionType::PurchaseIntent)->count());
        Mail::assertSent(\App\Mail\D3BusinessMail::class, 1);
    }

    public function test_reminder_window_global_gate_expiry_and_trial_cancellation(): void
    {
        $survey = $this->sent();
        $this->travel(3)->days();
        $this->assertSame('reminded', app(D3Lifecycle::class)->process($survey->entreprise_id));
        $this->assertSame('waiting', app(D3Lifecycle::class)->process($survey->entreprise_id));
        $survey->entreprise->update(['trial_ends_at' => now('UTC')->addDays(20)]);
        $this->travel(10)->days();
        $this->assertSame('expired', app(D3Lifecycle::class)->process($survey->entreprise_id));
        $this->get($this->link($survey))->assertStatus(410);
        $other = $this->sent();
        $other->entreprise->update(['type_compt' => 'solo']);
        $this->assertSame('cancelled', app(D3Lifecycle::class)->process($other->entreprise_id));
        $this->get($this->link($other))->assertStatus(410);
    }

    public function test_final_answer_has_no_reminder_and_maximum_link_age_is_fifteen_days(): void
    {
        $answered = $this->sent();
        $this->finish($answered, ['q1' => '4']);
        $this->travel(3)->days();
        $this->assertSame('closed', app(D3Lifecycle::class)->process($answered->entreprise_id));
        $open = $this->sent();
        $open->entreprise->update(['trial_ends_at' => now('UTC')->addDays(30)]);
        $this->travel(6)->days();
        $this->assertSame('waiting', app(D3Lifecycle::class)->process($open->entreprise_id));
        $this->travel(9)->days();
        $this->assertSame('expired', app(D3Lifecycle::class)->process($open->entreprise_id));
        $this->assertSame(0, $open->actions()->where('action_type', SurveyActionType::ReminderSent)->count());
    }

    public function test_all_survey_and_business_email_translations_are_present_in_four_locales(): void
    {
        foreach (['fr', 'en', 'nl', 'es'] as $locale) {
            $survey = $this->sent($locale);
            $this->get($this->link($survey, '4', $locale))->assertOk()->assertSee(__('surveys.d3_ui.title', [], $locale));
            foreach (app(SurveyCatalog::class)->definition(SurveyType::D3)['questions'] as $q) {
                $this->assertTrue(Lang::hasForLocale($q['label_key'], $locale));
                foreach ($q['choices'] as $c) {
                    $this->assertTrue(Lang::hasForLocale($c['label_key'], $locale));
                }
            }
            foreach (array_keys(Lang::get('surveys.ui', [], 'fr')) as $key) {
                $this->assertTrue(Lang::hasForLocale('surveys.d3_ui.'.$key, $locale));
            }
            $this->finish($survey, ['q1' => '4', 'q3' => 'Je dois obtenir l’accord d’un responsable', 'q4' => 'Très probable']);
            foreach (['plans', 'benefits'] as $kind) {
                foreach (['subject', 'body', 'cta'] as $key) {
                    $this->assertTrue(Lang::hasForLocale('d3-actions.'.$kind.'.'.$key, $locale));
                } $mail = new \App\Mail\D3BusinessMail($kind, $locale);
                $html = $mail->render();
                $this->assertStringContainsString(__('d3-actions.'.$kind.'.subject', [], $locale), $html);
                $this->assertStringNotContainsString('/questionnaires/', $html);
            }
        }
    }

    public function test_command_and_scheduler_prioritize_d3_without_changing_existing_commands(): void
    {
        $this->owner();
        $this->artisan('surveys:process-d3')->assertSuccessful();
        $this->artisan('surveys:process-d3')->assertSuccessful();
        $this->assertSame(1, SurveyResponse::count());
        $commands = array_values(array_map(fn ($e) => $e->command ?? '', app(Schedule::class)->events()));
        foreach (['d1', 'd2', 'd3'] as $type) {
            $this->assertCount(1, array_filter($commands, fn ($c) => str_contains($c, 'surveys:process-'.$type)));
        }
        Mail::assertSentCount(1);
    }

    public function test_concurrent_initial_final_and_business_actions_do_not_duplicate(): void
    {
        $owner = $this->owner();
        $results = $this->compete('process', (string) $owner->entreprise_id);
        $this->assertSame(1, array_sum(array_column($results, 'emails')));
        $survey = SurveyResponse::sole();
        $results = $this->compete('answer', $survey->id);
        $this->assertSame(1, array_sum(array_column($results, 'business_emails')));
        $this->compete('actions', $survey->id);
        $this->assertSame(1, Contact::count());
        $this->assertSame(1, $survey->actions()->where('action_type', SurveyActionType::AutoEmail)->count());
        $this->assertSame(1, $survey->actions()->where('action_type', SurveyActionType::PurchaseIntent)->count());
        $this->assertSame(1, $survey->actions()->where('action_type', SurveyActionType::Answered)->count());
        $survey->fresh()->contact->delete();
        $this->compete('actions', $survey->id);
        $this->assertSame(0, Contact::count());
    }

    public function test_manager_support_can_filter_view_reply_close_reopen_and_delete_d3_ticket(): void
    {
        $survey = $this->sent();
        $this->finish($survey, ['q1' => '2', 'q3' => 'Un problème technique']);
        $contact = $survey->fresh()->contact;
        $manager = Manager::create(['name' => 'Manager', 'email' => 'manager@example.test']);
        $this->actingAs($manager, 'manager')->get(route('manager.support.index', ['source' => ContactSource::D3Survey->value, 'priority' => 'normal']))
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
        app(D3ActionService::class)->process($survey);
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
        $this->post($this->link($survey), ['answers' => ['q1' => '4']])->assertStatus(419);
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
            throw new \RuntimeException('Simulated D3 action failure');
        });
        $this->app->instance(\App\Services\TicketService::class, $failing);
        $this->finish($survey, ['q1' => '2', 'q3' => 'Un problème technique', 'q4' => 'Très probable']);
        $this->assertNotNull($survey->fresh()->completed_at);
        $this->assertSame(3, $survey->answers()->count());
        $this->assertSame(0, Contact::count());
        $this->assertSame(0, $survey->actions()->where('action_key', D3ActionService::PROCESSED_KEY)->count());
        $this->app->instance(\App\Services\TicketService::class, $real);
        $this->artisan('surveys:process-d3')->assertSuccessful();
        $this->artisan('surveys:process-d3')->assertSuccessful();
        $this->assertSame(1, Contact::count());
        $this->assertSame('DT-000001', Contact::sole()->ticket_num);
        $this->assertSame(ContactPriority::Normal, Contact::sole()->priority);
    }

    public function test_all_five_scale_values_are_stored_as_exact_catalog_strings(): void
    {
        foreach (['1', '2', '3', '4', '5'] as $score) {
            $survey = $this->sent();
            $this->finish($survey, ['q1' => $score]);
            $this->assertSame($score, $survey->answers()->where('question_id', 'q1')->value('value'));
        }
    }

    public function test_unconfirmed_initial_send_is_reserved_once_and_never_blindly_retried(): void
    {
        $owner = $this->owner();
        Mail::swap(Mail::getFacadeRoot()->manager);
        \Illuminate\Support\Facades\Event::listen(\Illuminate\Mail\Events\MessageSending::class, fn () => false);
        $this->assertSame('unconfirmed', app(D3Lifecycle::class)->process($owner->entreprise_id));
        $this->travel(3)->days();
        $this->assertSame('already_attempted', app(D3Lifecycle::class)->process($owner->entreprise_id));
        $survey = SurveyResponse::sole();
        $this->assertNull($survey->sent_at);
        $this->assertSame(1, $survey->actions()->where('action_type', SurveyActionType::EmailUnconfirmed)->count());
    }

    private function compete(string $mode, string $parent): array
    {
        $processes = [];
        $start = (string) (microtime(true) + 1);
        try {
            for ($i = 0; $i < 2; $i++) {
                $process = proc_open([PHP_BINARY, base_path('tests/Support/survey-d3-worker.php'), $this->schema,
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
