<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')
            ->whereNull('email_verified_at')
            ->update(['email_verified_at' => now()]);
    }

    public function down(): void
    {
        // Intentionally a no-op: this is a one-time backfill that grandfathers
        // accounts created before email verification was introduced.
        // Reverting must not nullify email_verified_at, otherwise all users
        // (including legitimately verified ones) would be forced to re-verify.
    }
};
