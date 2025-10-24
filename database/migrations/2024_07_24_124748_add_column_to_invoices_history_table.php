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
        Schema::table('invoices_history', function (Blueprint $table) {
            $table->dropColumn('cost');
            $table->float('total_htva')->nullable();
            $table->float('total_tva')->nullable();
            $table->float('total_ttc')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices_history', function (Blueprint $table) {
            //
        });
    }
};
