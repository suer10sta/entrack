<?php

use App\Enums\ContactSource;
use App\Enums\ContactStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A locked, persistent allocator: deleting tickets never resets numbering.
        Schema::create('contact_ticket_sequences', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->unsignedBigInteger('next_number');
        });
        DB::table('contact_ticket_sequences')->insert(['id' => 1, 'next_number' => 1]);

        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->ipAddress('ip')->nullable();
            $table->string('name');
            $table->string('email')->index();
            $table->string('subject');
            $table->text('message');
            // Historical snapshot: a User with this email existed at creation.
            $table->boolean('is_active_acc')->default(false);
            $table->enum('status', array_column(ContactStatus::cases(), 'value'))->default(ContactStatus::Pending->value);
            $table->enum('source', array_column(ContactSource::cases(), 'value'))->default(ContactSource::Contact->value);
            $table->string('ticket_num', 32)->unique();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('entreprise_id')->nullable()->constrained('entreprises')->nullOnDelete();
            $table->timestamps();
            $table->index(['status', 'created_at']);
            $table->index(['source', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contacts');
        Schema::dropIfExists('contact_ticket_sequences');
    }
};
