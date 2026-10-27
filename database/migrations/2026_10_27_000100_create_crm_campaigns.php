<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manager_id')->nullable()->constrained('managers')->nullOnDelete();
            $table->string('name');
            $table->string('subject');
            $table->text('content');
            $table->string('status', 32)->default('draft')->index();
            $table->unsignedInteger('revision')->default(0);
            $table->timestampTz('queued_at')->nullable();
            $table->timestampTz('started_at')->nullable();
            $table->timestampTz('completed_at')->nullable();
            $table->timestampsTz();
        });
        Schema::create('crm_campaign_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('crm_campaigns')->cascadeOnDelete();
            $table->foreignId('prospect_id')->constrained('prospects')->restrictOnDelete();
            $table->string('email');
            $table->string('email_normalized');
            $table->string('name');
            $table->string('status', 32)->default('selected');
            $table->timestampTz('attempted_at')->nullable()->index();
            $table->timestampTz('sent_at')->nullable();
            $table->timestampTz('crm_synced_at')->nullable();
            $table->string('error')->nullable();
            $table->string('crm_error')->nullable();
            $table->timestampsTz();
            $table->unique(['campaign_id', 'prospect_id']);
            $table->unique(['campaign_id', 'email_normalized']);
            $table->index(['campaign_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_campaign_recipients');
        Schema::dropIfExists('crm_campaigns');
    }
};
