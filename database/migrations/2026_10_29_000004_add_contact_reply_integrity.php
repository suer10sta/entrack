<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contact_replies', function (Blueprint $table) {
            $table->uuid('submission_key')->nullable();
            $table->unique(['contact_id', 'submission_key'], 'contact_replies_submission_unique');
            $table->unique(['id', 'contact_id'], 'contact_replies_identity_unique');
        });
        if (DB::getDriverName() === 'sqlite') {
            // SQLite cannot add a composite FK to an existing table without rebuilding it.
            // Equivalent triggers cover raw SQL writes as well as Eloquent.
            foreach (['INSERT', 'UPDATE'] as $operation) {
                $name = 'contact_email_reply_'.strtolower($operation);
                DB::unprepared("CREATE TRIGGER {$name} BEFORE {$operation} ON email_sends_log WHEN NEW.contact_reply_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM contact_replies WHERE id = NEW.contact_reply_id AND contact_id = NEW.contact_id) BEGIN SELECT RAISE(ABORT, 'Contact/reply mismatch'); END");
            }
            DB::unprepared("CREATE TRIGGER contact_reply_parent_update BEFORE UPDATE OF contact_id ON contact_replies WHEN EXISTS (SELECT 1 FROM email_sends_log WHERE contact_reply_id = OLD.id AND contact_id != NEW.contact_id) BEGIN SELECT RAISE(ABORT, 'Contact/reply mismatch'); END");
        } else {
            Schema::table('email_sends_log', function (Blueprint $table) {
                $table->foreign(['contact_reply_id', 'contact_id'], 'contact_email_reply_identity_fk')
                    ->references(['id', 'contact_id'])->on('contact_replies');
            });
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            foreach (['contact_email_reply_insert', 'contact_email_reply_update', 'contact_reply_parent_update'] as $name) {
                DB::unprepared('DROP TRIGGER IF EXISTS '.$name);
            }
        } else {
            Schema::table('email_sends_log', fn (Blueprint $table) => $table->dropForeign('contact_email_reply_identity_fk'));
        }
        Schema::table('contact_replies', function (Blueprint $table) {
            $table->dropUnique('contact_replies_submission_unique');
            $table->dropUnique('contact_replies_identity_unique');
            $table->dropColumn('submission_key');
        });
    }
};
