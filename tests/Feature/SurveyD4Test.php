<?php

namespace Tests\Feature;

use App\Enums\ContactPriority;
use App\Enums\ContactSource;
use App\Enums\SurveyActionType;
use App\Enums\SurveyStatus;
use App\Enums\SurveyType;
use App\Mail\D4SurveyMail;
use App\Models\Contact;
use App\Models\Entreprise;
use App\Models\Manager;
use App\Models\SurveyAnswer;
use App\Models\SurveyResponse;
use App\Models\User;
use App\Services\ContactManagementService;
use App\Services\Surveys\D4ActionService;
use App\Services\Surveys\D4Eligibility;
use App\Services\Surveys\D4Lifecycle;
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

class SurveyD4Test extends TestCase
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
        (require database_path('migrations/2026_10_30_100005_add_d4_signal_action_types.php'))->up();
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
        $company = Entreprise::create(['name' => 'D4 company', 'trial_ends_at' => now('UTC')->subHours(24)]);
        $company->forceFill(['created_at' => now('UTC')->subYears(2)])->save();
        $user = User::create(['name' => 'Trial owner', 'email' => 'owner'.$company->id.'@example.test',
            'entreprise_id' => $company->id, 'locale' => $locale]);
        $user->forceFill(['created_at' => now('UTC')->subDays(3), 'email_verified_at' => now('UTC')->subDay()])->save();

        return $user;
    }

    private function sent(string $locale = 'fr'): SurveyResponse
    {
        $owner = $this->owner($locale);
        $this->assertSame('sent', app(D4Lifecycle::class)->process($owner->entreprise_id));

        return SurveyResponse::where('entreprise_id', $owner->entreprise_id)->where('survey_type', SurveyType::D4)->sole();
    }

    private function link(SurveyResponse $survey, string $choice = 'Je n’ai pas eu le temps de tester suffisamment', string $locale = 'fr'): string
    {
        return app(SurveyLink::class)->url($survey, $locale, $choice);
    }

    private function finish(SurveyResponse $survey, array $answers): void
    {
        $this->post($this->link($survey), ['intent' => 'complete', 'answers' => $answers])->assertOk();
    }

    private function pending(User $owner): SurveyResponse
    {
        return SurveyResponse::create(['entreprise_id' => $owner->entreprise_id, 'user_id' => $owner->id, 'email' => $owner->email, 'survey_type' => SurveyType::D4, 'survey_version' => 1]);
    }

    private function subscribe(SurveyResponse $survey): void
    {
        DB::table('subscriptions')->insert(['entreprise_id' => $survey->entreprise_id, 'stripe_status' => 'active']);
    }

    public function test_eligibility_at_exactly_24_hours_ignores_company_creation(): void
    {
        $owner = $this->owner();
        $company = $owner->entreprise;
        foreach ([null, now('UTC')->addDay(), now('UTC'), now('UTC')->subHours(23), now('UTC')->subHours(24)->addSecond()] as $end) {
            $company->update(['trial_ends_at' => $end]);
            $this->assertSame('ineligible', app(D4Lifecycle::class)->process($company->id));
        }
        $this->assertSame(0, SurveyResponse::count());
        $company->update(['trial_ends_at' => now('UTC')->subHours(24)]);
        $this->assertTrue(app(D4Eligibility::class)->eligible($company->id));
        $this->assertSame('sent', app(D4Lifecycle::class)->process($company->id));
        $this->assertSame('waiting', app(D4Lifecycle::class)->process($company->id));
        $this->assertSame('once', SurveyResponse::sole()->cycle_key);
        Mail::assertSentCount(1);
    }

    public function test_existing_plan_or_relevant_subscription_blocks_d4_and_cancelled_row_does_not_hide_active(): void
    {
        $owner = $this->owner();
        $company = $owner->entreprise;
        $company->update(['type_compt' => 'solo']);
        $this->assertFalse(app(D4Eligibility::class)->eligible($company->id));
        $company->update(['type_compt' => null]);
        foreach (['active', 'trialing', 'past_due', 'unpaid'] as $status) {
            DB::table('subscriptions')->delete();
            DB::table('subscriptions')->insert(['entreprise_id' => $company->id, 'stripe_status' => $status]);
            $this->assertSame('ineligible', app(D4Lifecycle::class)->process($company->id));
        }
        DB::table('subscriptions')->insert(['entreprise_id' => $company->id, 'stripe_status' => 'canceled']);
        $this->assertFalse(app(D4Eligibility::class)->eligible($company->id));
        DB::table('subscriptions')->update(['ends_at' => now('UTC')->subSecond()]);
        $this->assertTrue(app(D4Eligibility::class)->eligible($company->id));
        $this->assertSame(0, SurveyResponse::count());
        Mail::assertNothingSent();
    }

    public function test_global_survey_stop_and_unverified_or_invalid_recipient_block_send(): void
    {
        $owner = $this->owner();
        $owner->forceFill(['email_verified_at' => null])->save();
        $this->assertFalse(app(D4Eligibility::class)->eligible($owner->entreprise_id));
        $owner->forceFill(['email_verified_at' => now('UTC'), 'email' => 'invalid'])->save();
        $this->assertFalse(app(D4Eligibility::class)->eligible($owner->entreprise_id));
        $owner->update(['email' => 'valid@example.test']);
        $stop = SurveyResponse::create(['entreprise_id' => $owner->entreprise_id, 'email' => $owner->email, 'survey_type' => SurveyType::D1, 'survey_version' => 1]);
        $stop->answers()->create(['question_id' => 'q1', 'value' => 'abandon']);
        $this->assertSame('ineligible', app(D4Lifecycle::class)->process($owner->entreprise_id));
        Mail::assertNothingSent();
    }

    public function test_72_hour_gate_defers_without_duplicate_and_stops_only_unfinished_d3(): void
    {
        $owner = $this->owner();
        $d3 = SurveyResponse::create(['entreprise_id' => $owner->entreprise_id, 'email' => $owner->email, 'survey_type' => SurveyType::D3, 'survey_version' => 1, 'sent_at' => now('UTC')->subDays(2)]);
        $d3->answers()->create(['question_id' => 'q1', 'value' => '4']);
        $this->assertSame('rate_limited', app(D4Lifecycle::class)->process($owner->entreprise_id));
        $this->assertSame(SurveyStatus::Cancelled, $d3->fresh()->status);
        $this->assertSame('4', $d3->answers()->sole()->value);
        $this->assertSame(0, SurveyResponse::where('survey_type', SurveyType::D4)->count());
        $this->assertSame('closed', app(\App\Services\Surveys\D3Lifecycle::class)->process($owner->entreprise_id));
        $this->travel(1)->days();
        $this->assertSame('sent', app(D4Lifecycle::class)->process($owner->entreprise_id));
        $this->assertSame('waiting', app(D4Lifecycle::class)->process($owner->entreprise_id));
        $this->assertSame(1, $d3->actions()->where('action_key', 'cancel:trial_ended')->count());
        Mail::assertSentCount(1);
    }

    public function test_completed_d3_history_is_untouched(): void
    {
        $owner = $this->owner();
        $d3 = SurveyResponse::create(['entreprise_id' => $owner->entreprise_id, 'email' => $owner->email, 'survey_type' => SurveyType::D3, 'survey_version' => 1, 'status' => SurveyStatus::Answered, 'completed_at' => now('UTC')->subDays(4)]);
        $d3->answers()->create(['question_id' => 'q1', 'value' => '4']);
        $d3->actions()->create(['action_type' => SurveyActionType::Answered, 'action_key' => 'answered:final']);
        $before = $d3->fresh()->toArray();
        $this->assertSame('sent', app(D4Lifecycle::class)->process($owner->entreprise_id));
        $this->assertSame($before, $d3->fresh()->toArray());
        $this->assertSame(1, $d3->answers()->count());
        $this->assertSame(1, $d3->actions()->count());
    }

    public function test_subscription_after_creation_before_send_cancels_without_reactivation(): void
    {
        $survey = $this->pending($this->owner());
        $this->subscribe($survey);
        $this->assertSame('cancelled', app(D4Lifecycle::class)->process($survey->entreprise_id));
        $this->assertSame(SurveyStatus::Cancelled, $survey->fresh()->status);
        $this->assertNull($survey->fresh()->sent_at);
        DB::table('subscriptions')->delete();
        $this->assertSame('closed', app(D4Lifecycle::class)->process($survey->entreprise_id));
        $this->assertSame(1, SurveyResponse::count());
        Mail::assertNothingSent();
    }

    public function test_purchase_after_send_closes_old_link_without_answers_or_actions_and_keeps_history(): void
    {
        $survey = $this->sent();
        $before = $survey->fresh()->toArray();
        $actions = $survey->actions()->count();
        $this->subscribe($survey);
        $this->get($this->link($survey))->assertStatus(410);
        $this->post($this->link($survey), ['answers' => ['q1' => 'La prise en main était trop difficile', 'q4' => 'Oui']])->assertStatus(410);
        $this->assertSame($before, $survey->fresh()->toArray());
        $this->assertSame(0, $survey->answers()->count());
        $this->assertSame($actions, $survey->actions()->count());
        $this->assertSame(0, Contact::count());
        $this->artisan('surveys:process-d4')->assertSuccessful();
        $this->assertSame(SurveyStatus::Cancelled, $survey->fresh()->status);
        $this->assertNotNull($survey->fresh()->sent_at);
        DB::table('subscriptions')->delete();
        $this->artisan('surveys:process-d4')->assertSuccessful();
        $this->assertSame(SurveyStatus::Cancelled, $survey->fresh()->status);
        Mail::assertSentCount(1);
    }

    public function test_direct_answer_service_persists_cancellation_after_conversion(): void
    {
        $survey = $this->sent();
        $survey->entreprise->update(['type_compt' => 'solo', 'trial_ends_at' => null]);
        try {
            app(\App\Services\Surveys\D4AnswerService::class)->submit($survey, ['q1' => 'La prise en main était trop difficile'], false);
            $this->fail('Expected obsolete survey rejection');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertSame(410, $e->getStatusCode());
        }
        $this->assertSame(SurveyStatus::Cancelled, $survey->fresh()->status);
        $this->assertSame(0, $survey->answers()->count());
        $this->assertSame(0, Contact::count());
    }

    public function test_conversion_before_action_retry_blocks_contact_and_signals_preserving_answers(): void
    {
        $survey = $this->sent();
        $mock = \Mockery::mock(D4ActionService::class);
        $mock->shouldReceive('processSafely')->once()->andReturn(true);
        $this->app->instance(D4ActionService::class, $mock);
        $this->finish($survey, ['q1' => 'La prise en main était trop difficile', 'q3' => 'Davantage de temps pour tester', 'q4' => 'Oui']);
        $this->app->forgetInstance(D4ActionService::class);
        $this->subscribe($survey);
        app(D4ActionService::class)->process($survey);
        $this->assertSame(0, Contact::count());
        $this->assertSame(0, $survey->actions()->whereIn('action_type', [SurveyActionType::Ticket, SurveyActionType::TrialExtensionRequested])->count());
        $this->assertNotNull($survey->fresh()->completed_at);
        $this->assertSame(3, $survey->answers()->count());
        $this->assertSame(SurveyStatus::Cancelled, $survey->fresh()->status);
        $this->artisan('surveys:process-d4')->assertSuccessful();
        $this->assertSame(0, Contact::count());
    }

    public function test_trial_extension_by_manager_cancels_open_d4_without_changing_date_itself(): void
    {
        $survey = $this->sent();
        $end = now('UTC')->addDays(3);
        $survey->entreprise->update(['trial_ends_at' => $end]);
        $this->get($this->link($survey))->assertStatus(410);
        $this->assertSame('cancelled', app(D4Lifecycle::class)->process($survey->entreprise_id));
        $this->assertTrue($survey->entreprise->fresh()->trial_ends_at->equalTo($end));
    }

    public function test_email_has_nine_signed_choices_and_scanner_get_is_read_only(): void
    {
        $survey = $this->sent('en');
        Mail::assertSent(D4SurveyMail::class, function ($mail) use ($survey) {
            $html = $mail->render();
            $this->assertStringContainsString('trial has ended', $html);
            preg_match_all('/href="([^"]+)"/', $html, $matches);
            $this->assertCount(9, $matches[1]);
            foreach ($matches[1] as $url) {
                $r = \Illuminate\Http\Request::create(html_entity_decode($url));
                $this->assertTrue($r->hasValidSignature());
                $this->assertSame($survey->sent_at->addDays(15)->timestamp, (int) $r->query('expires'));
            }

            return true;
        });
        $before = $survey->fresh()->toArray();
        $actions = $survey->actions()->count();
        $this->get($this->link($survey, 'Autre (texte libre)'))->assertOk();
        $this->assertSame($before, $survey->fresh()->toArray());
        $this->assertSame($actions, $survey->actions()->count());
        $this->assertSame(0, SurveyAnswer::count());
    }

    public function test_q1_confirmation_is_immutable_and_finalization_is_unique(): void
    {
        $survey = $this->sent();
        $q1 = 'La prise en main était trop difficile';
        $url = $this->link($survey, $q1);
        $this->post($url, ['intent' => 'confirm_q1', 'answers' => ['q1' => $q1]])->assertOk();
        $this->post($url, ['intent' => 'confirm_q1', 'answers' => ['q1' => $q1]])->assertOk();
        $this->post($url, ['intent' => 'confirm_q1', 'answers' => ['q1' => 'Autre (texte libre)']])->assertConflict();
        $this->assertSame(0, Contact::count());
        $this->finish($survey, ['q1' => $q1, 'q4' => 'Oui']);
        $this->post($url, ['answers' => ['q1' => $q1]])->assertConflict();
        $this->assertSame(1, $survey->actions()->where('action_type', SurveyActionType::Q1Clicked)->count());
        $this->assertSame(1, $survey->actions()->where('action_type', SurveyActionType::Answered)->count());
        $this->assertSame(1, Contact::count());
    }

    public function test_other_text_condition_preview_validation_and_unicode_limit(): void
    {
        $survey = $this->sent();
        $this->post($this->link($survey), ['intent' => 'preview', 'answers' => ['q1' => 'Autre (texte libre)']])->assertOk()->assertViewHas('visible', fn ($ids) => $ids === ['q1', 'q1_autre', 'q2', 'q3', 'q4']);
        foreach ([[], ['q1' => 'invalid'], ['q1' => ['invalid']], ['q1' => 'Autre (texte libre)', 'q2' => 'invalid'], ['q1' => 'Autre (texte libre)', 'q3' => 'invalid'], ['q1' => 'Autre (texte libre)', 'q4' => 'invalid'], ['q1' => 'Autre (texte libre)', 'q1_autre' => str_repeat('é', 2001)]] as $answers) {
            $this->post($this->link($survey), ['answers' => $answers])->assertStatus(422);
        }
        $this->assertSame(0, SurveyAnswer::count());
        $this->finish($survey, ['q1' => 'Autre (texte libre)', 'q1_autre' => str_repeat('é', 2000), 'q2' => 'J’ai testé quelques fonctions', 'q4' => 'Non']);
        $this->assertSame(str_repeat('é', 2000), $survey->answers()->where('question_id', 'q1_autre')->value('value'));
        $this->assertSame(0, Contact::count());
    }

    public function test_hidden_other_answer_is_ignored_even_if_invalid(): void
    {
        $survey = $this->sent();
        $this->finish($survey, ['q1' => 'J’ai choisi une autre solution', 'q1_autre' => ['invalid']]);
        $this->assertSame(0, $survey->answers()->where('question_id', 'q1_autre')->count());
        $this->assertSame(1, $survey->answers()->count());
    }

    public function test_each_human_trigger_and_combination_creates_one_normal_contact(): void
    {
        foreach ([['La prise en main était trop difficile', 'Non'], ['Il manque une fonctionnalité essentielle', 'Non'], ['Je dois obtenir l’accord d’un décideur', 'Non'], ['Autre (texte libre)', 'Oui'], ['La prise en main était trop difficile', 'Oui']] as [$reason,$call]) {
            $survey = $this->sent();
            $this->finish($survey, ['q1' => $reason, 'q4' => $call]);
            $contact = $survey->fresh()->contact;
            $this->assertNotNull($contact);
            $this->assertSame(ContactPriority::Normal, $contact->priority);
            $this->assertSame(ContactSource::D4Survey, $contact->source);
            $this->assertSame($survey->user_id, $contact->user_id);
            $this->assertSame($survey->entreprise_id, $contact->entreprise_id);
            $this->assertStringNotContainsString($survey->token, $contact->message);
            $this->assertSame(1, $survey->actions()->where('action_type', SurveyActionType::Ticket)->count());
            if ($call === 'Oui') {
                $this->assertStringContainsString('20 minutes', $contact->message);
            }
            app(D4ActionService::class)->process($survey);
            $contact->delete();
            app(D4ActionService::class)->process($survey);
            $this->assertNull($survey->fresh()->contact_id);
        }$this->assertSame(0, Contact::count());
    }

    public function test_price_signal_reuses_d3_type_without_contact_or_business_email(): void
    {
        $survey = $this->sent();
        $this->finish($survey, ['q1' => 'Le prix des forfaits ne me convient pas']);
        $signal = $survey->actions()->where('action_type', SurveyActionType::PriceBarrier)->sole();
        $this->assertSame('D4:PRICE_BARRIER', $signal->action_key);
        $this->assertSame('frein_prix', json_decode($signal->detail, true)['signal']);
        app(D4ActionService::class)->process($survey);
        $this->assertSame(1, $survey->actions()->where('action_type', SurveyActionType::PriceBarrier)->count());
        $this->assertSame(0, Contact::count());
        Mail::assertSentCount(1);
    }

    public function test_time_and_timing_reuse_project_followup_without_contact(): void
    {
        foreach (['Je n’ai pas eu le temps de tester suffisamment', 'Ce n’était pas le bon moment pour mon entreprise'] as $reason) {
            $survey = $this->sent();
            $this->finish($survey, ['q1' => $reason]);
            app(D4ActionService::class)->process($survey);
            $signal = $survey->actions()->where('action_type', SurveyActionType::ProjectFollowUp)->sole();
            $this->assertSame($reason, json_decode($signal->detail, true)['reason']);
        }$this->assertSame(0, Contact::count());
        Mail::assertSentCount(2);
    }

    public function test_extension_request_is_signal_only_and_never_changes_trial_or_plan(): void
    {
        $survey = $this->sent();
        $end = $survey->entreprise->trial_ends_at;
        $this->finish($survey, ['q1' => 'Autre (texte libre)', 'q3' => 'Davantage de temps pour tester']);
        app(D4ActionService::class)->process($survey);
        $this->assertSame(1, $survey->actions()->where('action_type', SurveyActionType::TrialExtensionRequested)->count());
        $this->assertTrue($survey->entreprise->fresh()->trial_ends_at->equalTo($end));
        $this->assertNull($survey->entreprise->fresh()->type_compt);
        $this->assertSame(0, Contact::count());
        $this->assertSame(0, DB::table('subscriptions')->count());
        Mail::assertSentCount(1);
    }

    public function test_insufficient_value_and_competitor_each_have_one_explicit_signal_only(): void
    {
        foreach (['Je n’ai pas perçu assez de gain de temps' => SurveyActionType::InsufficientValue, 'J’ai choisi une autre solution' => SurveyActionType::CompetitorChosen] as $reason => $type) {
            $survey = $this->sent();
            $this->finish($survey, ['q1' => $reason]);
            app(D4ActionService::class)->process($survey);
            $this->assertSame(1, $survey->actions()->where('action_type', $type)->count());
        }$this->assertSame(0, Contact::count());
        Mail::assertSentCount(2);
    }

    public function test_signals_can_coexist_with_one_contact_and_neutral_answers_have_no_false_action(): void
    {
        $survey = $this->sent();
        $this->finish($survey, ['q1' => 'Le prix des forfaits ne me convient pas', 'q3' => 'Davantage de temps pour tester', 'q4' => 'Oui']);
        foreach ([SurveyActionType::PriceBarrier, SurveyActionType::TrialExtensionRequested, SurveyActionType::Ticket] as $type) {
            $this->assertSame(1, $survey->actions()->where('action_type', $type)->count());
        }
        $neutral = $this->sent();
        $this->finish($neutral, ['q1' => 'Autre (texte libre)', 'q4' => 'Peut-être']);
        $this->assertNull($neutral->fresh()->contact_id);
        $this->assertSame(1, Contact::count());
    }

    public function test_single_reminder_then_expiry_and_no_reminder_after_answer_or_purchase(): void
    {
        $survey = $this->sent();
        $expires = $survey->sent_at->addDays(15)->timestamp;
        $this->travel(3)->days();
        $this->assertSame('reminded', app(D4Lifecycle::class)->process($survey->entreprise_id));
        $this->assertSame('waiting', app(D4Lifecycle::class)->process($survey->entreprise_id));
        parse_str(parse_url($this->link($survey), PHP_URL_QUERY), $query);
        $this->assertSame($expires, (int) $query['expires']);
        $this->travel(10)->days();
        $this->assertSame('expired', app(D4Lifecycle::class)->process($survey->entreprise_id));
        $this->get($this->link($survey))->assertStatus(410);
        $answered = $this->sent();
        $this->finish($answered, ['q1' => 'Autre (texte libre)']);
        $purchase = $this->sent();
        $this->subscribe($purchase);
        $this->travel(3)->days();
        $this->assertSame('closed', app(D4Lifecycle::class)->process($answered->entreprise_id));
        $this->assertSame('cancelled', app(D4Lifecycle::class)->process($purchase->entreprise_id));
        $this->assertSame(0, $purchase->actions()->where('action_type', SurveyActionType::ReminderSent)->count());
    }

    public function test_reminder_gate_can_defer_until_day_five_and_never_later(): void
    {
        $survey = $this->sent();
        $this->travel(2)->days();
        SurveyResponse::create(['entreprise_id' => $survey->entreprise_id, 'email' => $survey->email, 'survey_type' => SurveyType::D1, 'survey_version' => 1, 'sent_at' => now('UTC')]);
        $this->travel(1)->days();
        $this->assertSame('rate_limited', app(D4Lifecycle::class)->process($survey->entreprise_id));
        $this->travel(2)->days();
        $this->assertSame('reminded', app(D4Lifecycle::class)->process($survey->entreprise_id));
        $late = $this->sent();
        $this->travel(6)->days();
        $this->assertSame('waiting', app(D4Lifecycle::class)->process($late->entreprise_id));
        $this->travel(9)->days();
        $this->assertSame('expired', app(D4Lifecycle::class)->process($late->entreprise_id));
        $this->assertSame(0, $late->actions()->where('action_type', SurveyActionType::ReminderSent)->count());
    }

    public function test_translations_cover_all_questions_choices_and_ui_without_fallback(): void
    {
        foreach (['fr', 'en', 'nl', 'es'] as $locale) {
            $survey = $this->sent($locale);
            $this->get($this->link($survey, 'Autre (texte libre)', $locale))->assertOk()->assertSee(__('surveys.d4_ui.title', [], $locale));
            foreach (app(SurveyCatalog::class)->definition(SurveyType::D4)['questions'] as $q) {
                $this->assertTrue(Lang::hasForLocale($q['label_key'], $locale));
                foreach ($q['choices'] as $c) {
                    $this->assertTrue(Lang::hasForLocale($c['label_key'], $locale));
                }
            }
            foreach (array_keys(Lang::get('surveys.ui', [], 'fr')) as $key) {
                $this->assertTrue(Lang::hasForLocale('surveys.d4_ui.'.$key, $locale));
            }
            $mail = new D4SurveyMail($survey, $locale, true);
            $this->assertStringContainsString(__('surveys.d4_ui.email_reminder', [], $locale), $mail->render());
        }
    }

    public function test_scheduler_order_is_d4_d3_d2_d1_and_command_retries_once(): void
    {
        $this->owner();
        $this->artisan('surveys:process-d4')->assertSuccessful();
        $this->artisan('surveys:process-d4')->assertSuccessful();
        $this->assertSame(1, SurveyResponse::count());
        $commands = array_values(array_map(fn ($e) => $e->command ?? '', app(Schedule::class)->events()));
        $positions = [];
        foreach (['d4', 'd3', 'd2', 'd1'] as $type) {
            $matches = array_keys(array_filter($commands, fn ($c) => str_contains($c, 'surveys:process-'.$type)));
            $this->assertCount(1, $matches);
            $positions[] = $matches[0];
        }$sorted = $positions;
        sort($sorted);
        $this->assertSame($sorted, $positions);
        Mail::assertSentCount(1);
    }

    public function test_concurrent_creation_finalization_and_actions_are_unique_after_deletion(): void
    {
        $owner = $this->owner();
        $results = $this->compete('process', (string) $owner->entreprise_id);
        $this->assertSame(1, array_sum(array_column($results, 'emails')));
        $survey = SurveyResponse::sole();
        $this->compete('answer', $survey->id);
        $this->compete('actions', $survey->id);
        foreach ([SurveyActionType::Answered, SurveyActionType::Ticket, SurveyActionType::PriceBarrier, SurveyActionType::TrialExtensionRequested] as $type) {
            $this->assertSame(1, $survey->actions()->where('action_type', $type)->count());
        }$this->assertSame(1, Contact::count());
        $survey->fresh()->contact->delete();
        $this->compete('actions', $survey->id);
        $this->assertSame(0, Contact::count());
    }

    public function test_manager_support_can_filter_view_reply_close_reopen_and_delete_d4_ticket(): void
    {
        $survey = $this->sent();
        $this->finish($survey, ['q1' => 'La prise en main était trop difficile', 'q4' => 'Oui']);
        $contact = $survey->fresh()->contact;
        $manager = Manager::create(['name' => 'Manager', 'email' => 'manager@example.test']);
        $this->actingAs($manager, 'manager')->get(route('manager.support.index', ['source' => ContactSource::D4Survey->value, 'priority' => 'normal']))
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
        app(D4ActionService::class)->process($survey);
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
            throw new \RuntimeException('Simulated D4 action failure');
        });
        $this->app->instance(\App\Services\TicketService::class, $failing);
        $this->finish($survey, ['q1' => 'La prise en main était trop difficile', 'q3' => 'Davantage de temps pour tester', 'q4' => 'Oui']);
        $this->assertNotNull($survey->fresh()->completed_at);
        $this->assertSame(3, $survey->answers()->count());
        $this->assertSame(0, Contact::count());
        $this->assertSame(0, $survey->actions()->where('action_key', D4ActionService::PROCESSED_KEY)->count());
        $this->app->instance(\App\Services\TicketService::class, $real);
        $this->artisan('surveys:process-d4')->assertSuccessful();
        $this->artisan('surveys:process-d4')->assertSuccessful();
        $this->assertSame(1, Contact::count());
        $this->assertSame('DT-000001', Contact::sole()->ticket_num);
        $this->assertSame(ContactPriority::Normal, Contact::sole()->priority);
    }

    public function test_unconfirmed_initial_send_is_reserved_once_and_never_blindly_retried(): void
    {
        $owner = $this->owner();
        Mail::swap(Mail::getFacadeRoot()->manager);
        \Illuminate\Support\Facades\Event::listen(\Illuminate\Mail\Events\MessageSending::class, fn () => false);
        $this->assertSame('unconfirmed', app(D4Lifecycle::class)->process($owner->entreprise_id));
        $this->travel(3)->days();
        $this->assertSame('already_attempted', app(D4Lifecycle::class)->process($owner->entreprise_id));
        $survey = SurveyResponse::sole();
        $this->assertNull($survey->sent_at);
        $this->assertSame(1, $survey->actions()->where('action_type', SurveyActionType::EmailUnconfirmed)->count());
    }

    public function test_conversion_transaction_wins_before_concurrent_final_answers_and_blocks_actions(): void
    {
        $survey = $this->sent();
        $process = proc_open([PHP_BINARY, base_path('tests/Support/survey-d4-worker.php'), $this->schema, config('database.connections.pgsql.database'), (string) microtime(true), 'convert', (string) $survey->entreprise_id, now('UTC')->toIso8601String()],
            [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, base_path());
        $this->assertIsResource($process);
        fclose($pipes[0]);
        try {
            $this->assertSame('locked', trim(fgets($pipes[1])));
            $this->compete('answer', $survey->id);
            $out = stream_get_contents($pipes[1]);
            $err = stream_get_contents($pipes[2]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            $this->assertSame(0, proc_close($process), $err);
            $process = null;
            $this->assertSame('converted', json_decode($out, true, 512, JSON_THROW_ON_ERROR)['result']);
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
        $this->assertSame(SurveyStatus::Cancelled, $survey->fresh()->status);
        $this->assertSame(0, $survey->answers()->count());
        $this->assertSame(0, Contact::count());
        foreach ([SurveyActionType::Ticket, SurveyActionType::PriceBarrier, SurveyActionType::TrialExtensionRequested] as $type) {
            $this->assertSame(0, $survey->actions()->where('action_type', $type)->count());
        }
    }

    public function test_valid_signature_cannot_pair_one_survey_with_another_token(): void
    {
        $first = $this->sent();
        $second = $this->sent();
        $url = URL::temporarySignedRoute('surveys.d4', $first->sent_at->addDays(15), ['locale' => 'fr', 'survey' => $first->id, 'token' => $second->token]);
        $this->get($url)->assertForbidden();
        $this->post($url, ['answers' => ['q1' => 'Autre (texte libre)']])->assertForbidden();
        $this->assertSame(0, SurveyAnswer::count());
    }

    private function compete(string $mode, string $parent): array
    {
        $processes = [];
        $start = (string) (microtime(true) + 1);
        try {
            for ($i = 0; $i < 2; $i++) {
                $process = proc_open([PHP_BINARY, base_path('tests/Support/survey-d4-worker.php'), $this->schema,
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
