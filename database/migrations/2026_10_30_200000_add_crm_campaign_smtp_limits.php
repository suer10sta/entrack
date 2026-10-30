<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('crm_campaign_recipients', function (Blueprint $table) {
            $table->timestampTz('next_attempt_at')->nullable()->index();
            $table->unsignedInteger('attempts_count')->default(0);
            $table->unsignedSmallInteger('last_smtp_code')->nullable();
        });
        // Separate ledger: retries must not erase earlier attempts in the window.
        Schema::create('crm_campaign_smtp_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recipient_id')->nullable()->constrained('crm_campaign_recipients')->nullOnDelete();
            $table->string('account_key', 64);
            $table->timestampTz('attempted_at');
            $table->index('recipient_id');
            $table->index(['account_key', 'attempted_at'], 'crm_smtp_attempts_account_time');
        });
        Schema::create('crm_campaign_smtp_pauses', function (Blueprint $table) {
            $table->string('account_key', 64)->primary();
            $table->timestampTz('paused_until');
        });
        Schema::create('crm_campaign_daily_launches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manager_id')->nullable()->constrained('managers')->nullOnDelete();
            $table->foreignId('campaign_id')->nullable()->constrained('crm_campaigns')->nullOnDelete();
            $table->date('launch_date');
            $table->timestampTz('launched_at');
            $table->unique(['manager_id', 'launch_date']);
        });
        // Scheduling previously had no confirmation timestamp: updated_at is the
        // best available fallback for historical reservations, including soft deletes.
        DB::statement("INSERT INTO crm_campaign_daily_launches (manager_id, campaign_id, launch_date, launched_at)
            SELECT DISTINCT ON (manager_id, (COALESCE(queued_at, started_at, updated_at) AT TIME ZONE 'UTC')::date)
                manager_id, id, (COALESCE(queued_at, started_at, updated_at) AT TIME ZONE 'UTC')::date,
                COALESCE(queued_at, started_at, updated_at)
            FROM crm_campaigns WHERE status <> 'draft' AND manager_id IS NOT NULL
            ORDER BY manager_id, (COALESCE(queued_at, started_at, updated_at) AT TIME ZONE 'UTC')::date, id");
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_campaign_daily_launches');
        Schema::dropIfExists('crm_campaign_smtp_pauses');
        Schema::dropIfExists('crm_campaign_smtp_attempts');
        Schema::table('crm_campaign_recipients', fn (Blueprint $table) => $table->dropColumn(['next_attempt_at', 'attempts_count', 'last_smtp_code']));
    }
};
