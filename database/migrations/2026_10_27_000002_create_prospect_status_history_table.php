<?php

use App\Enums\ProspectStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prospect_status_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prospect_id')->constrained('prospects')->restrictOnDelete();
            $table->enum('from_status', array_column(ProspectStatus::cases(), 'value'));
            $table->enum('to_status', array_column(ProspectStatus::cases(), 'value'));
            $table->timestampTz('occurred_at');
            $table->string('source', 64);
            $table->foreignId('manager_id')->nullable()->constrained('managers')->nullOnDelete();
            $table->text('note')->nullable();
            $table->string('idempotency_key', 191)->nullable();
            $table->foreignId('lifecycle_event_id')->nullable()->constrained('crm_lifecycle_events')->restrictOnDelete();
            $table->timestampsTz();
            $table->index(['prospect_id', 'occurred_at']);
            $table->unique(['prospect_id', 'source', 'idempotency_key'], 'prospect_status_history_idempotency_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prospect_status_history');
    }
};
