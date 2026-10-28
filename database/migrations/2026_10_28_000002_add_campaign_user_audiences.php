<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE crm_campaign_recipients ALTER COLUMN prospect_id DROP NOT NULL');
        Schema::table('crm_campaign_recipients', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
        });
        Schema::table('crm_campaigns', function (Blueprint $table) {
            $table->string('audience', 32)->default('custom');
        });
    }

    public function down(): void
    {
        if (DB::table('crm_campaign_recipients')->whereNull('prospect_id')->exists()) {
            throw new RuntimeException('User campaign recipients exist; preserve them before rolling back this migration.');
        }
        Schema::table('crm_campaign_recipients', fn (Blueprint $table) => $table->dropConstrainedForeignId('user_id'));
        Schema::table('crm_campaigns', fn (Blueprint $table) => $table->dropColumn('audience'));
        DB::statement('ALTER TABLE crm_campaign_recipients ALTER COLUMN prospect_id SET NOT NULL');
    }
};
