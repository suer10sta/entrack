<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics_contexts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('entreprise_id')->unique();
            $table->text('context');
            $table->timestamps();
        });
        Schema::create('analytics_business_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_key', 64)->unique();
            $table->string('event_name', 40);
            $table->string('status', 20)->default('pending');
            $table->text('payload');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_business_events');
        Schema::dropIfExists('analytics_contexts');
    }
};
