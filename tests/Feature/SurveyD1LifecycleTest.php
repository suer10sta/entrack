<?php

namespace Tests\Feature;

use App\Enums\SurveyActionType;
use App\Enums\SurveyStatus;
use App\Enums\SurveyType;
use App\Mail\D1SurveyMail;
use App\Models\Contact;
use App\Models\EmailSendsLog;
use App\Models\Entreprise;
use App\Models\SurveyAction;
use App\Models\SurveyAnswer;
use App\Models\SurveyResponse;
use App\Models\User;
use App\Services\Surveys\D1Eligibility;
use App\Services\Surveys\D1Lifecycle;
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

class SurveyD1LifecycleTest extends TestCase
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
                    $table->string('invoice_prefix')->default('F');
                    $table->string('invoice_number_format')->default('year_month_number');
                }
                if ($name === 'users') {
                    $table->foreignId('entreprise_id')->constrained('entreprises');
                    $table->timestamp('email_verified_at')->nullable();
                    $table->string('locale')->nullable();
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
        $company = Entreprise::create(['name' => 'D1 company']);
        $user = User::create(['name' => 'Owner', 'email' => 'owner'.$company->id.'@example.test',
            'entreprise_id' => $company->id, 'locale' => $locale]);
        $user->forceFill(['created_at' => now('UTC')->subDays(2), 'email_verified_at' => now('UTC')->subDay()])->save();

        return $user;
    }

    private function sent(string $locale = 'fr'): SurveyResponse
    {
        $owner = $this->owner($locale);
        $this->assertSame('sent', app(D1Lifecycle::class)->process($owner->entreprise_id));

        return SurveyResponse::where('entreprise_id', $owner->entreprise_id)->sole();
    }

    private function link(SurveyResponse $survey, ?string $q1 = 'connexion', string $locale = 'fr'): string
    {
        return app(SurveyLink::class)->url($survey, $locale, $q1);
    }

    public function test_eligibility_requires_24_hours_since_owner_verification_and_no_activity(): void
    {
        $owner = $this->owner();
        $eligibility = app(D1Eligibility::class);
        $this->assertTrue($eligibility->eligible($owner->entreprise_id));
        $owner->forceFill(['email_verified_at' => now('UTC')->subHours(23)])->save();
        $this->assertFalse($eligibility->eligible($owner->entreprise_id));
        $owner->forceFill(['email_verified_at' => null])->save();
        $this->assertFalse($eligibility->eligible($owner->entreprise_id));
        $this->assertSame('ineligible', app(D1Lifecycle::class)->process($owner->entreprise_id));
        $this->assertSame(0, SurveyResponse::count());
        Mail::assertNothingSent();
    }

    public function test_projects_employees_clients_and_timers_each_prevent_eligibility(): void
    {
        foreach (['projects', 'employees', 'clients', 'tasks_timer'] as $table) {
            $owner = $this->owner();
            DB::table($table)->insert(['entreprise_id' => $owner->entreprise_id, 'created_at' => now('UTC')]);
            $this->assertFalse(app(D1Eligibility::class)->eligible($owner->entreprise_id), $table);
            $this->assertSame('ineligible', app(D1Lifecycle::class)->process($owner->entreprise_id));
        }
        Mail::assertNothingSent();
    }

    public function test_only_the_automatic_owner_employee_is_excluded(): void
    {
        $owner = $this->owner();
        DB::table('employees')->insert(['entreprise_id' => $owner->entreprise_id, 'user_id' => $owner->id, 'created_at' => $owner->created_at]);
        $this->assertTrue(app(D1Eligibility::class)->eligible($owner->entreprise_id));
        DB::table('employees')->insert(['entreprise_id' => $owner->entreprise_id, 'user_id' => $owner->id, 'created_at' => now('UTC')]);
        $this->assertFalse(app(D1Eligibility::class)->eligible($owner->entreprise_id));
    }

    public function test_a_recently_added_employee_linked_to_owner_is_not_mistaken_for_automatic_seed(): void
    {
        $owner = $this->owner();
        DB::table('employees')->insert(['entreprise_id' => $owner->entreprise_id, 'user_id' => $owner->id, 'created_at' => now('UTC')]);
        $this->assertFalse(app(D1Eligibility::class)->eligible($owner->entreprise_id));
    }

    public function test_creation_is_once_and_does_not_create_contacts_or_contact_email_logs(): void
    {
        $survey = $this->sent();
        $this->assertSame('once', $survey->cycle_key);
        $this->assertSame('waiting', app(D1Lifecycle::class)->process($survey->entreprise_id));
        $this->assertSame(1, SurveyResponse::count());
        $this->assertSame(1, $survey->actions()->where('action_type', SurveyActionType::Sent)->count());
        $this->assertSame(1, $survey->actions()->where('action_type', SurveyActionType::EmailAttempted)->count());
        $this->assertSame(0, Contact::count());
        $this->assertSame(0, EmailSendsLog::count());
        Mail::assertSentCount(1);
    }

    public function test_global_email_gate_defers_creation_and_retries_after_exactly_three_days(): void
    {
        $owner = $this->owner();
        SurveyResponse::create(['entreprise_id' => $owner->entreprise_id, 'survey_type' => SurveyType::D2,
            'survey_version' => 1, 'email' => $owner->email, 'sent_at' => now('UTC')->subDays(2)]);
        $this->assertSame('rate_limited', app(D1Lifecycle::class)->process($owner->entreprise_id));
        $this->assertSame(0, SurveyResponse::where('survey_type', SurveyType::D1)->count());
        $this->travel(1)->days();
        $this->assertSame('sent', app(D1Lifecycle::class)->process($owner->entreprise_id));
        $this->assertSame(1, SurveyResponse::where('survey_type', SurveyType::D1)->count());
    }

    public function test_other_company_activity_and_email_do_not_affect_d1(): void
    {
        $other = $this->sent();
        DB::table('projects')->insert(['entreprise_id' => $other->entreprise_id]);
        $owner = $this->owner();
        $this->assertSame('sent', app(D1Lifecycle::class)->process($owner->entreprise_id));
    }

    public function test_email_uses_versioned_catalog_and_six_signed_q1_choices(): void
    {
        $survey = $this->sent('en');
        Mail::assertSent(D1SurveyMail::class, function ($mail) use ($survey) {
            $html = $mail->render();
            $this->assertStringContainsString(__('surveys.D1.questions.q1.label', [], 'en'), $html);
            $this->assertStringContainsString('I cannot log in', $html);
            $this->assertStringNotContainsString('Où en êtes-vous', $html);
            preg_match_all('/href="([^"]+)"/', $html, $links);
            $this->assertCount(6, $links[1]);
            foreach ($links[1] as $url) {
                $url = html_entity_decode($url);
                $request = \Illuminate\Http\Request::create($url);
                $this->assertTrue(URL::hasValidSignature($request));
                $this->assertSame($survey->sent_at->addDays(15)->timestamp, (int) $request->query('expires'));
                $this->assertStringNotContainsString(rawurlencode($survey->email), $url);
            }

            return $mail->hasTo($survey->email);
        });
    }

    public function test_scanner_get_prefills_q1_without_database_mutation(): void
    {
        $survey = $this->sent();
        $before = $survey->toArray();
        $events = SurveyAction::count();
        $this->get($this->link($survey))->assertOk()->assertSee('value="connexion" checked', false)
            ->assertHeader('Referrer-Policy', 'no-referrer');
        $this->assertSame(0, SurveyAnswer::count());
        $this->assertSame($events, SurveyAction::count());
        $this->assertSame($before, $survey->fresh()->toArray());
    }

    public function test_invalid_signature_token_id_type_and_expiry_are_rejected(): void
    {
        $survey = $this->sent();
        $url = $this->link($survey);
        $this->get(str_replace($survey->token, str_repeat('0', 64), $url))->assertForbidden()->assertSee('Lien invalide ou expiré.');
        $this->get(str_replace($survey->id, (string) Str::uuid(), $url))->assertForbidden();
        $this->get($url.'&extra=1')->assertForbidden();
        $other = SurveyResponse::create(['entreprise_id' => $survey->entreprise_id, 'survey_type' => SurveyType::D2,
            'survey_version' => 1, 'email' => $survey->email, 'sent_at' => now('UTC')]);
        $wrongTypeUrl = URL::temporarySignedRoute('surveys.d1', $other->sent_at->addDays(15), [
            'locale' => 'fr', 'survey' => $other->id, 'token' => $other->token,
        ]);
        $this->get($wrongTypeUrl)->assertForbidden();
        $this->travel(15)->days();
        $this->get($url)->assertStatus(410);
        $this->assertSame(0, SurveyAnswer::count());
    }

    public function test_cancelled_and_expired_surveys_are_closed(): void
    {
        foreach ([SurveyStatus::Cancelled, SurveyStatus::Expired] as $status) {
            $survey = $this->sent();
            $survey->update(['status' => $status]);
            $this->get($this->link($survey))->assertStatus(410)->assertSee('Cette enquête est clôturée.');
            $this->post($this->link($survey), ['answers' => ['q1' => 'connexion']])->assertStatus(410);
        }
    }

    public function test_q1_confirmation_is_idempotent_and_first_choice_cannot_be_replaced(): void
    {
        $survey = $this->sent();
        $url = $this->link($survey);
        $this->post($url, ['intent' => 'confirm_q1', 'answers' => ['q1' => 'connexion']])->assertOk();
        $clicked = $survey->fresh()->clicked_at;
        $this->post($url, ['intent' => 'confirm_q1', 'answers' => ['q1' => 'connexion']])->assertOk();
        $this->assertSame(SurveyStatus::Q1Clicked, $survey->fresh()->status);
        $this->assertEquals($clicked, $survey->fresh()->clicked_at);
        $this->assertSame(1, SurveyAnswer::count());
        $this->assertSame(1, $survey->actions()->where('action_type', SurveyActionType::Q1Clicked)->count());
        $this->post($url, ['intent' => 'confirm_q1', 'answers' => ['q1' => 'reussi']])->assertConflict();
        $this->get($this->link($survey, 'reussi'))->assertOk()->assertSee(__('surveys.ui.q1_locked'));
        $this->assertSame('connexion', $survey->answers()->sole()->value);
    }

    public function test_final_submission_saves_applicable_answers_and_rejects_replay_without_duplicate_actions(): void
    {
        $survey = $this->sent();
        $input = ['q1' => 'connexion', 'q2' => 'Un message d’erreur s’affiche', 'q2b' => '<script>alert(1)</script>',
            'q3' => 'Une assistance technique', 'q4' => 'Le prix ne me convient pas', 'q2_autre' => 'hidden'];
        $this->post($this->link($survey), ['intent' => 'complete', 'answers' => $input])->assertOk()->assertSee('Merci pour votre réponse');
        $this->assertSame(SurveyStatus::Answered, $survey->fresh()->status);
        $this->assertEquals(now('UTC'), $survey->fresh()->completed_at);
        $this->assertSame(['q1', 'q2', 'q2b', 'q3'], $survey->answers()->orderBy('question_id')->pluck('question_id')->all());
        $this->post($this->link($survey), ['intent' => 'complete', 'answers' => $input])->assertConflict();
        $this->assertSame(1, $survey->actions()->where('action_type', SurveyActionType::Answered)->count());
        $this->assertSame(1, Contact::count());
    }

    public function test_success_hides_q2_q2b_q3_and_q4_even_when_manually_posted(): void
    {
        $survey = $this->sent();
        $this->post($this->link($survey, 'reussi'), ['answers' => ['q1' => 'reussi', 'q2' => 'Un message d’erreur s’affiche',
            'q2b' => str_repeat('x', 2001), 'q3' => ['bad'], 'q4' => 'Autre']])->assertOk();
        $this->assertSame(['q1'], $survey->answers()->pluck('question_id')->all());
    }

    public function test_abandon_saves_reason_and_blocks_future_solicitations_with_one_acknowledgement(): void
    {
        $survey = $this->sent();
        $this->post($this->link($survey, 'abandon'), ['answers' => ['q1' => 'abandon', 'q4' => 'Je manque de temps',
            'q2' => 'Un message d’erreur s’affiche', 'q2b' => 'hidden', 'q3' => 'Une démonstration']])->assertOk();
        $this->assertSame(SurveyStatus::Abandon, $survey->fresh()->status);
        $this->assertNotNull($survey->fresh()->completed_at);
        $this->assertSame(['q1', 'q4'], $survey->answers()->orderBy('question_id')->pluck('question_id')->all());
        $this->travel(4)->days();
        $this->assertSame('closed', app(D1Lifecycle::class)->process($survey->entreprise_id));
        $this->assertTrue(app(D1Eligibility::class)->optedOut($survey->entreprise_id));
        Mail::assertSentCount(2);
        $this->assertSame(1, $survey->actions()->where('action_type', SurveyActionType::AutoEmail)->count());
        $this->assertSame(0, $survey->actions()->whereIn('action_type', [SurveyActionType::Ticket, SurveyActionType::StopCommercial])->count());
    }

    public function test_confirmed_abandon_without_final_submission_already_stops_reminders(): void
    {
        $survey = $this->sent();
        $this->post($this->link($survey, 'abandon'), ['intent' => 'confirm_q1', 'answers' => ['q1' => 'abandon']])->assertOk();
        $this->travel(4)->days();
        $this->assertSame('opted_out', app(D1Lifecycle::class)->process($survey->entreprise_id));
        Mail::assertSentCount(1);
    }

    public function test_missing_invalid_choices_arrays_and_overlong_text_are_rejected_transactionally(): void
    {
        $cases = [[], ['q1' => 'invented'], ['q1' => ['connexion']], ['q1' => 'connexion', 'q2' => 'invented'],
            ['q1' => 'connexion', 'q2' => 'Un message d’erreur s’affiche', 'q2b' => str_repeat('é', 2001)],
            ['q1' => 'connexion', 'q3' => ['Une démonstration']]];
        foreach ($cases as $input) {
            $survey = $this->sent();
            $this->post($this->link($survey), ['answers' => $input])->assertStatus(422);
            $this->assertSame(0, $survey->answers()->count());
            $this->assertNull($survey->fresh()->completed_at);
        }
    }

    public function test_optional_other_text_and_2000_unicode_characters_are_accepted(): void
    {
        $survey = $this->sent();
        $this->post($this->link($survey), ['answers' => ['q1' => 'connexion', 'q2' => 'Autre (texte libre)',
            'q2_autre' => str_repeat('é', 2000), 'q2b' => str_repeat('x', 2001)]])->assertOk();
        $this->assertSame(3, $survey->answers()->count());
    }

    public function test_preview_has_no_survey_mutation_and_renders_conditional_fields_without_javascript(): void
    {
        $survey = $this->sent();
        $this->post($this->link($survey), ['intent' => 'preview', 'answers' => [
            'q1' => 'connexion', 'q2' => 'Un message d’erreur s’affiche', 'q2b' => '<script>alert(1)</script>',
        ]])->assertOk()->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)->assertDontSee('<script>alert(1)</script>', false);
        $this->assertSame(0, SurveyAnswer::count());
        $this->assertNull($survey->fresh()->clicked_at);
    }

    public function test_centralized_lifecycle_cancels_after_any_real_activity_without_removing_history(): void
    {
        foreach (['projects', 'employees', 'clients', 'tasks_timer'] as $table) {
            $survey = $this->sent();
            DB::table($table)->insert(['entreprise_id' => $survey->entreprise_id, 'created_at' => now('UTC')]);
            $this->assertSame('cancelled', app(D1Lifecycle::class)->process($survey->entreprise_id));
            $this->assertSame(SurveyStatus::Cancelled, $survey->fresh()->status);
            $this->assertSame(1, $survey->actions()->where('action_type', SurveyActionType::Cancelled)->count());
            $this->assertNotNull($survey->fresh()->sent_at);
        }
    }

    public function test_one_reminder_after_three_days_then_expiry_ten_days_later(): void
    {
        $survey = $this->sent();
        $originalUrl = $this->link($survey);
        $this->travel(3)->days();
        $this->assertSame('reminded', app(D1Lifecycle::class)->process($survey->entreprise_id));
        $this->assertSame(SurveyStatus::Relaunched, $survey->fresh()->status);
        $this->assertSame('waiting', app(D1Lifecycle::class)->process($survey->entreprise_id));
        $this->assertSame(1, $survey->actions()->where('action_type', SurveyActionType::ReminderSent)->count());
        $this->travel(10)->days();
        $this->assertSame('expired', app(D1Lifecycle::class)->process($survey->entreprise_id));
        $this->assertSame(SurveyStatus::Expired, $survey->fresh()->status);
        $this->get($originalUrl)->assertStatus(410);
        Mail::assertSentCount(2);
    }

    public function test_no_reminder_after_final_answer_or_after_window_and_link_expires_without_reminder(): void
    {
        $survey = $this->sent();
        $this->post($this->link($survey), ['answers' => ['q1' => 'connexion']])->assertOk();
        $this->travel(3)->days();
        $this->assertSame('closed', app(D1Lifecycle::class)->process($survey->entreprise_id));
        $other = $this->sent();
        $this->travel(6)->days();
        $this->assertSame('waiting', app(D1Lifecycle::class)->process($other->entreprise_id));
        $this->travel(9)->days();
        $this->assertSame('expired', app(D1Lifecycle::class)->process($other->entreprise_id));
        Mail::assertSentCount(2);
    }

    public function test_future_d2_can_suppress_d1_reminder_without_implementing_d2(): void
    {
        $survey = $this->sent();
        app(D1Lifecycle::class)->suppressReminder($survey);
        app(D1Lifecycle::class)->suppressReminder($survey);
        $this->travel(3)->days();
        $this->assertSame('waiting', app(D1Lifecycle::class)->process($survey->entreprise_id));
        $this->assertSame(1, $survey->actions()->where('action_key', 'reminder:suppressed')->count());
        $this->assertSame(0, SurveyResponse::where('survey_type', SurveyType::D2)->count());
        Mail::assertSentCount(1);
    }

    public function test_command_and_scheduler_add_only_one_d1_task_and_keep_existing_tasks(): void
    {
        $this->owner();
        $this->artisan('surveys:process-d1')->assertSuccessful();
        $this->assertSame(1, SurveyResponse::count());
        $events = app(Schedule::class)->events();
        $d1 = array_values(array_filter($events, fn ($event) => str_contains($event->command ?? '', 'surveys:process-d1')));
        $this->assertCount(1, $d1);
        $this->assertSame('0 * * * *', $d1[0]->expression);
        $this->assertTrue($d1[0]->withoutOverlapping);
        foreach (['crm:send-campaigns', 'reports:send-daily-tasks', 'tasks:check-running', 'subscriptions:send-trial-warnings'] as $command) {
            $this->assertNotEmpty(array_filter($events, fn ($event) => str_contains($event->command ?? '', $command)));
        }
    }

    public function test_all_d1_user_facing_keys_and_choices_have_real_translations_in_all_four_languages(): void
    {
        $catalog = app(SurveyCatalog::class);
        foreach (['fr', 'en', 'nl', 'es'] as $locale) {
            $survey = $this->sent($locale);
            $response = $this->get($this->link($survey, 'connexion', $locale));
            $response->assertOk()->assertSee(__('surveys.ui.submit', [], $locale));
            foreach ($catalog->definition(SurveyType::D1)['questions'] as $question) {
                $this->assertTrue(Lang::hasForLocale($question['label_key'], $locale));
                foreach ($question['choices'] as $choice) {
                    $this->assertTrue(Lang::hasForLocale($choice['label_key'], $locale));
                }
            }
            foreach (array_keys(Lang::get('surveys.ui', [], 'fr')) as $key) {
                $this->assertTrue(Lang::hasForLocale('surveys.ui.'.$key, $locale));
            }
            if ($locale !== 'fr') {
                $response->assertDontSee('Où en êtes-vous');
            }
        }
    }

    public function test_concurrent_d1_creation_and_reminder_each_deliver_only_one_email(): void
    {
        $owner = $this->owner();
        $results = $this->compete('process', (string) $owner->entreprise_id);
        $this->assertSame(1, array_sum(array_column($results, 'emails')));
        $this->assertSame(1, SurveyResponse::count());
        $survey = SurveyResponse::sole();
        $this->assertSame(1, $survey->actions()->where('action_type', SurveyActionType::Sent)->count());
        $this->travel(3)->days();
        $results = $this->compete('process', (string) $owner->entreprise_id);
        $this->assertSame(1, array_sum(array_column($results, 'emails')));
        $this->assertSame(1, $survey->actions()->where('action_type', SurveyActionType::ReminderSent)->count());
    }

    public function test_concurrent_final_submissions_commit_only_one_answer_and_one_final_receipt(): void
    {
        $survey = $this->sent();
        $results = $this->compete('answer', $survey->id);
        $states = array_column($results, 'result');
        sort($states);
        $this->assertSame(['answered', 'closed'], $states);
        $this->assertSame(1, $survey->answers()->count());
        $this->assertSame(1, $survey->actions()->where('action_type', SurveyActionType::Answered)->count());
        $this->assertSame(1, $survey->actions()->where('action_type', SurveyActionType::Q1Clicked)->count());
        $this->assertSame(1, Contact::count());
    }

    public function test_unconfirmed_delivery_has_durable_receipt_and_is_never_blindly_retried(): void
    {
        $owner = $this->owner();
        Mail::swap(Mail::getFacadeRoot()->manager);
        \Illuminate\Support\Facades\Event::listen(\Illuminate\Mail\Events\MessageSending::class, fn () => false);
        $this->assertSame('unconfirmed', app(D1Lifecycle::class)->process($owner->entreprise_id));
        $survey = SurveyResponse::sole();
        $this->assertNull($survey->sent_at);
        $this->assertSame(1, $survey->actions()->where('action_type', SurveyActionType::EmailUnconfirmed)->count());
        $this->assertSame(0, $survey->actions()->where('action_type', SurveyActionType::Sent)->count());
        $this->assertFalse(app(\App\Services\Surveys\SurveyEmailGate::class)->allows($owner->entreprise_id));
        $this->travel(4)->days();
        $this->assertSame('already_attempted', app(D1Lifecycle::class)->process($owner->entreprise_id));
        $this->assertSame(1, $survey->actions()->where('action_type', SurveyActionType::EmailAttempted)->count());
    }

    public function test_only_owner_verification_counts_and_recipient_changes_cancel_pending_d1(): void
    {
        $owner = $this->owner();
        $owner->forceFill(['email_verified_at' => null])->save();
        $employeeUser = User::create(['name' => 'Employee', 'email' => 'employee@example.test', 'entreprise_id' => $owner->entreprise_id]);
        $employeeUser->forceFill(['email_verified_at' => now('UTC')->subDays(2)])->save();
        $this->assertFalse(app(D1Eligibility::class)->eligible($owner->entreprise_id));
        $owner->forceFill(['email_verified_at' => now('UTC')->subDays(2)])->save();
        $this->assertSame('sent', app(D1Lifecycle::class)->process($owner->entreprise_id));
        $owner->update(['email' => 'changed@example.test']);
        $this->travel(3)->days();
        $this->assertSame('cancelled', app(D1Lifecycle::class)->process($owner->entreprise_id));
        Mail::assertSentCount(1);
    }

    public function test_reminder_checks_global_gate_and_can_be_deferred_within_five_day_window(): void
    {
        $survey = $this->sent();
        $this->travel(2)->days();
        SurveyResponse::create(['entreprise_id' => $survey->entreprise_id, 'survey_type' => SurveyType::D3,
            'survey_version' => 1, 'email' => $survey->email, 'sent_at' => now('UTC')]);
        $this->travel(1)->days();
        $this->assertSame('rate_limited', app(D1Lifecycle::class)->process($survey->entreprise_id));
        $this->travel(2)->days();
        $this->assertSame('reminded', app(D1Lifecycle::class)->process($survey->entreprise_id));
        Mail::assertSentCount(2);
    }

    public function test_valid_signature_does_not_authorize_wrong_token_or_wrong_survey_pair(): void
    {
        $first = $this->sent();
        $second = $this->sent();
        $wrong = URL::temporarySignedRoute('surveys.d1', now('UTC')->addDays(15), [
            'locale' => 'fr', 'survey' => $second->id, 'token' => $first->token,
        ]);
        $this->get($wrong)->assertForbidden();
        $this->actingAs($second->user)->get($this->link($first))->assertOk();
        $this->assertSame(0, SurveyAnswer::count());
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
        $this->post($this->link($survey), ['answers' => ['q1' => 'connexion']])->assertStatus(419);
        $this->assertSame(0, SurveyAnswer::count());
    }

    public function test_survey_dates_keep_their_instant_when_database_timezone_is_not_utc(): void
    {
        DB::statement("SET TIME ZONE 'Europe/Paris'");
        try {
            $survey = $this->sent();
            $this->assertSame(now('UTC')->timestamp, $survey->sent_at->timestamp);
            $this->travel(3)->days();
            $this->assertSame('reminded', app(D1Lifecycle::class)->process($survey->entreprise_id));
            $this->get($this->link($survey))->assertOk();
        } finally {
            DB::statement('RESET TIME ZONE');
        }
    }

    private function compete(string $mode, string $parent): array
    {
        $processes = [];
        $start = (string) (microtime(true) + 1);
        try {
            for ($i = 0; $i < 2; $i++) {
                $process = proc_open([PHP_BINARY, base_path('tests/Support/survey-d1-worker.php'), $this->schema,
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
