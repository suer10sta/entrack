<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('visits', function (Blueprint $table) {
            $table->string('referrer')->nullable()->after('url');
            $table->string('utm_source', 100)->nullable()->after('referrer');
            $table->string('device_type', 50)->nullable()->after('utm_source');
        });
    }

    public function down(): void
    {
        Schema::table('visits', function (Blueprint $table) {
            $table->dropColumn(['referrer', 'utm_source', 'device_type']);
        });
    }
};


