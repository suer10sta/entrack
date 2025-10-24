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
        Schema::table('entreprises', function (Blueprint $table) {
            $table->integer('tva')->nullable()->change();
            $table->string('bank_name')->nullable()->change();
            $table->string('iban')->nullable()->change();
            $table->string('swift')->nullable()->change();
            $table->string('bic')->nullable()->change();
            $table->string('email')->nullable()->change();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('entreprises', function (Blueprint $table) {
            //
        });
    }
};
