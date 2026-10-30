<?php

namespace Tests\Feature;

use App\Enums\ContactSource;
use App\Enums\SurveyType;
use App\Models\Contact;
use App\Models\Entreprise;
use App\Models\Manager;
use App\Models\SurveyAnswer;
use App\Models\SurveyResponse;
use App\Models\User;
use App\Services\Surveys\SurveyCatalog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class ManagerQuestionnairesTest extends TestCase
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

    private function fixture(string $type = 'D1', string $status = 'sent'): SurveyResponse
    {
        $company = Entreprise::create(['name' => 'Acme '.Str::random(6)]);
        $user = User::create(['name' => 'Client', 'email' => Str::random(8).'@example.test', 'entreprise_id' => $company->id]);

        return SurveyResponse::create(['survey_type' => $type, 'survey_version' => 1, 'entreprise_id' => $company->id, 'user_id' => $user->id, 'email' => $user->email, 'status' => $status, 'sent_at' => now()->subDays(2), 'completed_at' => $status === 'answered' ? now() : null]);
    }

    private function manager(): void
    {
        $this->actingAs(Manager::create(['name' => 'Manager', 'email' => 'manager@example.test']), 'manager');
        app()->setLocale('fr');
    }

    public function test_access_requires_manager_and_all_six_catalogues_render(): void
    {
        $this->getJson('/manager/questionnaires')->assertUnauthorized();
        $this->manager();
        foreach (SurveyType::cases() as $type) {
            $response = $this->get('/manager/questionnaires?type='.$type->value);
            if (getenv('MANAGER_SURVEY_QA_DIR')) {
                file_put_contents(getenv('MANAGER_SURVEY_QA_DIR').'/'.$type->value.'.html', $response->getContent());
            }
            $response->assertOk()->assertSee('Aperçu du questionnaire envoyé')->assertSee('Questionnaire '.substr($type->value, 1))->assertDontSee('Version 1')->assertViewHas('sent', 0);
            $catalog = app(SurveyCatalog::class);
            foreach ($catalog->definition($type)['questions'] as $question) {
                $response->assertSee($catalog->label($question['label_key'], 'fr'));
                foreach ($question['choices'] as $choice) {
                    $response->assertSee($catalog->label($choice['label_key'], 'fr'));
                }
            }
        }
    }

    public function test_metrics_filters_statuses_and_conditional_responses(): void
    {
        $this->manager();
        $survey = $this->fixture('D1', 'answered');
        SurveyAnswer::create(['survey_id' => $survey->id, 'question_id' => 'q1', 'value' => 'reussi']);
        SurveyAnswer::create(['survey_id' => $survey->id, 'question_id' => 'q2', 'value' => 'SECRET HIDDEN CONDITIONAL']);
        $this->fixture('D1', 'expired');
        $this->fixture('D1', 'cancelled');
        $this->fixture('D2');
        $this->get('/manager/questionnaires')->assertOk()->assertViewHas('sent', 3)->assertViewHas('answered', 1)->assertViewHas('rate', 33.3)->assertDontSee('SECRET HIDDEN CONDITIONAL');
        $this->get('/manager/questionnaires?status=expired')->assertOk()->assertViewHas('sent', 1);
        $this->get('/manager/questionnaires?answers[q1]=reussi')->assertOk()->assertViewHas('sent', 1);
        $this->get('/manager/questionnaires?q='.urlencode($survey->entreprise->name))->assertOk()->assertViewHas('sent', 1);
        $this->get('/manager/questionnaires?q=not-found')->assertOk()->assertViewHas('sent', 0);
        $this->get('/manager/questionnaires/'.$survey->id)->assertOk()->assertDontSee('SECRET HIDDEN CONDITIONAL')->assertDontSee($survey->token);
        $this->getJson('/manager/questionnaires?answers[q1]=invalid')->assertUnprocessable();
        $this->getJson('/manager/questionnaires?type=D7')->assertUnprocessable();
    }

    public function test_pagination_export_and_bounded_queries(): void
    {
        $this->manager();
        $first = $this->fixture('D2');
        DB::enableQueryLog();
        $this->get('/manager/questionnaires?type=D2')->assertOk();
        $single = count(DB::getQueryLog());
        DB::disableQueryLog();
        for ($i = 0; $i < 26; $i++) {
            $this->fixture('D2');
        }
        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->get('/manager/questionnaires?type=D2')->assertOk()->assertViewHas('surveys', fn ($rows) => $rows->total() === 27 && $rows->count() === 25)->assertSee('type=D2&amp;page=2', false);
        $this->assertLessThanOrEqual($single + 2, count(DB::getQueryLog()));
        DB::disableQueryLog();
        $export = $this->get('/manager/questionnaires/export?type=D2')->assertOk()->streamedContent();
        $this->assertStringStartsWith("\xEF\xBB\xBF", $export);
        $csv = fopen('php://temp', 'r+');
        fwrite($csv, $export);
        rewind($csv);
        $rows = [];
        while (($row = fgetcsv($csv, 0, ';', '"', '')) !== false) {
            $rows[] = $row;
        } fclose($csv);
        $this->assertCount(28, $rows);
        $this->assertStringContainsString($first->email, $export);
        $this->assertStringNotContainsString($first->token, $export);
        $this->get('/manager/questionnaires/export?type=D1')->assertOk()->assertDownload('questionnaires-D1.csv');
    }

    public function test_d6_recall_history_contact_and_read_only_detail(): void
    {
        $this->manager();
        $survey = $this->fixture('D6', 'answered');
        $survey->update(['recall_due_at' => now()->addMonths(3)]);
        foreach (app(SurveyCatalog::class)->definition(SurveyType::D6)['questions'] as $question) {
            if (in_array($question['id'], ['q1', 'q2', 'q4'])) {
                SurveyAnswer::create(['survey_id' => $survey->id, 'question_id' => $question['id'], 'value' => $question['choices'][0]['value']]);
            }
        }
        $contact = Contact::create(['ticket_num' => 'DT-000123', 'name' => 'Client', 'email' => $survey->email, 'subject' => 'D6', 'message' => 'Help', 'source' => ContactSource::D6Survey, 'status' => 'pending']);
        $survey->update(['contact_id' => $contact->id]);
        $survey->actions()->create(['action_type' => 'TICKET', 'action_key' => 'ticket', 'detail' => 'Contact créé', 'triggered_at' => now()]);
        $before = $survey->fresh()->getAttributes();
        $this->get('/manager/questionnaires?type=D6')->assertOk()->assertViewHas('contacts', 1)->assertSee(route('manager.support.show', $contact));
        $detail = $this->get('/manager/questionnaires/'.$survey->id);
        if (getenv('MANAGER_SURVEY_QA_DIR')) {
            file_put_contents(getenv('MANAGER_SURVEY_QA_DIR').'/detail.html', $detail->getContent());
            file_put_contents(getenv('MANAGER_SURVEY_QA_DIR').'/populated.html', $this->get('/manager/questionnaires?type=D6')->getContent());
        }
        $detail->assertOk()->assertSee('Rappel prévu')->assertSee($survey->recall_due_at->format('d/m/Y'))->assertSee('Contact créé');
        $this->assertSame($before, $survey->fresh()->getAttributes());
    }

    public function test_session_seen_cursor_does_not_hide_later_answers(): void
    {
        $this->manager();
        $survey = $this->fixture('D1', 'answered');
        $this->get('/manager/questionnaires')->assertOk()->assertViewHas('newCount', 1);
        Carbon::setTestNow(now()->addHour());
        $this->fixture('D1', 'answered');
        $this->post('/manager/questionnaires/seen', ['type' => 'D1'])->assertRedirect();
        $this->get('/manager/questionnaires')->assertOk()->assertViewHas('newCount', 1);
        $this->get('/manager/questionnaires?unread=1')->assertOk()->assertViewHas('answered', 1);
    }

    public function test_export_is_filtered_and_escapes_formula_and_html_payloads(): void
    {
        $this->manager();
        $survey = $this->fixture('D2', 'answered');
        $survey->update(['email' => '=HYPERLINK("https://example.test")']);
        SurveyAnswer::create(['survey_id' => $survey->id, 'question_id' => 'q1', 'value' => 'usage_reel']);
        SurveyAnswer::create(['survey_id' => $survey->id, 'question_id' => 'q2', 'value' => '<script>alert(1)</script>']);
        $this->fixture('D2', 'expired');
        $csv = $this->get('/manager/questionnaires/export?type=D2&status=answered')->assertOk()->streamedContent();
        $this->assertStringContainsString("'=HYPERLINK", $csv);
        $this->assertStringNotContainsString('Expiré', $csv);
        $this->get('/manager/questionnaires/'.$survey->id)->assertOk()->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)->assertDontSee('<script>alert(1)</script>', false);
        $this->get('/manager/questionnaires/'.Str::uuid())->assertNotFound();
    }

    public function test_seen_state_is_scoped_to_manager_and_type_and_public_page_is_read_only(): void
    {
        $this->manager();
        $first = $this->fixture('D1', 'answered');
        $this->fixture('D2', 'answered');
        $before = DB::table('survey_actions')->count();
        $this->get('/manager/questionnaires')->assertOk();
        $this->post('/manager/questionnaires/seen', ['type' => 'D1'])->assertRedirect();
        $this->get('/manager/questionnaires')->assertViewHas('newCount', 0);
        $this->get('/manager/questionnaires?type=D2')->assertViewHas('newCount', 1);
        $this->actingAs(Manager::create(['name' => 'Other', 'email' => 'other@example.test']), 'manager');
        $this->get('/manager/questionnaires')->assertViewHas('newCount', 1);
        $this->assertSame($before, DB::table('survey_actions')->count());
        Mail::assertNothingSent();
        Notification::assertNothingSent();
    }

    public function test_email_preview_matches_real_d1_to_d6_mail_without_live_response_links(): void
    {
        $reader = app(\App\Services\Surveys\ManagerSurveyReader::class);
        foreach (SurveyType::cases() as $type) {
            $survey = $this->fixture($type->value);
            foreach (['fr', 'en', 'nl', 'es'] as $locale) {
                $survey->user->forceFill(['locale' => $locale])->save();
                foreach ([false, true] as $reminder) {
                    $previousLocale = app()->getLocale();
                    $preview = $reader->emailPreview($survey, $reminder);
                    $class = '\\App\\Mail\\'.$type->value.'SurveyMail';
                    $mail = new $class($survey, $locale, $reminder);
                    $actual = $mail->render();
                    $this->assertSame(preg_replace('/href="[^"]*"/', 'href="#"', $actual), $preview['html']);
                    $this->assertSame($mail->subject, $preview['subject']);
                    $this->assertTrue($mail->hasCc('support@dalytrac.com'));
                    $this->assertSame($locale, $preview['locale']);
                    $this->assertSame($previousLocale, app()->getLocale());
                    $this->assertStringNotContainsString($survey->token, $preview['html']);
                }
            }
        }
        Mail::assertNothingSent();
    }

    public function test_voir_shows_initial_and_reminder_emails_without_mutating_survey(): void
    {
        $this->manager();
        $survey = $this->fixture('D2');
        $survey->update(['reminder_sent_at' => now()->subHour()]);
        $survey->user->forceFill(['locale' => 'nl'])->save();
        $before = $survey->fresh()->getAttributes();
        $this->get('/manager/questionnaires/'.$survey->id)->assertOk()
            ->assertSee('Email initial')->assertSee('Email de relance')->assertSee($survey->email)
            ->assertSee('sandbox=""', false)->assertViewHas('emails', fn ($emails) => count($emails) === 2 && $emails[0]['locale'] === 'nl')
            ->assertDontSee($survey->token)->assertDontSee('signature=');
        $this->assertSame($before, $survey->fresh()->getAttributes());
        $this->assertSame(0, $survey->actions()->count());
        $survey->update(['sent_at' => null, 'reminder_sent_at' => null]);
        $this->get('/manager/questionnaires/'.$survey->id)->assertOk()->assertSee('Aucun email envoyé enregistré');
        Mail::assertNothingSent();
        Notification::assertNothingSent();
    }

    public function test_detail_hides_empty_answers_and_shows_red_no_response_message(): void
    {
        $this->manager();
        $survey = $this->fixture('D2');
        $this->get('/manager/questionnaires/'.$survey->id)->assertOk()
            ->assertSee('Aucune réponse')->assertSee('class="mq-no-response"', false)
            ->assertSee('Questionnaire 2 — Premier usage')
            ->assertSeeInOrder(['Aucune réponse', 'Aperçu de l’email envoyé'])
            ->assertDontSee('class="mq-detail-answers"', false);
        SurveyAnswer::create(['survey_id' => $survey->id, 'question_id' => 'q1', 'value' => 'usage_reel']);
        $this->get('/manager/questionnaires/'.$survey->id)->assertOk()
            ->assertSee('class="mq-detail-answers"', false)
            ->assertDontSee('class="mq-no-response"', false)
            ->assertViewHas('responses', fn ($answers) => $answers['q1']['value'] !== null);
    }

    public function test_first_email_answer_is_visible_to_manager_before_final_submission(): void
    {
        $survey = $this->fixture('D1');
        $url = app(\App\Services\Surveys\SurveyLink::class)->url($survey, 'fr', 'connexion');
        $this->get($url)->assertOk()->assertViewHas('autoConfirmQ1', true);
        $this->post($url, ['intent' => 'confirm_q1', 'answers' => ['q1' => 'connexion']])->assertOk();
        $this->manager();
        $this->get('/manager/questionnaires/'.$survey->id)->assertOk()
            ->assertViewHas('responses', fn ($answers) => filled($answers['q1']['value']))
            ->assertSee('class="mq-detail-answers"', false)->assertDontSee('class="mq-no-response"', false);
        $this->get('/manager/questionnaires')->assertOk()->assertSee('Première réponse');
        $this->assertNull($survey->fresh()->completed_at);
        Mail::assertNothingSent();
    }

    public function test_questionnaire_four_counts_unique_companies_with_a_confirmed_first_payment_after_send(): void
    {
        $this->manager();
        $paid = function (SurveyResponse $survey, $at, ?string $invoice = 'in_confirmed'): void {
            DB::table('entreprises')->where('id', $survey->entreprise_id)->update([
                'first_paid_at' => $at?->toIso8601String(), 'first_paid_invoice_id' => $invoice === null ? null : $invoice.'_'.$survey->entreprise_id,
            ]);
        };
        $converted = $this->fixture('D4', 'cancelled');
        $paid($converted, now()->subDay());
        $answered = $this->fixture('D4', 'answered');
        $paid($answered, now()->subDay());
        SurveyResponse::create(['survey_type' => 'D4', 'survey_version' => 1, 'cycle_key' => 'duplicate-cycle',
            'entreprise_id' => $converted->entreprise_id, 'user_id' => $converted->user_id,
            'email' => $converted->email, 'sent_at' => now()->subDays(3)]);
        $paid($this->fixture('D4'), now()->subDays(3));
        $paid($this->fixture('D4'), now()->subDays(2));
        $paid($this->fixture('D4'), now()->addDay());
        $pending = $this->fixture('D4');
        DB::table('entreprises')->where('id', $pending->entreprise_id)->update(['first_paid_lookup_customer_id' => 'cus_pending']);
        $paid($this->fixture('D4'), null, null);
        $unsent = $this->fixture('D4');
        $unsent->update(['sent_at' => null]);
        $paid($unsent, now()->subDay());
        $paid($this->fixture('D3'), now()->subDay());
        $this->get('/manager/questionnaires?type=D4')->assertOk()
            ->assertViewHas('subscriptionsAfterSend', 2)->assertSee('Abonnés après l’envoi');
        $this->get('/manager/questionnaires?type=D4&status=cancelled')->assertOk()->assertViewHas('subscriptionsAfterSend', 1);
        $this->get('/manager/questionnaires?type=D4&q='.urlencode($answered->email))->assertOk()->assertViewHas('subscriptionsAfterSend', 1);
        $this->get('/manager/questionnaires?type=D4&q=not-found')->assertOk()->assertViewHas('subscriptionsAfterSend', 0);
        $this->get('/manager/questionnaires?type=D3')->assertOk()->assertViewHas('subscriptionsAfterSend', null)->assertDontSee('Abonnés après l’envoi');
        Mail::assertNothingSent();
    }

    public function test_questionnaire_four_conversion_kpi_is_zero_when_empty_and_is_read_only(): void
    {
        $this->manager();
        $this->get('/manager/questionnaires?type=D4')->assertOk()->assertViewHas('subscriptionsAfterSend', 0);
        $survey = $this->fixture('D4', 'expired');
        DB::table('entreprises')->where('id', $survey->entreprise_id)->update([
            'first_paid_at' => now()->subDay()->toIso8601String(), 'first_paid_invoice_id' => 'in_later_paid',
        ]);
        $before = $survey->fresh()->getAttributes();
        $companyBefore = DB::table('entreprises')->where('id', $survey->entreprise_id)->first();
        $response = $this->get('/manager/questionnaires?type=D4')->assertOk()->assertViewHas('subscriptionsAfterSend', 1);
        if (getenv('MANAGER_SURVEY_QA_DIR')) {
            file_put_contents(getenv('MANAGER_SURVEY_QA_DIR').'/D4-kpi.html', $response->getContent());
        }
        $this->assertSame($before, $survey->fresh()->getAttributes());
        $this->assertEquals($companyBefore, DB::table('entreprises')->where('id', $survey->entreprise_id)->first());
        $this->assertSame(0, $survey->actions()->count());
    }
}
