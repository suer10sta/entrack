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
        Schema::create('credit_note', function (Blueprint $table) {
            $table->id();
            $table->string('project_name');
            $table->integer('number');
            $table->string('link');
            $table->integer('client_id');
            $table->integer('project_id');
            $table->date('due_date')->nullable();
            $table->date('end_date')->nullable();
            $table->date('start_date')->nullable();
            $table->string('type');
            $table->integer('Payment_status')->nullable();
            $table->integer('sentReminder');
            $table->integer('sentInvoice')->default('0');
            $table->float('duration')->nullable();
            $table->float('total_htva')->nullable();
            $table->float('total_tva')->nullable();
            $table->float('total_ttc')->nullable();
            $table->string('credit_note_type')->nullable();
            $table->unsignedBigInteger('entreprise_id')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credit_note');
    }
};
