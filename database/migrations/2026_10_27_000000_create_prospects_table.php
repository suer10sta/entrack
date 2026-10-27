<?php

use App\Enums\ProspectStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prospects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('email_normalized')->nullable()->index();
            $table->string('phone', 64)->nullable();
            $table->string('company_name')->nullable();
            $table->string('country', 100)->nullable();
            $table->enum('status', array_column(ProspectStatus::cases(), 'value'))->default(ProspectStatus::Nouveau->value)->index();
            $table->foreignId('owner_manager_id')->nullable()->constrained('managers')->nullOnDelete();
            $table->string('source')->nullable();
            foreach (['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term'] as $name) {
                $table->string($name)->nullable();
            }
            $table->timestampTz('source_captured_at')->nullable();
            $table->foreignId('linked_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('linked_entreprise_id')->nullable()->constrained('entreprises')->nullOnDelete();
            $table->timestampTz('linked_at')->nullable();
            $table->timestampTz('captured_at')->useCurrent();
            $table->timestampTz('status_changed_at')->nullable();
            $table->timestampTz('trial_started_at')->nullable();
            $table->timestampTz('first_paid_at')->nullable();
            $table->timestampTz('next_follow_up_at')->nullable()->index();
            $table->timestampsTz();
            $table->softDeletesTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prospects');
    }
};
