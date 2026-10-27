<?php

use App\Enums\ProspectActivityType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prospect_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prospect_id')->constrained('prospects')->restrictOnDelete();
            $table->foreignId('manager_id')->nullable()->constrained('managers')->nullOnDelete();
            $table->enum('type', array_column(ProspectActivityType::cases(), 'value'));
            $table->string('subject')->nullable();
            $table->text('body')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->timestampTz('occurred_at');
            $table->timestampTz('due_at')->nullable()->index();
            $table->timestampTz('completed_at')->nullable();
            $table->timestampsTz();
            $table->index(['prospect_id', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prospect_activities');
    }
};
