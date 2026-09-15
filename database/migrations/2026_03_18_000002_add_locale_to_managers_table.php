<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('managers', function (Blueprint $table) {
            if (!Schema::hasColumn('managers', 'locale')) {
                $table->string('locale', 10)->nullable()->after('email');
            }
        });
    }

    public function down(): void
    {
        Schema::table('managers', function (Blueprint $table) {
            if (Schema::hasColumn('managers', 'locale')) {
                $table->dropColumn('locale');
            }
        });
    }
};


