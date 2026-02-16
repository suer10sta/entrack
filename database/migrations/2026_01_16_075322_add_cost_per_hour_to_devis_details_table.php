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
        Schema::table('devis_details', function (Blueprint $table) {
            if (!Schema::hasColumn('devis_details', 'cost_per_hour')) {
                $table->decimal('cost_per_hour', 12, 2)->nullable()->after('hours');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('devis_details', function (Blueprint $table) {
            if (Schema::hasColumn('devis_details', 'cost_per_hour')) {
                $table->dropColumn('cost_per_hour');
            }
        });
    }
};
