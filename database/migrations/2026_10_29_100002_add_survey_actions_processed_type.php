<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE survey_actions DROP CONSTRAINT survey_actions_action_type_check');
        DB::statement("ALTER TABLE survey_actions ADD CONSTRAINT survey_actions_action_type_check CHECK (action_type IN ('SENT','REMINDER_SENT','Q1_CLICKED','ANSWERED','TICKET','AUTO_EMAIL','UPSELL','STOP_COMMERCIAL','RECALL_DONE','CANCELLED','EMAIL_ATTEMPTED','EMAIL_UNCONFIRMED','ACTIONS_PROCESSED'))");
    }

    public function down(): void
    {
        // The migration transaction refuses rollback while newer receipts exist.
        DB::statement('ALTER TABLE survey_actions DROP CONSTRAINT survey_actions_action_type_check');
        DB::statement("ALTER TABLE survey_actions ADD CONSTRAINT survey_actions_action_type_check CHECK (action_type IN ('SENT','REMINDER_SENT','Q1_CLICKED','ANSWERED','TICKET','AUTO_EMAIL','UPSELL','STOP_COMMERCIAL','RECALL_DONE','CANCELLED','EMAIL_ATTEMPTED','EMAIL_UNCONFIRMED'))");
    }
};
