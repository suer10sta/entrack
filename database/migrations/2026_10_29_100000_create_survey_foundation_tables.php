<?php

use App\Enums\SurveyActionType;
use App\Enums\SurveyStatus;
use App\Enums\SurveyType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('survey_responses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->enum('survey_type', array_column(SurveyType::cases(), 'value'));
            $table->unsignedInteger('survey_version');
            $table->string('cycle_key')->default('once');
            // Keep the company identity and permanent opt-out/action history intact.
            $table->foreignId('entreprise_id')->constrained('entreprises')->restrictOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('subscription_id')->nullable()->constrained('subscriptions')->nullOnDelete();
            $table->string('email');
            $table->string('token', 64)->unique();
            $table->enum('status', array_column(SurveyStatus::cases(), 'value'))->default(SurveyStatus::Sent->value);
            $table->timestampTz('sent_at')->nullable();
            $table->timestampTz('reminder_sent_at')->nullable();
            $table->timestampTz('clicked_at')->nullable();
            $table->timestampTz('completed_at')->nullable();
            $table->timestampTz('recall_due_at')->nullable();
            $table->boolean('converted_after_answer')->default(false);
            $table->timestampTz('converted_at')->nullable();
            $table->foreignId('contact_id')->nullable()->constrained('contacts')->nullOnDelete();
            $table->timestampsTz();
            $table->unique(['entreprise_id', 'survey_type', 'cycle_key'], 'survey_company_type_cycle_unique');
            $table->index(['survey_type', 'status', 'sent_at']);
            $table->index(['status', 'recall_due_at']);
            $table->index(['entreprise_id', 'sent_at']);
            $table->index(['entreprise_id', 'reminder_sent_at']);
            $table->index(['entreprise_id', 'status']);
        });

        Schema::create('survey_answers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('survey_id')->constrained('survey_responses')->cascadeOnDelete();
            $table->string('question_id');
            $table->text('value');
            $table->timestampsTz();
            $table->unique(['survey_id', 'question_id']);
        });

        Schema::create('survey_actions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            // A processed action receipt must not disappear through a survey deletion.
            $table->foreignUuid('survey_id')->constrained('survey_responses')->restrictOnDelete();
            $table->enum('action_type', array_column(SurveyActionType::cases(), 'value'));
            $table->string('action_key');
            $table->text('detail')->nullable();
            $table->timestampTz('triggered_at')->useCurrent();
            $table->unique(['survey_id', 'action_key']);
            $table->index(['survey_id', 'triggered_at']);
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE survey_responses ADD CONSTRAINT survey_version_positive CHECK (survey_version > 0), ADD CONSTRAINT survey_cycle_not_empty CHECK (length(trim(cycle_key)) > 0), ADD CONSTRAINT survey_token_format CHECK (token ~ '^[a-f0-9]{64}$')");
            DB::statement('ALTER TABLE survey_answers ADD CONSTRAINT survey_answer_length CHECK (char_length(value) <= 2000)');
            DB::statement('ALTER TABLE survey_actions ADD CONSTRAINT survey_action_key_not_empty CHECK (length(trim(action_key)) > 0)');
            DB::unprepared(<<<'SQL'
                CREATE FUNCTION reject_survey_action_mutation() RETURNS trigger LANGUAGE plpgsql AS $$
                BEGIN
                    RAISE EXCEPTION 'survey_actions is append-only' USING ERRCODE = '23514';
                END;
                $$;
                CREATE TRIGGER survey_actions_append_only BEFORE UPDATE OR DELETE ON survey_actions
                FOR EACH ROW EXECUTE FUNCTION reject_survey_action_mutation();
                CREATE TRIGGER survey_actions_no_truncate BEFORE TRUNCATE ON survey_actions
                FOR EACH STATEMENT EXECUTE FUNCTION reject_survey_action_mutation();
                SQL);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('survey_actions');
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('DROP FUNCTION IF EXISTS reject_survey_action_mutation()');
        }
        Schema::dropIfExists('survey_answers');
        Schema::dropIfExists('survey_responses');
    }
};
