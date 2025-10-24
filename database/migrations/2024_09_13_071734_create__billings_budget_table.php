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
        Schema::create('_billings_budget', function (Blueprint $table) {
            $table->id();
            $table->integer('number');
            $table->float('budget');
            $table->float('budget_spent')->default(0);;
            $table->float('budget_remaining');
            $table->unsignedBigInteger('entreprise_id')->default(0);
            $table->float('sould_out')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('_billings_budget');
    }
};
