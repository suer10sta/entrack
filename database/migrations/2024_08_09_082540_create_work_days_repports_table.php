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
        Schema::create('work_days_repports', function (Blueprint $table) {
            $table->id();
            $table->float('work_days_per_month')->nullable();
            $table->float('days_worked')->nullable();
            $table->float('sick_days')->nullable();
            $table->float('holidays')->nullable();
            $table->float('days_of_unpaid_absence')->nullable();
            $table->float('remaining_days_of_paid_leave')->nullable();
            $table->float('number_of_days_of_paid_leave_acquired')->nullable();
            $table->float('number_of_days_of_paid_leave_taken')->nullable();
            $table->float('holiday_balance')->nullable();
            $table->float('remarque')->nullable();
            $table->DATE('month')->nullable();
            $table->DATE('year')->nullable();
            $table->integer('employee_id');
            $table->unsignedBigInteger('entreprise_id')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_days_repports');
    }
};
