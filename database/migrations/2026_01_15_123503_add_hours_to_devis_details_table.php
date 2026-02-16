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
            if (!Schema::hasColumn('devis_details', 'hours')) {
                $table->decimal('hours', 12, 2)->nullable()->after('task_details');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('devis_details', function (Blueprint $table) {
            if (Schema::hasColumn('devis_details', 'hours')) {
                $table->dropColumn('hours');
            }
        });
    }
};
