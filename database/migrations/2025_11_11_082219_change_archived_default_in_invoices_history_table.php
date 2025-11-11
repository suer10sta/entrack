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
        Schema::table('invoices_history', function (Blueprint $table) {
            //
            DB::table('invoices_history')->whereNull('archived')->update(['archived' => 0]);
            
            // 2. Now, change the column to have a default of 0 and be non-nullable
            $table->boolean('archived')->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices_history', function (Blueprint $table) {
            //
            $table->boolean('archived')->nullable()->default(null)->change();
        });
    }
};
