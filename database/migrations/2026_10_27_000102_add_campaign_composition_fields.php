<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE crm_campaigns ADD COLUMN IF NOT EXISTS preheader VARCHAR(255), ADD COLUMN IF NOT EXISTS utm_campaign VARCHAR(255), ADD COLUMN IF NOT EXISTS cta_text VARCHAR(120), ADD COLUMN IF NOT EXISTS cta_url TEXT');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE crm_campaigns DROP COLUMN IF EXISTS preheader, DROP COLUMN IF EXISTS utm_campaign, DROP COLUMN IF EXISTS cta_text, DROP COLUMN IF EXISTS cta_url');
    }
};
