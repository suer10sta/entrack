<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('crm_campaign_recipients', function (Blueprint $table) {
            $table->string('tracking_token', 64)->nullable()->unique();
            $table->timestampTz('first_opened_at')->nullable();
            $table->unsignedBigInteger('open_count')->default(0);
            $table->timestampTz('first_clicked_at')->nullable();
            $table->unsignedBigInteger('click_count')->default(0);
            $table->text('last_clicked_url')->nullable();
        });
        Schema::create('crm_campaign_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recipient_id')->constrained('crm_campaign_recipients')->cascadeOnDelete();
            $table->string('token', 64)->unique();
            $table->string('destination_hash', 64);
            $table->text('destination');
            $table->timestampTz('first_clicked_at')->nullable();
            $table->unsignedBigInteger('click_count')->default(0);
            $table->timestampsTz();
            $table->unique(['recipient_id', 'destination_hash']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_campaign_links');
        Schema::table('crm_campaign_recipients', function (Blueprint $table) {
            $table->dropUnique(['tracking_token']);
            $table->dropColumn(['tracking_token', 'first_opened_at', 'open_count', 'first_clicked_at', 'click_count', 'last_clicked_url']);
        });
    }
};
