<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('visits', function (Blueprint $table) {
            $table->boolean('is_proxy')->nullable()->after('device_type');
            $table->boolean('is_hosting')->nullable()->after('is_proxy');
        });
    }

    public function down(): void
    {
        Schema::table('visits', function (Blueprint $table) {
            $table->dropColumn(['is_proxy', 'is_hosting']);
        });
    }
};

