<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE survey_actions DROP CONSTRAINT survey_actions_action_type_check');
            DB::statement("ALTER TABLE survey_actions ADD CONSTRAINT survey_actions_action_type_check CHECK (action_type IN ('SENT','REMINDER_SENT','Q1_CLICKED','ANSWERED','TICKET','AUTO_EMAIL','UPSELL','STOP_COMMERCIAL','RECALL_DONE','CANCELLED','EMAIL_ATTEMPTED','EMAIL_UNCONFIRMED'))");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            // Intentionally refuses rollback if newer delivery receipts still exist.
            DB::statement('ALTER TABLE survey_actions DROP CONSTRAINT survey_actions_action_type_check');
            DB::statement("ALTER TABLE survey_actions ADD CONSTRAINT survey_actions_action_type_check CHECK (action_type IN ('SENT','REMINDER_SENT','Q1_CLICKED','ANSWERED','TICKET','AUTO_EMAIL','UPSELL','STOP_COMMERCIAL','RECALL_DONE','CANCELLED'))");
        }
    }
};
