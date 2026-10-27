<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Existing installs may have run the first campaign migration before revision was added.
        // PostgreSQL resolves the active schema and safely handles fresh installs too.
        DB::statement('ALTER TABLE crm_campaigns ADD COLUMN IF NOT EXISTS revision INTEGER NOT NULL DEFAULT 0');
    }

    public function down(): void
    {
        // Intentionally keep this additive compatibility column: the creation migration
        // also owns it on fresh installs, and existing campaigns must retain revisions.
    }
};
