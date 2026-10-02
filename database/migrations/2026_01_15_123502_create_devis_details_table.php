<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Base table for devis line items. This table was missing its
     * create migration: only the two later alter migrations existed
     * (2026_01_15 add hours, 2026_01_16 add cost_per_hour), so a fresh
     * `php artisan migrate` failed with "relation devis_details
     * does not exist". Columns below are the original ones (before
     * hours / cost_per_hour were added), matching production
     * (old_app_backup_2026-02-24_17-00-01.sql) and the model fillable.
     */
    public function up(): void
    {
        Schema::create('devis_details', function (Blueprint $table) {
            $table->id();
            $table->integer('devis_id')->nullable();
            $table->text('task_details')->nullable();
            $table->double('task_budget')->nullable();
            $table->integer('entreprise_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devis_details');
    }
};
