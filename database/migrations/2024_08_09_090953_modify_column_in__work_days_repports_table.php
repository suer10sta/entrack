<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('work_days_repports', function (Blueprint $table) {
            $table->text('remarque')->nullable()->change();
            $table->string('month')->nullable()->change();
            $table->string('year')->nullable()->change();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('work_days_repports', function (Blueprint $table) {
            //
        });
    }
};
