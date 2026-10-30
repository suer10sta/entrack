<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private function types(bool $includeD5): void
    {
        $types = "'SENT','REMINDER_SENT','Q1_CLICKED','ANSWERED','TICKET','AUTO_EMAIL','UPSELL','STOP_COMMERCIAL','RECALL_DONE','CANCELLED','EMAIL_ATTEMPTED','EMAIL_UNCONFIRMED','ACTIONS_PROCESSED','PROJECT_FOLLOW_UP','VALUE_CONFIRMED','PURCHASE_INTENT','PRICE_BARRIER','DECISION_APPROVAL','TRIAL_EXTENSION_REQUESTED','INSUFFICIENT_VALUE','COMPETITOR_CHOSEN'";
        if ($includeD5) {
            $types .= ",'ADOPTION_RISK','RENEWAL_RISK'";
        }
        DB::statement('ALTER TABLE survey_actions DROP CONSTRAINT survey_actions_action_type_check');
        DB::statement("ALTER TABLE survey_actions ADD CONSTRAINT survey_actions_action_type_check CHECK (action_type IN ($types))");
    }

    public function up(): void
    {
        $this->types(true);
    }

    public function down(): void
    {
        $this->types(false);
    }
};
