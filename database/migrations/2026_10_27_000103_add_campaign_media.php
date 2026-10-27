<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE crm_campaigns ADD COLUMN IF NOT EXISTS media JSONB');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE crm_campaigns DROP COLUMN IF EXISTS media');
    }
};
