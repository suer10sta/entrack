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
        Schema::table('_billings_budget', function (Blueprint $table) {
            //
            $table->renameColumn('sould_out', 'sold_out');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('_billings_budget', function (Blueprint $table) {
            //
            $table->renameColumn('sold_out', 'sould_out');
        });
    }
};
