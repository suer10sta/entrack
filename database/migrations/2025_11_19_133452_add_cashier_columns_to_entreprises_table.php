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
            //
            $table->string('stripe_id')->nullable();
        $table->string('pm_type')->nullable();
        $table->string('pm_last_four')->nullable();
        $table->string('stripe_subscription_id')->nullable(); 
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('entreprises', function (Blueprint $table) {
            //
            $table->dropColumn([
                'stripe_id',
                'pm_type',
                'pm_last_four',
                'stripe_subscription_id',
            ]);
        });
    }
};
