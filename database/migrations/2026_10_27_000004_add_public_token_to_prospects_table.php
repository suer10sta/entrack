<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prospects', function (Blueprint $table) {
            $table->string('public_token', 48)->nullable()->unique();
        });
        // Include archived contacts. Preserve all existing statuses and business dates.
        DB::table('prospects')->orderBy('id')->chunkById(500, function ($prospects) {
            foreach ($prospects as $prospect) {
                DB::table('prospects')->where('id', $prospect->id)->update(['public_token' => bin2hex(random_bytes(24))]);
            }
        });
        Schema::table('prospects', function (Blueprint $table) {
            $table->string('public_token', 48)->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('prospects', function (Blueprint $table) {
            $table->dropUnique(['public_token']);
            $table->dropColumn('public_token');
        });
    }
};
