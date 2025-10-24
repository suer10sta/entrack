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
        Schema::table('employees', function (Blueprint $table) {
            $table->string('name')->nullable()->change();
            $table->string('email')->nullable()->change();
            $table->string('password')->nullable()->change();
            $table->string('phoneNumber')->nullable()->change();
            $table->string('age')->nullable()->change();
            $table->string('cnss')->nullable()->change();
            $table->string('bankAccountNumber')->nullable()->change();
            $table->string('Date_Entree')->nullable()->change();
            $table->string('Date_Release')->nullable()->change();
            $table->string('user_id')->nullable()->change();
            $table->string('status')->nullable()->change();
            $table->string('salary')->nullable()->change();
            $table->string('Cotisation_CNSS')->nullable()->change();
            $table->string('Cotisation_AMO')->nullable()->change();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            //
        });
    }
};
