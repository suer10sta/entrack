<?php

namespace Tests\Feature;

use App\Enums\ProspectActivityType;
use App\Enums\ProspectStatus;
use App\Models\CrmLifecycleEvent;
use App\Models\Manager;
use App\Models\Prospect;
use App\Models\ProspectStatusHistory;
use App\Services\ProspectStatusService;
use DomainException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;
use LogicException;
use RuntimeException;
use Tests\TestCase;

class ProspectFoundationTest extends TestCase
{
    private ?string $isolatedSchema = null;

    private array $migrations = [];

    protected function setUp(): void
    {
        parent::setUp();
        if (getenv('CRM_TEST_POSTGRES') === '1') {
            // Optional PostgreSQL run: dedicated testing DB + disposable schema only.
            $database = config('database.connections.pgsql.database');
            if (! is_string($database) || ! preg_match('/(_test|_testing)$/', $database) || $database === $this->developmentDatabaseName()) {
                throw new RuntimeException('CRM PostgreSQL tests require a dedicated testing database.');
            }
            config(['database.default' => 'pgsql']);
            $this->isolatedSchema = 'crm_test_'.bin2hex(random_bytes(8));
            DB::statement('CREATE SCHEMA "'.$this->isolatedSchema.'"');
            DB::statement('SET search_path TO "'.$this->isolatedSchema.'"');
        } else {
            config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        }
        Schema::create('managers', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->timestamps();
        });
        Schema::create('entreprises', function ($table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });
        Schema::create('users', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->unsignedBigInteger('entreprise_id');
            $table->timestamps();
        });
        foreach (glob(database_path('migrations/2026_10_27_00000*_*.php')) as $path) {
            $migration = require $path;
            $migration->up();
            $this->migrations[] = $migration;
        }
    }

    protected function tearDown(): void
    {
        try {
            if ($this->isolatedSchema !== null) {
                DB::statement('SET search_path TO public');
                DB::statement('DROP SCHEMA "'.$this->isolatedSchema.'" CASCADE');
            }
        } finally {
            parent::tearDown();
        }
    }

    private function prospect(array $attributes = []): Prospect
    {
        return Prospect::create(array_merge(['name' => 'CRM test', 'email' => 'Test.Contact+Launch@Example.test'], $attributes));
    }

    private function manager(): Manager
    {
        return Manager::create(['name' => 'Manager test', 'email' => 'manager@example.test']);
    }

    public function test_creation_has_default_status_nullable_links_and_no_tenant_owner(): void
    {
        $prospect = $this->prospect();
        $this->assertSame(ProspectStatus::Nouveau, $prospect->status);
        $this->assertSame(ProspectStatus::Nouveau, $prospect->fresh()->status);
        $this->assertNull($prospect->linked_user_id);
        $this->assertNull($prospect->linked_entreprise_id);
        $this->assertFalse(Schema::hasColumn('prospects', 'entreprise_id'));
        $this->assertSame('Démo / Essai', ProspectStatus::DemoEssai->label());
    }

    public function test_email_normalization_preserves_original_dots_and_plus_and_is_not_unique(): void
    {
        $email = '  Test.Contact+Launch@Example.test  ';
        $prospect = $this->prospect(['email' => $email]);
        $this->assertSame($email, $prospect->email);
        $this->assertSame('test.contact+launch@example.test', $prospect->email_normalized);
        $this->prospect(['email' => 'test.contact+launch@example.test']);
        $prospect->update(['email' => ' Other+Tag@EXAMPLE.test ']);
        $this->assertSame('other+tag@example.test', $prospect->fresh()->email_normalized);
        $prospect->update(['email' => null]);
        $this->assertNull($prospect->fresh()->email_normalized);
    }

    public function test_relations_to_user_company_manager_activities_history_and_events(): void
    {
        DB::table('entreprises')->insert(['id' => 1, 'name' => 'Company test']);
        DB::table('users')->insert(['id' => 1, 'name' => 'Account test', 'email' => 'account@example.test', 'entreprise_id' => 1]);
        $manager = $this->manager();
        $prospect = $this->prospect(['linked_user_id' => 1, 'linked_entreprise_id' => 1, 'owner_manager_id' => $manager->id, 'linked_at' => now()]);
        $activity = $prospect->activities()->create(['type' => ProspectActivityType::FollowUp, 'manager_id' => $manager->id, 'subject' => 'Call back', 'occurred_at' => now(), 'due_at' => now()->addDay(), 'metadata' => ['channel' => 'phone']]);
        $updated = app(ProspectStatusService::class)->transition($prospect, ProspectStatus::Contacte, 'manual', $manager, 'First contact', 'manual:contact:1');
        $this->assertSame(1, $updated->linkedUser->id);
        $this->assertSame(1, $updated->linkedEntreprise->id);
        $this->assertTrue($updated->ownerManager->is($manager));
        $this->assertSame(ProspectActivityType::FollowUp, $activity->fresh()->type);
        $this->assertTrue($activity->prospect->is($prospect));
        $this->assertTrue($activity->manager->is($manager));
        $history = $updated->statusHistories()->first();
        $this->assertSame(ProspectStatus::Nouveau, $history->from_status);
        $this->assertSame(ProspectStatus::Contacte, $history->to_status);
        $this->assertTrue($history->manager->is($manager));
        $this->assertTrue($history->prospect->is($prospect));
        $this->assertTrue($history->lifecycleEvent->prospect->is($prospect));
        $this->assertSame(1, $updated->lifecycleEvents()->count());
    }

    public function test_transitions_append_history_and_same_status_is_a_no_op(): void
    {
        $service = app(ProspectStatusService::class);
        $prospect = $this->prospect();
        $service->transition($prospect, ProspectStatus::Contacte, 'system');
        $service->transition($prospect, ProspectStatus::Engage, 'system');
        $service->transition($prospect, ProspectStatus::Engage, 'system');
        $this->assertSame(ProspectStatus::Engage, $prospect->fresh()->status);
        $this->assertSame(2, $prospect->statusHistories()->count());
        $this->assertSame(['nouveau', 'contacte'], $prospect->statusHistories()->orderBy('id')->get()->map(fn ($row) => $row->from_status->value)->all());
    }

    public function test_idempotent_replay_never_reapplies_a_late_status(): void
    {
        $service = app(ProspectStatusService::class);
        $prospect = $this->prospect();
        $service->transition($prospect, ProspectStatus::DemoEssai, 'registration', idempotencyKey: 'account:1');
        $service->transition($prospect, ProspectStatus::DemoEssai, 'registration', idempotencyKey: 'account:1');
        $service->transition($prospect, ProspectStatus::Client, 'stripe', idempotencyKey: 'evt_paid_1');
        $result = $service->transition($prospect, ProspectStatus::DemoEssai, 'registration', idempotencyKey: 'account:1');
        $this->assertSame(ProspectStatus::Client, $result->status);
        $this->assertSame(2, $prospect->statusHistories()->count());
        $this->assertSame(2, CrmLifecycleEvent::whereNotNull('processed_at')->count());
    }

    public function test_no_op_idempotency_key_is_remembered_even_after_later_manual_progression(): void
    {
        $service = app(ProspectStatusService::class);
        $manager = $this->manager();
        $prospect = $this->prospect();
        $service->transition($prospect, ProspectStatus::Nouveau, 'manual', $manager, idempotencyKey: 'manual:no-op:1');
        $service->transition($prospect, ProspectStatus::Contacte, 'manual', $manager);
        $result = $service->transition($prospect, ProspectStatus::Nouveau, 'manual', $manager, idempotencyKey: 'manual:no-op:1');
        $this->assertSame(ProspectStatus::Contacte, $result->status);
        $this->assertSame(1, $prospect->statusHistories()->count());
    }

    public function test_client_cannot_automatically_regress_to_trial(): void
    {
        $service = app(ProspectStatusService::class);
        $prospect = $this->prospect();
        $service->transition($prospect, ProspectStatus::Client, 'stripe');
        try {
            $service->transition($prospect, ProspectStatus::DemoEssai, 'registration', idempotencyKey: 'late:registration');
            $this->fail('Automatic regression accepted.');
        } catch (DomainException $e) {
            $this->assertSame(ProspectStatus::Client, $prospect->fresh()->status);
            $this->assertSame(1, $prospect->statusHistories()->count());
            $this->assertSame(0, CrmLifecycleEvent::count());
        }
    }

    public function test_automatic_backward_progression_loss_and_reopening_are_rejected(): void
    {
        $service = app(ProspectStatusService::class);
        $prospect = $this->prospect();
        $service->transition($prospect, ProspectStatus::Engage, 'system');
        foreach ([ProspectStatus::Contacte, ProspectStatus::Perdu] as $target) {
            try {
                $service->transition($prospect, $target, 'campaign');
                $this->fail('Invalid transition accepted.');
            } catch (DomainException $e) {
                $this->assertSame(ProspectStatus::Engage, $prospect->fresh()->status);
            }
        }
        $service->transition($prospect, ProspectStatus::Perdu, 'manual', $this->manager());
        $this->expectException(DomainException::class);
        $service->transition($prospect, ProspectStatus::DemoEssai, 'registration');
    }

    public function test_manual_change_requires_manager_and_can_explicitly_reopen_a_lost_prospect(): void
    {
        $prospect = $this->prospect();
        $service = app(ProspectStatusService::class);
        try {
            $service->transition($prospect, ProspectStatus::Contacte);
            $this->fail('Missing Manager accepted.');
        } catch (InvalidArgumentException $e) {
            $this->assertSame(0, $prospect->statusHistories()->count());
        }
        $manager = $this->manager();
        $service->transition($prospect, ProspectStatus::Perdu, 'manual', $manager);
        $service->transition($prospect, ProspectStatus::Contacte, 'manual', $manager);
        $this->assertSame(ProspectStatus::Contacte, $prospect->fresh()->status);
    }

    public function test_idempotency_key_cannot_be_reused_for_another_target_or_prospect(): void
    {
        $service = app(ProspectStatusService::class);
        $prospect = $this->prospect();
        $service->transition($prospect, ProspectStatus::DemoEssai, 'registration', idempotencyKey: 'registration:1');
        foreach ([[$prospect, ProspectStatus::Client], [$this->prospect(), ProspectStatus::DemoEssai]] as [$other, $target]) {
            try {
                $service->transition($other, $target, 'registration', idempotencyKey: 'registration:1');
                $this->fail('Conflicting key accepted.');
            } catch (DomainException $e) {
                $this->assertSame(1, ProspectStatusHistory::count());
            }
        }
    }

    public function test_failed_history_insert_rolls_back_status_and_receipt(): void
    {
        $prospect = $this->prospect();
        ProspectStatusHistory::creating(fn () => throw new RuntimeException('Simulated history failure'));
        try {
            app(ProspectStatusService::class)->transition($prospect, ProspectStatus::DemoEssai, 'registration', idempotencyKey: 'atomic:1');
            $this->fail('Expected history failure.');
        } catch (RuntimeException $e) {
            $this->assertSame(ProspectStatus::Nouveau, $prospect->fresh()->status);
            $this->assertSame(0, CrmLifecycleEvent::count());
            $this->assertSame(0, ProspectStatusHistory::count());
        } finally {
            ProspectStatusHistory::flushEventListeners();
        }
    }

    public function test_direct_model_status_changes_are_rejected(): void
    {
        $prospect = $this->prospect();
        $prospect->status = ProspectStatus::Client;
        $this->expectException(LogicException::class);
        $prospect->save();
    }

    public function test_soft_deletion_preserves_timeline_and_deleting_linked_records_nulls_links(): void
    {
        DB::table('entreprises')->insert(['id' => 1, 'name' => 'Company test']);
        DB::table('users')->insert(['id' => 1, 'name' => 'Account test', 'email' => 'account@example.test', 'entreprise_id' => 1]);
        $prospect = $this->prospect(['linked_user_id' => 1, 'linked_entreprise_id' => 1]);
        app(ProspectStatusService::class)->transition($prospect, ProspectStatus::DemoEssai, 'registration');
        DB::table('users')->where('id', 1)->delete();
        DB::table('entreprises')->where('id', 1)->delete();
        $this->assertNull($prospect->fresh()->linked_user_id);
        $this->assertNull($prospect->fresh()->linked_entreprise_id);
        $prospect->delete();
        $this->assertSame(1, ProspectStatusHistory::count());
        $this->assertTrue(ProspectStatusHistory::first()->prospect->trashed());
    }

    public function test_lifecycle_events_can_arrive_before_matching_and_are_unique_per_source(): void
    {
        $attributes = ['source' => 'stripe', 'event_key' => 'evt_first_payment', 'event_type' => 'subscription.paid', 'payload' => ['invoice_id' => 'in_test'], 'occurred_at' => now()];
        $event = CrmLifecycleEvent::create($attributes);
        $this->assertNull($event->prospect_id);
        $this->assertNull($event->processed_at);
        $this->assertSame('in_test', $event->fresh()->payload['invoice_id']);
        $this->expectException(QueryException::class);
        CrmLifecycleEvent::create($attributes);
    }

    public function test_migrations_can_be_rolled_back_in_dependency_order(): void
    {
        foreach (array_reverse($this->migrations) as $migration) {
            $migration->down();
        }
        foreach (['prospects', 'prospect_status_history', 'prospect_activities', 'crm_lifecycle_events'] as $table) {
            $this->assertFalse(Schema::hasTable($table));
        }
        $this->assertTrue(Schema::hasTable('users'));
    }
}
