<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_id')->constrained('contacts')->cascadeOnDelete();
            // Preserve authorship; a manager with replies cannot be hard-deleted.
            $table->foreignId('manager_id')->constrained('managers')->restrictOnDelete();
            $table->text('message');
            $table->timestamps();
            $table->index(['contact_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_replies');
    }
};
