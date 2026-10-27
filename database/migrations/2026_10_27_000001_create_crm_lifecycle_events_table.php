<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_lifecycle_events', function (Blueprint $table) {
            $table->id();
            $table->string('source', 64);
            $table->string('event_key', 191);
            $table->string('event_type', 100);
            $table->foreignId('prospect_id')->nullable()->constrained('prospects')->restrictOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('entreprise_id')->nullable()->constrained('entreprises')->nullOnDelete();
            $table->jsonb('payload')->nullable();
            $table->timestampTz('occurred_at');
            $table->timestampTz('processed_at')->nullable()->index();
            $table->timestampsTz();
            $table->unique(['source', 'event_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_lifecycle_events');
    }
};
