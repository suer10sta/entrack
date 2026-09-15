<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('visits', function (Blueprint $table) {
            $table->timestamp('last_activity_at')->nullable()->after('updated_at');
            $table->timestamp('ended_at')->nullable()->after('last_activity_at');
            $table->unsignedInteger('duration_seconds')->nullable()->after('ended_at');
        });
    }

    public function down(): void
    {
        Schema::table('visits', function (Blueprint $table) {
            $table->dropColumn(['last_activity_at', 'ended_at', 'duration_seconds']);
        });
    }
};

