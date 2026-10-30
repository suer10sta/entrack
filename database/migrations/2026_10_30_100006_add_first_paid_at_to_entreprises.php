<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('entreprises', function (Blueprint $t) {
            $t->timestampTz('first_paid_at')->nullable()->index();
            $t->string('first_paid_invoice_id')->nullable()->unique();
            $t->string('first_paid_lookup_customer_id')->nullable()->index();
        });
        DB::statement('ALTER TABLE entreprises ADD CONSTRAINT entreprises_first_payment_pair CHECK ((first_paid_at IS NULL) = (first_paid_invoice_id IS NULL))');
        DB::unprepared("CREATE FUNCTION preserve_first_subscription_payment() RETURNS trigger LANGUAGE plpgsql AS \$\$ BEGIN IF OLD.first_paid_at IS NOT NULL AND (NEW.first_paid_at IS DISTINCT FROM OLD.first_paid_at OR NEW.first_paid_invoice_id IS DISTINCT FROM OLD.first_paid_invoice_id) THEN RAISE EXCEPTION 'First subscription payment is immutable' USING ERRCODE = '23514'; END IF; RETURN NEW; END; \$\$; CREATE TRIGGER preserve_first_subscription_payment BEFORE UPDATE ON entreprises FOR EACH ROW EXECUTE FUNCTION preserve_first_subscription_payment();");
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER preserve_first_subscription_payment ON entreprises; DROP FUNCTION preserve_first_subscription_payment();');
        DB::statement('ALTER TABLE entreprises DROP CONSTRAINT entreprises_first_payment_pair');
        Schema::table('entreprises', function (Blueprint $t) {
            $t->dropColumn(['first_paid_at', 'first_paid_invoice_id', 'first_paid_lookup_customer_id']);
        });
    }
};
