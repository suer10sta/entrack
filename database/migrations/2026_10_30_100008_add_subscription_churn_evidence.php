<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('entreprises', fn (Blueprint $t) => $t->timestampTz('survey_resumed_at')->nullable());
        Schema::table('subscriptions', function (Blueprint $t) {
            $t->timestampTz('survey_ended_at')->nullable();
            $t->timestampTz('survey_event_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('entreprises', fn (Blueprint $t) => $t->dropColumn('survey_resumed_at'));
        Schema::table('subscriptions', fn (Blueprint $t) => $t->dropColumn(['survey_ended_at', 'survey_event_at']));
    }
};
