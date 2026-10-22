<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('entreprises', function (Blueprint $table) {
            $table->string('invoice_number_format')->default('year_month_number');
            $table->string('invoice_prefix')->nullable()->default('F')->change();
        });
        Schema::table('invoices_history', function (Blueprint $table) {
            // Only new invoices get a frozen reference; historical rows stay untouched.
            $table->text('invoice_reference')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('invoices_history', fn (Blueprint $table) => $table->dropColumn('invoice_reference'));
        Schema::table('entreprises', function (Blueprint $table) {
            $table->dropColumn('invoice_number_format');
            $table->string('invoice_prefix')->nullable()->default(null)->change();
        });
    }
};
