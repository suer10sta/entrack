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
        Schema::table('entreprises', function (Blueprint $table) {
            $table->string('country')->nullable()->after('Address');
            $table->string('city')->nullable()->after('country');
            $table->string('zip_code')->nullable()->after('city');
        });

        $rows = Schema::getConnection()
            ->table('entreprises')
            ->whereNotNull('Address')
            ->where('Address', '!=', '')
            ->select('id', 'Address')
            ->get();

        foreach ($rows as $row) {
            Schema::getConnection()
                ->table('entreprises')
                ->where('id', $row->id)
                ->update(['country' => $row->Address]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('entreprises', function (Blueprint $table) {
            $table->dropColumn(['country', 'city', 'zip_code']);
        });
    }
};

