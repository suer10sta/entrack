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
        // 1. Remove the column from the 'users' table
        // We check if it exists first, just in case
        if (Schema::hasColumn('users', 'type_compt')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('type_compt');
            });
        }

        // 2. Add the new column to the 'entreprises' table
        Schema::table('entreprises', function (Blueprint $table) {
            // Can be null, for free trials or new signups
            $table->string('type_compt')->nullable()->after('id'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Add the column back to 'users'
        Schema::table('users', function (Blueprint $table) {
            $table->string('type_compt')->nullable();
        });

        // 2. Remove the column from 'entreprises'
        Schema::table('entreprises', function (Blueprint $table) {
            $table->dropColumn('type_compt');
        });
    }
};