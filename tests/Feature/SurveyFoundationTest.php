<?php

namespace Tests\Feature;

use App\Enums\SurveyActionType;
use App\Enums\SurveyStatus;
use App\Enums\SurveyType;
use App\Models\Contact;
use App\Models\Entreprise;
use App\Models\SurveyAction;
use App\Models\SurveyAnswer;
use App\Models\SurveyResponse;
use App\Models\User;
use App\Services\Surveys\SurveyCatalog;
use App\Services\TicketService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use Laravel\Cashier\Subscription;
use LogicException;
use RuntimeException;
use Tests\TestCase;

class SurveyFoundationTest extends TestCase
{
    private ?string $schema = null;

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'pgsql']);
        $this->guardAgainstNonTestingDatabase();
        $this->schema = 'survey_test_'.bin2hex(random_bytes(8));
        DB::statement('CREATE SCHEMA "'.$this->schema.'"');
        DB::statement('SET search_path TO "'.$this->schema.'"');
        foreach (['entreprises', 'users', 'managers'] as $name) {
            Schema::create($name, function ($table) use ($name) {
                $table->id();
                $table->string('name')->nullable();
                $table->string('email')->nullable();
                if ($name === 'entreprises') {
                    $table->string('invoice_prefix')->default('F');
                    $table->string('invoice_number_format')->default('year_month_number');
                }
                if ($name === 'users') {
                    $table->foreignId('entreprise_id')->nullable()->constrained('entreprises');
                }
                $table->timestamps();
            });
        }
        (require database_path('migrations/2019_05_03_000002_create_subscriptions_table.php'))->up();
        (require database_path('migrations/2025_11_19_142449_update_subscriptions_table_for_entreprise.php'))->up();
        (require database_path('migrations/2019_05_03_000003_create_subscription_items_table.php'))->up();
        foreach (glob(database_path('migrations/2026_10_29_00000*_*.php')) as $path) {
            (require $path)->up();
        }
        $this->migration()->up();
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

    private function migration(): object
    {
        return require database_path('migrations/2026_10_29_100000_create_survey_foundation_tables.php');
    }

    private function survey(array $attributes = []): SurveyResponse
    {
        $company = $attributes['entreprise_id'] ?? Entreprise::create(['name' => 'Survey company'])->id;

        return SurveyResponse::create(array_replace([
            'entreprise_id' => $company, 'survey_type' => SurveyType::D1,
            'survey_version' => 1, 'email' => 'respondent@example.test',
        ], $attributes));
    }

    private function action(SurveyResponse $survey, array $attributes = []): SurveyAction
    {
        return $survey->actions()->create(array_replace([
            'action_type' => SurveyActionType::Ticket, 'action_key' => 'ticket:technical-block:v1',
        ], $attributes));
    }

    private function rejects(callable $operation, string $state): void
    {
        try {
            DB::transaction($operation);
            $this->fail('The database accepted an invalid operation.');
        } catch (QueryException $exception) {
            $this->assertSame($state, $exception->getCode());
        }
    }

    public function test_all_types_create_uuid_surveys_with_random_hidden_tokens_and_no_side_effects(): void
    {
        $company = Entreprise::create(['name' => 'Company']);
        $tokens = [];
        foreach (SurveyType::cases() as $type) {
            $survey = $this->survey(['entreprise_id' => $company->id, 'survey_type' => $type]);
            $survey->refresh();
            $this->assertTrue(Str::isUuid($survey->id));
            $this->assertSame($type, $survey->survey_type);
            $this->assertSame(SurveyStatus::Sent, $survey->status);
            $this->assertSame('once', $survey->cycle_key);
            $this->assertFalse($survey->converted_after_answer);
            $this->assertNull($survey->sent_at);
            $this->assertNull($survey->contact);
            $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $survey->token);
            $this->assertArrayNotHasKey('token', $survey->toArray());
            $tokens[] = $survey->token;
        }
        $this->assertCount(6, array_unique($tokens));
        $this->assertSame(0, Contact::count());
        $this->assertSame(0, SurveyAction::count());
        Mail::assertNothingSent();
        Notification::assertNothingSent();
    }

    public function test_company_type_cycle_is_unique_and_d6_accepts_distinct_cycles(): void
    {
        $first = $this->survey(['survey_type' => SurveyType::D6, 'cycle_key' => '2026-10-01']);
        $this->rejects(fn () => $this->survey(['entreprise_id' => $first->entreprise_id,
            'survey_type' => SurveyType::D6, 'cycle_key' => '2026-10-01']), '23505');
        $this->survey(['entreprise_id' => $first->entreprise_id, 'survey_type' => SurveyType::D6, 'cycle_key' => '2027-10-01']);
        $this->survey(['survey_type' => SurveyType::D6, 'cycle_key' => '2026-10-01']);
        $this->assertSame(3, SurveyResponse::count());
    }

    public function test_duplicate_token_is_rejected_by_database(): void
    {
        $first = $this->survey();
        $copy = $first->replicate();
        $copy->cycle_key = 'other';
        $this->rejects(fn () => $copy->save(), '23505');
    }

    public function test_numeric_parent_relations_and_nullable_contact_and_subscription(): void
    {
        $company = Entreprise::create(['name' => 'Company']);
        $user = User::create(['name' => 'Owner', 'email' => 'owner@example.test', 'entreprise_id' => $company->id]);
        $subscription = new Subscription;
        $subscription->entreprise_id = $company->id;
        $subscription->type = 'default';
        $subscription->stripe_id = 'sub_fixture';
        $subscription->stripe_status = 'active';
        $subscription->save();
        $contact = app(TicketService::class)->create(name: 'Owner', email: $user->email,
            subject: 'Existing contact', message: 'Fixture only', user: $user, entreprise: $company);
        $survey = $this->survey(['entreprise_id' => $company->id, 'user_id' => $user->id,
            'subscription_id' => $subscription->id, 'contact_id' => $contact->id]);
        $this->assertTrue($survey->entreprise->is($company));
        $this->assertTrue($survey->user->is($user));
        $this->assertTrue($survey->subscription->is($subscription));
        $this->assertTrue($survey->contact->is($contact));
        DB::table('subscriptions')->where('id', $subscription->id)->delete();
        DB::table('users')->where('id', $user->id)->delete();
        $survey->refresh();
        $this->assertNull($survey->subscription_id);
        $this->assertNull($survey->user_id);
        $this->assertSame('respondent@example.test', $survey->email);
    }

    public function test_answer_uniqueness_and_cascade_for_survey_without_action_receipts(): void
    {
        $survey = $this->survey();
        $answer = $survey->answers()->create(['question_id' => 'q1', 'value' => 'connexion']);
        $this->assertTrue(Str::isUuid($answer->id));
        $this->assertTrue($answer->survey->is($survey));
        $this->rejects(fn () => $survey->answers()->create(['question_id' => 'q1', 'value' => 'reussi']), '23505');
        $survey->delete();
        $this->assertSame(0, SurveyAnswer::count());
    }

    public function test_text_answers_accept_2000_unicode_characters_and_reject_more(): void
    {
        $survey = $this->survey();
        $answer = $survey->answers()->create(['question_id' => 'q2b', 'value' => str_repeat('é', 2000)]);
        $this->assertSame(2000, mb_strlen($answer->fresh()->value));
        $this->expectException(ValidationException::class);
        $survey->answers()->create(['question_id' => 'q2_autre', 'value' => str_repeat('é', 2001)]);
    }

    public function test_database_enforces_answer_length_when_bypassing_models(): void
    {
        $survey = $this->survey();
        $this->rejects(fn () => DB::table('survey_answers')->insert([
            'id' => (string) Str::uuid(), 'survey_id' => $survey->id, 'question_id' => 'q2b', 'value' => str_repeat('é', 2001),
        ]), '23514');
    }

    public function test_action_receipt_is_unique_per_survey_rule_and_all_types_are_supported(): void
    {
        $survey = $this->survey();
        foreach (SurveyActionType::cases() as $type) {
            $action = $this->action($survey, ['action_type' => $type, 'action_key' => 'event:'.$type->value]);
            $this->assertTrue(Str::isUuid($action->id));
            $this->assertSame($type, $action->fresh()->action_type);
            $this->assertNotNull($action->fresh()->triggered_at);
            $this->assertTrue($action->survey->is($survey));
        }
        $this->rejects(fn () => $this->action($survey, ['action_key' => 'event:TICKET']), '23505');
        $this->action($survey, ['action_key' => 'ticket:another-rule:v1']);
        $this->assertSame(count(SurveyActionType::cases()) + 1, $survey->actions()->count());
    }

    public function test_contact_deletion_preserves_receipt_and_prevents_replaying_same_rule(): void
    {
        $contact = app(TicketService::class)->create(name: 'Fixture', email: 'fixture@example.test', subject: 'Fixture', message: 'Fixture');
        $survey = $this->survey(['contact_id' => $contact->id]);
        $receipt = $this->action($survey);
        $contact->delete();
        $this->assertNull($survey->fresh()->contact_id);
        $this->assertTrue($receipt->fresh()->exists);
        $this->rejects(fn () => $this->action($survey), '23505');
        $this->rejects(fn () => DB::table('survey_responses')->where('id', $survey->id)->delete(), '23503');
        $this->rejects(fn () => DB::table('entreprises')->where('id', $survey->entreprise_id)->delete(), '23503');
    }

    public function test_action_journal_is_append_only_even_for_direct_sql(): void
    {
        $action = $this->action($this->survey());
        $this->rejects(fn () => DB::table('survey_actions')->where('id', $action->id)->update(['detail' => 'changed']), '23514');
        $this->rejects(fn () => DB::table('survey_actions')->where('id', $action->id)->delete(), '23514');
        $this->rejects(fn () => DB::statement('TRUNCATE survey_actions'), '23514');
        try {
            $action->update(['detail' => 'changed']);
            $this->fail('Model update should be refused.');
        } catch (LogicException) {
            $this->assertNull($action->fresh()->detail);
        }
        $this->expectException(LogicException::class);
        $action->delete();
    }

    public function test_action_receipt_rolls_back_with_its_transaction_and_keys_are_scoped_to_survey(): void
    {
        $survey = $this->survey();
        try {
            DB::transaction(function () use ($survey) {
                $this->action($survey);
                throw new RuntimeException('Simulated future action failure');
            });
            $this->fail('Expected transaction failure.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Simulated future action failure', $exception->getMessage());
        }
        $this->assertSame(0, SurveyAction::count());
        $this->action($survey);
        $this->action($this->survey());
        $this->assertSame(2, SurveyAction::count());
        $this->assertSame(0, Contact::count());
    }

    public function test_database_enforces_parent_foreign_keys(): void
    {
        $survey = $this->survey();
        foreach (['entreprise_id', 'user_id', 'subscription_id', 'contact_id'] as $column) {
            $this->rejects(fn () => DB::table('survey_responses')->where('id', $survey->id)->update([$column => 999999]), '23503');
        }
        $this->rejects(fn () => SurveyAnswer::create([
            'survey_id' => (string) Str::uuid(), 'question_id' => 'q1', 'value' => 'connexion',
        ]), '23503');
        $this->rejects(fn () => SurveyAction::create([
            'survey_id' => (string) Str::uuid(), 'action_type' => SurveyActionType::Sent, 'action_key' => 'sent:initial',
        ]), '23503');
    }

    public function test_database_rejects_invalid_enums_version_cycle_token_and_action_key(): void
    {
        $survey = $this->survey();
        foreach (['survey_type' => 'D7', 'status' => 'draft', 'survey_version' => 0, 'cycle_key' => '', 'token' => 'predictable'] as $field => $value) {
            $this->rejects(fn () => DB::table('survey_responses')->where('id', $survey->id)->update([$field => $value]), '23514');
        }
        $this->rejects(fn () => DB::table('survey_actions')->insert([
            'id' => (string) Str::uuid(), 'survey_id' => $survey->id, 'action_type' => 'UNKNOWN', 'action_key' => 'test',
        ]), '23514');
        $this->rejects(fn () => $this->action($survey, ['action_key' => ' ']), '23514');
        foreach (SurveyStatus::cases() as $status) {
            $survey->update(['status' => $status]);
            $this->assertSame($status, $survey->fresh()->status);
        }
    }

    public function test_catalog_loads_exact_reference_questions_conditions_and_french_fallback(): void
    {
        $catalog = app(SurveyCatalog::class);
        $expectedCounts = ['D1' => 6, 'D2' => 5, 'D3' => 6, 'D4' => 5, 'D5' => 5, 'D6' => 5];
        foreach (SurveyType::cases() as $type) {
            $definition = $catalog->definition($type, 1);
            $this->assertSame(1, $definition['version']);
            $this->assertCount($expectedCounts[$type->value], $definition['questions']);
            $questions = collect($definition['questions'])->keyBy('id');
            $this->assertCount(count($definition['questions']), $questions);
            $this->assertTrue($questions['q1']['required']);
            $this->assertTrue($questions['q1']['email_clickable']);
            foreach ($questions as $question) {
                foreach (['fr', 'en', 'nl', 'es'] as $locale) {
                    if (! in_array($type, [SurveyType::D1, SurveyType::D2, SurveyType::D3, SurveyType::D4], true) || $locale === 'fr') {
                        $this->assertSame($catalog->label($question['label_key'], 'fr'), $catalog->label($question['label_key'], $locale));
                    } else {
                        $this->assertTrue(\Illuminate\Support\Facades\Lang::hasForLocale($question['label_key'], $locale));
                    }
                }
                if ($question['type'] === 'text') {
                    $this->assertSame(2000, $question['max_length']);
                }
                foreach ($question['choices'] as $choice) {
                    $this->assertNotSame($choice['label_key'], $catalog->label($choice['label_key'], 'fr'));
                }
                if ($question['condition']) {
                    $parent = $questions[$question['condition']['question_id']];
                    foreach ($question['condition']['values'] as $value) {
                        $this->assertContains($value, array_column($parent['choices'], 'value'));
                    }
                }
            }
        }
        $d1 = collect($catalog->definition(SurveyType::D1)['questions'])->keyBy('id');
        $this->assertSame(['pas_commence', 'connexion', 'configuration', 'probleme_technique', 'reussi', 'abandon'], array_column($d1['q1']['choices'], 'value'));
        $this->assertSame(['reussi', 'abandon'], $d1['q2']['condition']['values']);
        $this->assertSame('Copiez le message d’erreur affiché.', $catalog->label($d1['q2b']['label_key'], 'fr'));
        $this->expectException(InvalidArgumentException::class);
        $catalog->definition(SurveyType::D1, 999);
    }

    public function test_no_legacy_ticket_foreign_key_and_migration_is_reversible(): void
    {
        $this->assertFalse(Schema::hasTable($this->schema.'.support_tickets'));
        $this->assertFalse(Schema::hasColumn($this->schema.'.survey_responses', 'support_ticket_id'));
        $this->assertTrue(Schema::hasColumn($this->schema.'.contacts', 'priority'));
        $this->action($this->survey());
        $this->migration()->down();
        foreach (['survey_responses', 'survey_answers', 'survey_actions'] as $table) {
            $this->assertFalse(Schema::hasTable($this->schema.'.'.$table));
        }
        $this->assertTrue(Schema::hasTable($this->schema.'.contacts'));
        $this->migration()->up();
        $this->assertSame(0, SurveyResponse::count());
    }

    public function test_concurrent_processes_cannot_create_duplicate_surveys_or_action_receipts(): void
    {
        $company = Entreprise::create(['name' => 'Concurrent company']);
        $this->compete('survey', (string) $company->id);
        $this->assertSame(1, SurveyResponse::count());
        $this->compete('action', SurveyResponse::sole()->id);
        $this->assertSame(1, SurveyAction::count());
    }

    private function compete(string $mode, string $parent): void
    {
        $processes = [];
        $start = (string) (microtime(true) + 1);
        try {
            for ($i = 0; $i < 2; $i++) {
                $process = proc_open([PHP_BINARY, base_path('tests/Support/survey-foundation-worker.php'), $this->schema,
                    config('database.connections.pgsql.database'), $start, $mode, $parent],
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
                $results[] = $stdout;
            }
            $processes = [];
            sort($results);
            $this->assertSame(['created', 'duplicate'], $results);
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
