<?php

namespace Tests\Feature;

use App\Enums\ContactSource;
use App\Enums\ContactStatus;
use App\Enums\SurveyActionType;
use App\Enums\SurveyStatus;
use App\Enums\SurveyType;
use App\Mail\D1AccountWaitingMail;
use App\Models\Contact;
use App\Models\Entreprise;
use App\Models\Manager;
use App\Models\SurveyResponse;
use App\Models\User;
use App\Services\ContactManagementService;
use App\Services\Surveys\D1ActionService;
use App\Services\Surveys\D1AnswerService;
use App\Services\Surveys\SurveyLink;
use App\Services\TicketService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class SurveyD1ActionsTest extends TestCase
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
                $table->string('name');
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
                $table->string('entreprise_id');
                if ($name === 'employees') {
                    $table->unsignedBigInteger('user_id')->nullable();
                }
                $table->timestamps();
            });
        }
        foreach (array_merge(glob(database_path('migrations/2026_10_29_00000*_*.php')),
            glob(database_path('migrations/2026_10_29_10000*_*.php'))) as $path) {
            (require $path)->up();
        }
        $this->withoutVite();
        Mail::fake();
        Notification::fake();
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

    private function survey(array $answers = ['q1' => 'connexion'], bool $completed = true): SurveyResponse
    {
        $company = Entreprise::create(['name' => 'Trusted company']);
        $user = User::create(['name' => 'Trusted owner', 'email' => 'owner'.$company->id.'@example.test',
            'entreprise_id' => $company->id, 'locale' => 'fr']);
        $user->forceFill(['created_at' => now('UTC')->subDays(2), 'email_verified_at' => now('UTC')->subDay()])->save();
        $survey = SurveyResponse::create(['entreprise_id' => $company->id, 'user_id' => $user->id,
            'email' => $user->email, 'survey_type' => SurveyType::D1, 'survey_version' => 1, 'sent_at' => now('UTC'),
            'status' => $completed ? (($answers['q1'] ?? null) === 'abandon' ? SurveyStatus::Abandon : SurveyStatus::Answered) : SurveyStatus::Sent,
            'completed_at' => $completed ? now('UTC') : null]);
        if ($completed) {
            foreach ($answers as $id => $value) {
                $survey->answers()->create(['question_id' => $id, 'value' => $value]);
            }
            $survey->actions()->create(['action_type' => SurveyActionType::Answered, 'action_key' => 'answered:final']);
        }

        return $survey;
    }

    public function test_each_ticket_rule_creates_one_pending_contact_using_ticket_service(): void
    {
        $cases = [
            [['q1' => 'connexion'], 'Problème de connexion'],
            [['q1' => 'probleme_technique'], 'Problème technique'],
            [['q1' => 'configuration', 'q2' => 'Un message d’erreur s’affiche', 'q2b' => 'Error E42'], 'Message d’erreur'],
            [['q1' => 'pas_commence', 'q3' => 'Une assistance technique'], 'Demande d’assistance'],
            [['q1' => 'pas_commence', 'q3' => 'Une démonstration'], 'Demande de démonstration'],
            [['q1' => 'reussi'], 'Démarrage déclaré non constaté'],
        ];
        foreach ($cases as $index => [$answers, $subject]) {
            $survey = $this->survey($answers);
            $ticket = app(D1ActionService::class)->process($survey);
            $this->assertSame('D1 — '.$subject, $ticket->subject);
            $this->assertSame('DT-'.str_pad((string) ($index + 1), 6, '0', STR_PAD_LEFT), $ticket->ticket_num);
            $this->assertSame(ContactSource::D1Survey, $ticket->source);
            $this->assertSame(ContactStatus::Pending, $ticket->status);
            $this->assertSame(\App\Enums\ContactPriority::Normal, $ticket->priority);
            $this->assertSame($survey->user_id, $ticket->user_id);
            $this->assertSame($survey->entreprise_id, $ticket->entreprise_id);
            $this->assertSame($survey->email, $ticket->email);
            $this->assertSame('Trusted owner', $ticket->name);
            $this->assertSame($ticket->id, $survey->fresh()->contact_id);
            $this->assertNull($ticket->ip);
            $this->assertSame(1, $ticket->events()->count());
            $this->assertSame(1, $survey->actions()->where('action_type', SurveyActionType::Ticket)->count());
            $this->assertSame(0, $ticket->emailLogs()->count());
            if (isset($answers['q2b'])) {
                $this->assertStringContainsString('Error E42', $ticket->message);
            }
        }
        Mail::assertNothingSent();
    }

    public function test_multiple_triggers_are_consolidated_and_all_useful_answers_are_readable(): void
    {
        $survey = $this->survey(['q1' => 'probleme_technique', 'q2' => 'Un message d’erreur s’affiche',
            'q2b' => '<script>alert(1)</script> Error E42', 'q3' => 'Une assistance technique']);
        $contact = app(D1ActionService::class)->process($survey);
        $this->assertSame(1, Contact::count());
        foreach (['Questionnaire D1', 'Problème technique', 'Message d’erreur', 'Demande d’assistance', 'Error E42'] as $text) {
            $this->assertStringContainsString($text, $contact->message);
        }
        $metadata = $contact->events()->sole()->metadata;
        $this->assertSame(['q1.probleme_technique', 'q2.erreur', 'q3.assistance'], $metadata['rules']);
        $this->assertSame($survey->id, $metadata['survey_id']);
        $this->assertSame(1, $metadata['survey_version']);
        $this->assertSame('D1', $metadata['survey_type']);
        $this->assertStringNotContainsString($survey->token, json_encode($metadata));
        $this->assertStringNotContainsString($survey->token, $contact->message);
    }

    public function test_no_trigger_abandon_and_real_success_do_not_create_false_ticket_actions(): void
    {
        foreach ([['q1' => 'pas_commence', 'q3' => 'Un guide de démarrage'], ['q1' => 'abandon'], ['q1' => 'reussi']] as $answers) {
            $survey = $this->survey($answers);
            if ($answers['q1'] === 'reussi') {
                DB::table('projects')->insert(['entreprise_id' => $survey->entreprise_id]);
            }
            $this->assertNull(app(D1ActionService::class)->process($survey));
            $this->assertSame(0, $survey->actions()->where('action_type', SurveyActionType::Ticket)->count());
            $this->assertSame(1, $survey->actions()->where('action_type', SurveyActionType::ActionsProcessed)->count());
        }
        $this->assertSame(0, Contact::count());
        Mail::assertNothingSent();
    }

    public function test_success_activity_check_uses_all_four_categories_and_excludes_only_owner_seed(): void
    {
        foreach (['projects', 'employees', 'clients', 'tasks_timer'] as $table) {
            $survey = $this->survey(['q1' => 'reussi']);
            DB::table($table)->insert(['entreprise_id' => $survey->entreprise_id, 'created_at' => now('UTC')]);
            $this->assertNull(app(D1ActionService::class)->process($survey));
        }
        $seed = $this->survey(['q1' => 'reussi']);
        DB::table('employees')->insert(['entreprise_id' => $seed->entreprise_id, 'user_id' => $seed->user_id,
            'created_at' => $seed->user->created_at]);
        $this->assertNotNull(app(D1ActionService::class)->process($seed));
        $this->assertSame(1, Contact::count());
    }

    public function test_repeated_processing_and_contact_deletion_never_create_a_replacement(): void
    {
        $survey = $this->survey();
        $contact = app(D1ActionService::class)->process($survey);
        $this->assertTrue(app(D1ActionService::class)->process($survey)->is($contact));
        $manager = Manager::create(['name' => 'Manager', 'email' => 'manager@example.test']);
        app(ContactManagementService::class)->delete($contact, $manager, $contact->ticket_num);
        $this->assertNull($survey->fresh()->contact_id);
        $this->assertNull(app(D1ActionService::class)->process($survey));
        $this->assertSame(0, Contact::count());
        $this->assertSame(1, $survey->actions()->where('action_key', D1ActionService::CONTACT_KEY)->count());
        $this->assertSame(2, (int) DB::table('contact_ticket_sequences')->value('next_number'));
    }

    public function test_get_and_q1_only_do_not_execute_actions_but_final_post_does_once(): void
    {
        $survey = $this->survey(completed: false);
        $url = app(SurveyLink::class)->url($survey, 'fr', 'connexion');
        $this->get($url)->assertOk();
        $this->post($url, ['intent' => 'confirm_q1', 'answers' => ['q1' => 'connexion']])->assertOk();
        $this->assertNull(app(D1ActionService::class)->process($survey));
        $this->assertSame(0, Contact::count());
        $this->post($url, ['intent' => 'complete', 'answers' => ['q1' => 'connexion']])->assertOk();
        $this->post($url, ['intent' => 'complete', 'answers' => ['q1' => 'connexion']])->assertConflict();
        $this->assertSame(1, Contact::count());
        $this->assertSame(1, $survey->actions()->where('action_type', SurveyActionType::Ticket)->count());
    }

    public function test_ticket_failure_after_allocation_keeps_committed_answers_and_scheduler_can_retry(): void
    {
        $survey = $this->survey(completed: false);
        $real = new TicketService;
        $failing = Mockery::mock(TicketService::class);
        $failing->shouldReceive('create')->once()->andReturnUsing(function (...$args) use ($real) {
            $real->create(...$args);
            throw new RuntimeException('Simulated action failure');
        });
        $this->app->instance(TicketService::class, $failing);
        $url = app(SurveyLink::class)->url($survey, 'fr', 'connexion');
        $this->post($url, ['answers' => ['q1' => 'connexion']])->assertOk();
        $this->assertSame(SurveyStatus::Answered, $survey->fresh()->status);
        $this->assertNotNull($survey->fresh()->completed_at);
        $this->assertSame('connexion', $survey->answers()->sole()->value);
        $this->assertSame(0, Contact::count());
        $this->assertSame(0, $survey->actions()->where('action_type', SurveyActionType::Ticket)->count());
        $this->assertSame(0, $survey->actions()->where('action_key', D1ActionService::PROCESSED_KEY)->count());
        $this->app->instance(TicketService::class, $real);
        $this->artisan('surveys:process-d1')->assertSuccessful();
        $this->assertSame(1, Contact::count());
        $this->assertSame('DT-000001', Contact::sole()->ticket_num);
        $this->artisan('surveys:process-d1')->assertSuccessful();
        $this->assertSame(1, Contact::count());
    }

    public function test_receipt_failure_rolls_back_contact_link_and_number_without_erasing_answers(): void
    {
        $survey = $this->survey();
        DB::unprepared("CREATE FUNCTION fail_d1_ticket_receipt() RETURNS trigger LANGUAGE plpgsql AS $$ BEGIN IF NEW.action_type = 'TICKET' THEN RAISE EXCEPTION 'Test failure'; END IF; RETURN NEW; END; $$;
            CREATE TRIGGER fail_ticket BEFORE INSERT ON survey_actions FOR EACH ROW EXECUTE FUNCTION fail_d1_ticket_receipt();");
        $this->assertFalse(app(D1ActionService::class)->processSafely($survey));
        $this->assertSame(0, Contact::count());
        $this->assertNull($survey->fresh()->contact_id);
        $this->assertSame(1, $survey->answers()->count());
        $this->assertSame(1, (int) DB::table('contact_ticket_sequences')->value('next_number'));
        DB::statement('DROP TRIGGER fail_ticket ON survey_actions');
        $this->assertSame('DT-000001', app(D1ActionService::class)->process($survey)->ticket_num);
    }

    public function test_identity_is_taken_from_relations_not_from_email_and_missing_user_uses_company_name(): void
    {
        $survey = $this->survey();
        $other = $this->survey();
        $survey->update(['email' => $other->email]);
        $contact = app(D1ActionService::class)->process($survey);
        $this->assertSame($survey->user_id, $contact->user_id);
        $this->assertSame($other->email, $contact->email);
        $withoutUser = $this->survey();
        DB::table('users')->where('id', $withoutUser->user_id)->delete();
        $contact = app(D1ActionService::class)->process($withoutUser->fresh());
        $this->assertNull($contact->user_id);
        $this->assertSame('Trusted company', $contact->name);
    }

    public function test_inconsistent_identity_is_not_silently_reassociated(): void
    {
        $survey = $this->survey();
        $other = $this->survey();
        $survey->update(['user_id' => $other->user_id]);
        $this->assertFalse(app(D1ActionService::class)->processSafely($survey));
        $this->assertSame(0, Contact::count());
        $this->assertSame(SurveyStatus::Answered, $survey->fresh()->status);
    }

    public function test_known_survey_token_is_not_copied_even_when_pasted_into_error_text(): void
    {
        $survey = $this->survey(['q1' => 'configuration', 'q2' => 'Un message d’erreur s’affiche', 'q2b' => 'Initial error']);
        $survey->answers()->where('question_id', 'q2b')->first()->update(['value' => 'Error '.$survey->token]);
        $contact = app(D1ActionService::class)->process($survey);
        $this->assertStringNotContainsString($survey->token, $contact->message);
        $this->assertStringNotContainsString($survey->token, json_encode($contact->events()->sole()->metadata));
        $this->assertStringContainsString('[lien personnel masqué]', $contact->message);
    }

    public function test_abandon_lack_of_time_sends_one_acknowledgement_and_no_contact(): void
    {
        $survey = $this->survey(['q1' => 'abandon', 'q4' => D1ActionService::TIME_REASON]);
        app(D1ActionService::class)->process($survey);
        app(D1ActionService::class)->process($survey);
        $this->artisan('surveys:process-d1')->assertSuccessful();
        Mail::assertSent(D1AccountWaitingMail::class, 1);
        $this->assertSame(0, Contact::count());
        $this->assertSame(SurveyStatus::Abandon, $survey->fresh()->status);
        $this->assertSame(1, $survey->actions()->where('action_type', SurveyActionType::AutoEmail)->count());
        $this->assertSame(0, $survey->actions()->where('action_type', SurveyActionType::Ticket)->count());
    }

    public function test_other_abandon_reason_does_not_send_email_or_create_ticket(): void
    {
        $survey = $this->survey(['q1' => 'abandon', 'q4' => 'Autre', 'q2' => 'Un message d’erreur s’affiche', 'q3' => 'Une démonstration']);
        app(D1ActionService::class)->process($survey);
        Mail::assertNothingSent();
        $this->assertSame(0, Contact::count());
    }

    public function test_no_contact_decision_is_durable_even_if_activity_is_removed_later(): void
    {
        $survey = $this->survey(['q1' => 'reussi']);
        DB::table('clients')->insert(['entreprise_id' => $survey->entreprise_id]);
        app(D1ActionService::class)->process($survey);
        DB::table('clients')->delete();
        app(D1ActionService::class)->process($survey);
        $this->assertSame(0, Contact::count());
        $this->assertSame(0, $survey->actions()->where('action_type', SurveyActionType::Ticket)->count());
    }

    public function test_manager_support_can_filter_view_reply_close_reopen_and_delete_d1_ticket(): void
    {
        $survey = $this->survey(['q1' => 'configuration', 'q2' => 'Un message d’erreur s’affiche', 'q2b' => '<script>alert(1)</script>']);
        $contact = app(D1ActionService::class)->process($survey);
        $manager = Manager::create(['name' => 'Manager', 'email' => 'manager@example.test']);
        $this->actingAs($manager, 'manager')->get(route('manager.support.index', ['source' => ContactSource::D1Survey->value]))
            ->assertOk()->assertSee($contact->ticket_num)->assertSee($contact->subject)->assertSee($contact->email);
        $this->get(route('manager.support.show', $contact))->assertOk()
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)->assertDontSee('<script>alert(1)</script>', false);
        $service = app(ContactManagementService::class);
        $log = $service->reply($contact, $manager, 'Support reply', (string) Str::uuid());
        $this->assertSame(1, $contact->replies()->count());
        $this->assertSame($contact->id, $log->contact_id);
        $service->close($contact, $manager);
        $this->assertSame(ContactStatus::Closed, $contact->fresh()->status);
        $service->reopen($contact, $manager);
        $this->assertSame(ContactStatus::Pending, $contact->fresh()->status);
        $service->delete($contact, $manager, $contact->ticket_num);
        app(D1ActionService::class)->process($survey);
        $this->assertSame(0, Contact::count());
    }

    public function test_abandon_email_is_localized_and_does_not_contain_a_survey_link(): void
    {
        foreach (['fr', 'en', 'nl', 'es'] as $locale) {
            $survey = $this->survey(['q1' => 'abandon', 'q4' => D1ActionService::TIME_REASON]);
            $survey->user->update(['locale' => $locale]);
            app(D1ActionService::class)->process($survey);
        }
        Mail::assertSent(D1AccountWaitingMail::class, 4);
        foreach (Mail::sent(D1AccountWaitingMail::class) as $mail) {
            $html = $mail->render();
            $this->assertStringContainsString(__('d1-actions.subject', [], $mail->locale), $html);
            $this->assertStringNotContainsString('/questionnaires/', $html);
        }
    }

    public function test_unconfirmed_acknowledgement_is_not_retried_or_logged_as_sent(): void
    {
        $survey = $this->survey(['q1' => 'abandon', 'q4' => D1ActionService::TIME_REASON]);
        Mail::swap(Mail::getFacadeRoot()->manager);
        \Illuminate\Support\Facades\Event::listen(\Illuminate\Mail\Events\MessageSending::class, fn () => false);
        $this->assertFalse(app(D1ActionService::class)->processSafely($survey));
        app(D1ActionService::class)->process($survey);
        $this->assertSame(1, $survey->actions()->where('action_type', SurveyActionType::EmailUnconfirmed)->count());
        $this->assertSame(0, $survey->actions()->where('action_type', SurveyActionType::AutoEmail)->count());
        $this->assertSame(0, Contact::count());
        $this->assertSame(0, app(D1ActionService::class)->retryCompany($survey->entreprise_id));
    }

    public function test_an_outer_answer_transaction_runs_actions_only_after_its_final_commit(): void
    {
        $survey = $this->survey(completed: false);
        DB::beginTransaction();
        try {
            app(D1AnswerService::class)->submit($survey, ['q1' => 'connexion'], false);
            $this->assertSame(0, Contact::count());
            DB::rollBack();
        } catch (\Throwable $exception) {
            DB::rollBack();
            throw $exception;
        }
        $this->assertSame(0, Contact::count());
        $this->assertNull($survey->fresh()->completed_at);
        DB::transaction(function () use ($survey) {
            app(D1AnswerService::class)->submit($survey, ['q1' => 'connexion'], false);
            $this->assertSame(0, Contact::count());
        });
        $this->assertSame(1, Contact::count());
    }

    public function test_non_d1_surveys_and_unfinished_responses_have_no_actions(): void
    {
        $survey = $this->survey();
        $survey->update(['survey_type' => SurveyType::D2]);
        $this->assertNull(app(D1ActionService::class)->process($survey));
        $unfinished = $this->survey(completed: false);
        $this->assertNull(app(D1ActionService::class)->process($unfinished));
        $this->assertSame(0, Contact::count());
    }

    public function test_concurrent_processes_create_one_contact_and_one_abandon_email(): void
    {
        $survey = $this->survey();
        DB::unprepared('CREATE FUNCTION delay_d1_contact() RETURNS trigger LANGUAGE plpgsql AS $$ BEGIN PERFORM pg_sleep(0.4); RETURN NEW; END; $$;
            CREATE TRIGGER delay_contact BEFORE INSERT ON contacts FOR EACH ROW EXECUTE FUNCTION delay_d1_contact();');
        $this->compete($survey);
        $this->assertSame(1, Contact::count());
        $this->assertSame(1, $survey->actions()->where('action_type', SurveyActionType::Ticket)->count());
        $abandon = $this->survey(['q1' => 'abandon', 'q4' => D1ActionService::TIME_REASON]);
        $results = $this->compete($abandon);
        $this->assertSame(1, array_sum(array_column($results, 'emails')));
        $this->assertSame(1, $abandon->actions()->where('action_type', SurveyActionType::AutoEmail)->count());
        $this->assertSame(1, Contact::count());
    }

    private function compete(SurveyResponse $survey): array
    {
        $workers = [];
        $start = (string) (microtime(true) + 1);
        try {
            for ($i = 0; $i < 2; $i++) {
                $process = proc_open([PHP_BINARY, base_path('tests/Support/survey-d1-actions-worker.php'), $this->schema,
                    config('database.connections.pgsql.database'), $start, $survey->id],
                    [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, base_path());
                $this->assertIsResource($process);
                fclose($pipes[0]);
                $workers[] = [$process, $pipes];
            }
            $results = [];
            foreach ($workers as [$process, $pipes]) {
                $stdout = stream_get_contents($pipes[1]);
                $stderr = stream_get_contents($pipes[2]);
                fclose($pipes[1]);
                fclose($pipes[2]);
                $this->assertSame(0, proc_close($process), $stderr);
                $results[] = json_decode($stdout, true, 512, JSON_THROW_ON_ERROR);
            }
            $workers = [];

            return $results;
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
}
