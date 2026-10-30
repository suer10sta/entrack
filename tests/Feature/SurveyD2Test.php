<?php

namespace Tests\Feature;

use App\Enums\ContactPriority;
use App\Enums\ContactSource;
use App\Enums\SurveyActionType;
use App\Enums\SurveyStatus;
use App\Enums\SurveyType;
use App\Mail\D2SurveyMail;
use App\Models\Contact;
use App\Models\Entreprise;
use App\Models\Manager;
use App\Models\SurveyAnswer;
use App\Models\SurveyResponse;
use App\Models\User;
use App\Services\ContactManagementService;
use App\Services\Surveys\D1Lifecycle;
use App\Services\Surveys\D2ActionService;
use App\Services\Surveys\D2Eligibility;
use App\Services\Surveys\D2Lifecycle;
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

class SurveyD2Test extends TestCase
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
        $company = Entreprise::create(['name' => 'D2 company', 'trial_ends_at' => now('UTC')->addDays(12)]);
        $company->forceFill(['created_at' => now('UTC')->subYears(2)])->save();
        $user = User::create(['name' => 'Trial owner', 'email' => 'owner'.$company->id.'@example.test',
            'entreprise_id' => $company->id, 'locale' => $locale]);
        $user->forceFill(['created_at' => now('UTC')->subDays(3), 'email_verified_at' => now('UTC')->subDay()])->save();

        return $user;
    }

    private function sent(string $locale = 'fr'): SurveyResponse
    {
        $owner = $this->owner($locale);
        $this->assertSame('sent', app(D2Lifecycle::class)->process($owner->entreprise_id));

        return SurveyResponse::where('entreprise_id', $owner->entreprise_id)->where('survey_type', SurveyType::D2)->sole();
    }

    private function link(SurveyResponse $survey, string $choice = 'usage_reel', string $locale = 'fr'): string
    {
        return app(SurveyLink::class)->url($survey, $locale, $choice);
    }

    private function finish(SurveyResponse $survey, array $answers): void
    {
        $this->post($this->link($survey), ['intent' => 'complete', 'answers' => $answers])->assertOk();
    }

    public function test_eligibility_uses_trial_end_minus_twelve_days_not_company_creation(): void
    {
        $owner = $this->owner();
        $company = $owner->entreprise;
        $this->assertTrue(app(D2Eligibility::class)->eligible($company->id));
        $company->update(['trial_ends_at' => now('UTC')->addDays(12)->addSecond()]);
        $this->assertFalse(app(D2Eligibility::class)->eligible($company->id));
        $this->assertSame('ineligible', app(D2Lifecycle::class)->process($company->id));
        $this->assertSame(0, SurveyResponse::count());
        foreach ([null, now('UTC'), now('UTC')->subDay()] as $end) {
            $company->update(['trial_ends_at' => $end]);
            $this->assertFalse(app(D2Eligibility::class)->eligible($company->id));
        }
        Mail::assertNothingSent();
    }

    public function test_selected_plan_or_live_local_subscription_prevents_d2_without_stripe_calls(): void
    {
        $owner = $this->owner();
        $owner->entreprise->update(['type_compt' => 'solo']);
        $this->assertSame('ineligible', app(D2Lifecycle::class)->process($owner->entreprise_id));
        $owner->entreprise->update(['type_compt' => null]);
        foreach (['active', 'trialing', 'past_due', 'unpaid'] as $status) {
            DB::table('subscriptions')->delete();
            DB::table('subscriptions')->insert(['entreprise_id' => $owner->entreprise_id, 'stripe_status' => $status]);
            $this->assertFalse(app(D2Eligibility::class)->eligible($owner->entreprise_id));
        }
        DB::table('subscriptions')->insert(['entreprise_id' => $owner->entreprise_id, 'stripe_status' => 'canceled', 'created_at' => now('UTC')]);
        $this->assertFalse(app(D2Eligibility::class)->eligible($owner->entreprise_id));
        DB::table('subscriptions')->update(['stripe_status' => 'canceled']);
        $this->assertTrue(app(D2Eligibility::class)->eligible($owner->entreprise_id));
        Mail::assertNothingSent();
    }

    public function test_unverified_owner_and_global_abandon_are_not_eligible(): void
    {
        $owner = $this->owner();
        $owner->forceFill(['email_verified_at' => null])->save();
        $this->assertFalse(app(D2Eligibility::class)->eligible($owner->entreprise_id));
        $owner->forceFill(['email_verified_at' => now('UTC')])->save();
        SurveyResponse::create(['entreprise_id' => $owner->entreprise_id, 'survey_type' => SurveyType::D1,
            'survey_version' => 1, 'email' => $owner->email, 'status' => SurveyStatus::Abandon]);
        $this->assertFalse(app(D2Eligibility::class)->eligible($owner->entreprise_id));
    }

    public function test_rate_limit_defers_creation_and_d1_suppression_until_dispatch(): void
    {
        $owner = $this->owner();
        $d1 = SurveyResponse::create(['entreprise_id' => $owner->entreprise_id, 'user_id' => $owner->id,
            'survey_type' => SurveyType::D1, 'survey_version' => 1, 'email' => $owner->email, 'sent_at' => now('UTC')->subDays(2)]);
        $this->assertSame('rate_limited', app(D2Lifecycle::class)->process($owner->entreprise_id));
        $this->assertSame(0, SurveyResponse::where('survey_type', SurveyType::D2)->count());
        $this->assertSame(0, $d1->actions()->count());
        $this->travel(1)->days();
        $this->assertSame('sent', app(D2Lifecycle::class)->process($owner->entreprise_id));
        $this->assertSame(1, $d1->actions()->where('action_key', 'reminder:suppressed')->count());
        $this->assertSame(SurveyStatus::Sent, $d1->fresh()->status);
        $this->assertNotNull($d1->fresh()->sent_at);
        $this->assertSame('waiting', app(D1Lifecycle::class)->process($owner->entreprise_id));
        $this->assertSame('once', SurveyResponse::where('survey_type', SurveyType::D2)->sole()->cycle_key);
        Mail::assertSentCount(1);
    }

    public function test_answered_d1_is_not_modified_when_d2_is_sent(): void
    {
        $owner = $this->owner();
        $d1 = SurveyResponse::create(['entreprise_id' => $owner->entreprise_id, 'survey_type' => SurveyType::D1,
            'survey_version' => 1, 'email' => $owner->email, 'status' => SurveyStatus::Answered, 'completed_at' => now('UTC')->subDay()]);
        $this->assertSame('sent', app(D2Lifecycle::class)->process($owner->entreprise_id));
        $this->assertSame(SurveyStatus::Answered, $d1->fresh()->status);
        $this->assertSame(0, $d1->actions()->count());
    }

    public function test_initial_send_is_unique_even_after_retry_and_does_not_create_contact(): void
    {
        $survey = $this->sent();
        $this->assertSame('waiting', app(D2Lifecycle::class)->process($survey->entreprise_id));
        $this->assertSame(1, SurveyResponse::count());
        $this->assertSame(1, $survey->actions()->where('action_type', SurveyActionType::Sent)->count());
        $this->assertSame(0, Contact::count());
        Mail::assertSent(D2SurveyMail::class, 1);
    }

    public function test_email_uses_five_catalog_choices_and_signed_links_with_original_expiry(): void
    {
        $survey = $this->sent('en');
        Mail::assertSent(D2SurveyMail::class, function ($mail) use ($survey) {
            $html = $mail->render();
            $this->assertStringContainsString('Have you started using DalyTrac?', $html);
            preg_match_all('/href="([^"]+)"/', $html, $matches);
            $this->assertCount(5, $matches[1]);
            foreach ($matches[1] as $link) {
                $request = \Illuminate\Http\Request::create(html_entity_decode($link));
                $this->assertTrue($request->hasValidSignature());
                $this->assertSame($survey->sent_at->addDays(15)->timestamp, (int) $request->query('expires'));
            }

            return true;
        });
    }

    public function test_get_is_read_only_and_post_confirmation_keeps_first_q1(): void
    {
        $survey = $this->sent();
        $before = $survey->toArray();
        $count = $survey->actions()->count();
        $url = $this->link($survey, 'blocage');
        $this->get($url)->assertOk()->assertSee('value="blocage" checked', false);
        $this->assertSame($before, $survey->fresh()->toArray());
        $this->assertSame($count, $survey->actions()->count());
        $this->post($url, ['intent' => 'confirm_q1', 'answers' => ['q1' => 'blocage']])->assertOk();
        $this->post($url, ['intent' => 'confirm_q1', 'answers' => ['q1' => 'blocage']])->assertOk();
        $this->post($url, ['intent' => 'confirm_q1', 'answers' => ['q1' => 'usage_reel']])->assertConflict();
        $this->assertSame(1, $survey->answers()->count());
        $this->assertSame(1, $survey->actions()->where('action_type', SurveyActionType::Q1Clicked)->count());
        $this->assertSame(0, Contact::count());
    }

    public function test_public_conditions_and_no_javascript_preview_use_persisted_catalog(): void
    {
        $survey = $this->sent();
        $this->post($this->link($survey), ['intent' => 'preview', 'answers' => ['q1' => 'usage_reel', 'q2' => 'Autre (texte libre)']])
            ->assertOk()->assertViewHas('visible', fn ($ids) => $ids === ['q1', 'q2', 'q2_autre', 'q3', 'q4']);
        $this->post($this->link($survey), ['intent' => 'preview', 'answers' => ['q1' => 'pas_donnees']])
            ->assertOk()->assertViewHas('visible', fn ($ids) => $ids === ['q1', 'q4']);
        $this->assertSame(0, $survey->answers()->count());
    }

    public function test_validation_rejects_bad_choices_missing_q1_arrays_and_overlong_text(): void
    {
        $survey = $this->sent();
        foreach ([[], ['q1' => 'invalid'], ['q1' => ['blocage']], ['q1' => 'usage_reel', 'q2' => 'invalid'],
            ['q1' => 'usage_reel', 'q3' => 'invalid'], ['q1' => 'usage_reel', 'q4' => 'invalid'],
            ['q1' => 'usage_reel', 'q2' => 'Autre (texte libre)', 'q2_autre' => str_repeat('é', 2001)]] as $answers) {
            $this->post($this->link($survey), ['answers' => $answers])->assertStatus(422);
            $this->assertSame(0, $survey->answers()->count());
        }
    }

    public function test_hidden_answers_are_ignored_and_full_submission_is_idempotent(): void
    {
        $survey = $this->sent();
        $answers = ['q1' => 'blocage', 'q2' => ['invalid'], 'q2_autre' => str_repeat('x', 2001), 'q3' => 'Oui, clairement'];
        $this->finish($survey, $answers);
        $this->post($this->link($survey), ['answers' => $answers])->assertConflict();
        $this->assertSame(['q1'], $survey->answers()->pluck('question_id')->all());
        $this->assertSame(SurveyStatus::Answered, $survey->fresh()->status);
        $this->assertNotNull($survey->fresh()->completed_at);
        $this->assertSame(1, $survey->actions()->where('action_type', SurveyActionType::Answered)->count());
        $this->assertSame(1, Contact::count());
    }

    public function test_ticket_rules_consolidate_with_correct_source_priority_and_identity(): void
    {
        foreach ([['blocage', null, ContactPriority::High], ['decouverte', 'Oui, une courte démonstration', ContactPriority::Normal],
            ['usage_reel', 'Oui, un guide ciblé', ContactPriority::Normal], ['blocage', 'Oui, une courte démonstration', ContactPriority::High]] as $i => [$q1, $q4, $priority]) {
            $survey = $this->sent();
            $this->finish($survey, array_filter(['q1' => $q1, 'q4' => $q4], fn ($v) => $v !== null));
            $contact = $survey->fresh()->contact;
            $this->assertNotNull($contact);
            $this->assertSame(ContactSource::D2Survey, $contact->source);
            $this->assertSame($priority, $contact->priority);
            $this->assertSame($survey->user_id, $contact->user_id);
            $this->assertSame($survey->entreprise_id, $contact->entreprise_id);
            $this->assertSame($survey->email, $contact->email);
            $this->assertSame($i + 1, Contact::count());
            $this->assertSame(1, $survey->actions()->where('action_type', SurveyActionType::Ticket)->count());
            $this->assertStringNotContainsString($survey->token, $contact->message);
            $this->assertStringNotContainsString($survey->token, json_encode($contact->events()->sole()->metadata));
            if ($q1 === 'blocage' && $q4) {
                $this->assertStringContainsString('Blocage', $contact->message);
                $this->assertStringContainsString('démonstration', $contact->message);
            }
        }
        Mail::assertSentCount(4);
    }

    public function test_project_follow_up_signals_have_no_contact_or_extra_email_and_do_not_repeat(): void
    {
        foreach (['pas_temps', 'pas_donnees'] as $q1) {
            $survey = $this->sent();
            $this->finish($survey, ['q1' => $q1]);
            app(D2ActionService::class)->process($survey);
            $signal = $survey->actions()->where('action_type', SurveyActionType::ProjectFollowUp)->sole();
            $this->assertSame($q1, json_decode($signal->detail, true)['reason']);
            $this->assertNull($survey->fresh()->contact_id);
        }
        Mail::assertSentCount(2);
        $this->assertSame(0, Contact::count());
    }

    public function test_real_use_and_yes_or_partial_result_record_value_only_once(): void
    {
        foreach (['Oui, clairement', 'En partie'] as $result) {
            $survey = $this->sent();
            $this->finish($survey, ['q1' => 'usage_reel', 'q2' => 'Le pointage des heures', 'q3' => $result]);
            app(D2ActionService::class)->process($survey);
            $this->assertSame(1, $survey->actions()->where('action_type', SurveyActionType::ValueConfirmed)->count());
            $this->assertSame(0, $survey->actions()->where('action_type', SurveyActionType::Ticket)->count());
        }
        $this->assertSame(0, Contact::count());
        $this->assertSame(0, SurveyResponse::where('survey_type', SurveyType::D3)->count());
        Mail::assertSentCount(2);
    }

    public function test_discovery_or_negative_result_does_not_create_false_value_or_contact(): void
    {
        foreach ([['q1' => 'decouverte', 'q3' => 'Oui, clairement'], ['q1' => 'usage_reel', 'q3' => 'Non'],
            ['q1' => 'usage_reel', 'q3' => 'Je n’ai pas encore assez testé']] as $answers) {
            $survey = $this->sent();
            $this->finish($survey, $answers);
            $this->assertSame(0, $survey->actions()->whereIn('action_type', [SurveyActionType::Ticket, SurveyActionType::ValueConfirmed])->count());
        }
    }

    public function test_contact_deletion_and_retry_do_not_recreate_ticket_or_signal(): void
    {
        $survey = $this->sent();
        $this->finish($survey, ['q1' => 'pas_temps', 'q4' => 'Oui, un guide ciblé']);
        $contact = $survey->fresh()->contact;
        $contact->delete();
        app(D2ActionService::class)->process($survey);
        $this->assertNull($survey->fresh()->contact_id);
        $this->assertSame(0, Contact::count());
        $this->assertSame(1, $survey->actions()->where('action_type', SurveyActionType::Ticket)->count());
        $this->assertSame(1, $survey->actions()->where('action_type', SurveyActionType::ProjectFollowUp)->count());
    }

    public function test_single_reminder_at_day_three_expires_ten_days_later_and_never_extends_link(): void
    {
        $survey = $this->sent();
        // A longer still-active trial fixture lets us reach the link lifecycle independently.
        $survey->entreprise->update(['trial_ends_at' => now('UTC')->addDays(20)]);
        $expires = $survey->sent_at->addDays(15)->timestamp;
        $this->travel(3)->days();
        $this->assertSame('reminded', app(D2Lifecycle::class)->process($survey->entreprise_id));
        $this->assertSame('waiting', app(D2Lifecycle::class)->process($survey->entreprise_id));
        parse_str(parse_url($this->link($survey), PHP_URL_QUERY), $query);
        $this->assertSame($expires, (int) $query['expires']);
        $this->travel(10)->days();
        $this->assertSame('expired', app(D2Lifecycle::class)->process($survey->entreprise_id));
        $this->get($this->link($survey))->assertStatus(410);
        $this->assertSame(1, $survey->actions()->where('action_type', SurveyActionType::ReminderSent)->count());
        Mail::assertSentCount(2);
    }

    public function test_answered_surveys_are_not_reminded_and_trial_end_cancels_open_survey(): void
    {
        $answered = $this->sent();
        $this->finish($answered, ['q1' => 'decouverte']);
        $open = $this->sent();
        $open->entreprise->update(['trial_ends_at' => now('UTC')->subSecond()]);
        $this->assertSame('cancelled', app(D2Lifecycle::class)->process($open->entreprise_id));
        $this->get($this->link($open))->assertStatus(410);
        $this->travel(3)->days();
        $this->assertSame('closed', app(D2Lifecycle::class)->process($answered->entreprise_id));
        Mail::assertSentCount(2);
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

    public function test_all_d2_keys_are_translated_without_fallback_in_four_locales(): void
    {
        foreach (['fr', 'en', 'nl', 'es'] as $locale) {
            $survey = $this->sent($locale);
            $this->get($this->link($survey, 'usage_reel', $locale))->assertOk()->assertSee(__('surveys.d2_ui.title', [], $locale));
            foreach (app(SurveyCatalog::class)->definition(SurveyType::D2)['questions'] as $question) {
                $this->assertTrue(Lang::hasForLocale($question['label_key'], $locale));
                foreach ($question['choices'] as $choice) {
                    $this->assertTrue(Lang::hasForLocale($choice['label_key'], $locale));
                }
            }
            foreach (array_keys(Lang::get('surveys.ui', [], 'fr')) as $key) {
                $this->assertTrue(Lang::hasForLocale('surveys.d2_ui.'.$key, $locale));
            }
        }
    }

    public function test_scheduler_runs_d2_before_d1_and_command_sends_once(): void
    {
        $this->owner();
        $this->artisan('surveys:process-d2')->assertSuccessful();
        $this->artisan('surveys:process-d2')->assertSuccessful();
        $this->assertSame(1, SurveyResponse::count());
        $commands = array_values(array_map(fn ($event) => $event->command ?? '', app(Schedule::class)->events()));
        $d1 = array_keys(array_filter($commands, fn ($command) => str_contains($command, 'surveys:process-d1')))[0];
        $d2 = array_keys(array_filter($commands, fn ($command) => str_contains($command, 'surveys:process-d2')))[0];
        $this->assertLessThan($d1, $d2);
        Mail::assertSentCount(1);
    }

    public function test_concurrent_creation_answers_and_actions_are_unique_even_after_contact_deletion(): void
    {
        $owner = $this->owner();
        $results = $this->compete('process', (string) $owner->entreprise_id);
        $this->assertSame(1, array_sum(array_column($results, 'emails')));
        $survey = SurveyResponse::sole();
        $this->compete('answer', $survey->id);
        $this->assertNotNull($survey->fresh()->completed_at);
        $this->assertSame(2, $survey->answers()->count());
        $this->compete('actions', $survey->id);
        $this->assertSame(1, Contact::count());
        $this->assertSame(1, $survey->actions()->where('action_type', SurveyActionType::Ticket)->count());
        $this->assertSame(1, $survey->actions()->where('action_type', SurveyActionType::ProjectFollowUp)->count());
        $survey->fresh()->contact->delete();
        $this->compete('actions', $survey->id);
        $this->assertSame(0, Contact::count());
    }

    public function test_action_failure_preserves_answers_and_command_retries_without_duplicate_number(): void
    {
        $survey = $this->sent();
        $real = new \App\Services\TicketService;
        $failing = \Mockery::mock(\App\Services\TicketService::class);
        $failing->shouldReceive('create')->once()->andReturnUsing(function (...$args) use ($real) {
            $real->create(...$args);
            throw new \RuntimeException('Simulated D2 action failure');
        });
        $this->app->instance(\App\Services\TicketService::class, $failing);
        $this->finish($survey, ['q1' => 'blocage', 'q2' => 'Autre', 'q2_autre' => 'Erreur précise', 'q4' => 'Oui, un guide ciblé']);
        $this->assertNotNull($survey->fresh()->completed_at);
        $this->assertSame(2, $survey->answers()->count());
        $this->assertSame(0, Contact::count());
        $this->assertSame(0, $survey->actions()->where('action_key', D2ActionService::PROCESSED_KEY)->count());
        $this->app->instance(\App\Services\TicketService::class, $real);
        $this->artisan('surveys:process-d2')->assertSuccessful();
        $this->artisan('surveys:process-d2')->assertSuccessful();
        $this->assertSame(1, Contact::count());
        $this->assertSame('DT-000001', Contact::sole()->ticket_num);
        $this->assertSame(ContactPriority::High, Contact::sole()->priority);
    }

    public function test_shared_gate_can_defer_d2_reminder_until_day_five(): void
    {
        $survey = $this->sent();
        $this->travel(2)->days();
        SurveyResponse::create(['entreprise_id' => $survey->entreprise_id, 'user_id' => $survey->user_id,
            'email' => $survey->email, 'survey_type' => SurveyType::D1, 'survey_version' => 1,
            'cycle_key' => 'once', 'sent_at' => now('UTC')]);
        $this->travel(1)->days();
        $this->assertSame('rate_limited', app(D2Lifecycle::class)->process($survey->entreprise_id));
        $this->travel(2)->days();
        $this->assertSame('reminded', app(D2Lifecycle::class)->process($survey->entreprise_id));
        $this->assertSame(1, $survey->actions()->where('action_type', SurveyActionType::ReminderSent)->count());
        Mail::assertSentCount(2);
    }

    public function test_manager_support_can_filter_view_reply_close_reopen_and_delete_d2_ticket(): void
    {
        $survey = $this->sent();
        $this->finish($survey, ['q1' => 'blocage', 'q4' => 'Oui, une courte démonstration']);
        $contact = $survey->fresh()->contact;
        $manager = Manager::create(['name' => 'Manager', 'email' => 'manager@example.test']);
        $this->actingAs($manager, 'manager')->get(route('manager.support.index', ['source' => ContactSource::D2Survey->value, 'priority' => 'high']))
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
        app(D2ActionService::class)->process($survey);
        $this->assertSame(0, Contact::count());
    }

    public function test_unconfirmed_send_is_not_blindly_retried_or_counted_as_sent(): void
    {
        $owner = $this->owner();
        Mail::swap(Mail::getFacadeRoot()->manager);
        \Illuminate\Support\Facades\Event::listen(\Illuminate\Mail\Events\MessageSending::class, fn () => false);
        $this->assertSame('unconfirmed', app(D2Lifecycle::class)->process($owner->entreprise_id));
        $survey = SurveyResponse::sole();
        $this->assertNull($survey->sent_at);
        $this->travel(3)->days();
        $this->assertSame('already_attempted', app(D2Lifecycle::class)->process($owner->entreprise_id));
        $this->assertSame(1, $survey->actions()->where('action_type', SurveyActionType::EmailUnconfirmed)->count());
        $this->assertSame(0, $survey->actions()->where('action_type', SurveyActionType::Sent)->count());
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
        $this->post($this->link($survey), ['answers' => ['q1' => 'blocage']])->assertStatus(419);
        $this->assertSame(0, SurveyAnswer::count());
    }

    public function test_two_thousand_unicode_characters_are_accepted_for_applicable_other_text(): void
    {
        $survey = $this->sent();
        $text = str_repeat('é', 2000);
        $this->finish($survey, ['q1' => 'usage_reel', 'q2' => 'Autre (texte libre)', 'q2_autre' => $text]);
        $this->assertSame($text, $survey->answers()->where('question_id', 'q2_autre')->sole()->value);
    }

    private function compete(string $mode, string $parent): array
    {
        $processes = [];
        $start = (string) (microtime(true) + 1);
        try {
            for ($i = 0; $i < 2; $i++) {
                $process = proc_open([PHP_BINARY, base_path('tests/Support/survey-d2-worker.php'), $this->schema,
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
}
