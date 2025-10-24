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
        Schema::create('note_de_frais', function (Blueprint $table) {
            $table->id();
            $table->integer('note_number')->default('0');
            $table->string('employee_name');
            $table->text('description')->nullable();
            $table->float('budget')->nullable();
            $table->unsignedBigInteger('entreprise_id')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('note_de_frais');
    }
};
