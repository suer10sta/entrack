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
        Schema::table('devis_history', function (Blueprint $table) {
            if (!Schema::hasColumn('devis_history', 'billing_type')) {
                $table->string('billing_type')->default('forfait')->after('budget');
            }
            if (!Schema::hasColumn('devis_history', 'hours')) {
                $table->decimal('hours', 12, 2)->nullable()->after('billing_type');
            }
            if (!Schema::hasColumn('devis_history', 'amount_per_hour')) {
                $table->decimal('amount_per_hour', 12, 2)->nullable()->after('hours');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('devis_history', function (Blueprint $table) {
            if (Schema::hasColumn('devis_history', 'amount_per_hour')) {
                $table->dropColumn('amount_per_hour');
            }
            if (Schema::hasColumn('devis_history', 'hours')) {
                $table->dropColumn('hours');
            }
            if (Schema::hasColumn('devis_history', 'billing_type')) {
                $table->dropColumn('billing_type');
            }
        });
    }
};

