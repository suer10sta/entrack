<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('crm_campaigns', function (Blueprint $table) {
            $table->timestampTz('scheduled_at')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::table('crm_campaigns', function (Blueprint $table) {
            $table->dropColumn('scheduled_at');
        });
    }
};
