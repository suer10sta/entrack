<?php

namespace Tests\Feature;

use App\Enums\ContactEmailStatus;
use App\Enums\ContactEventType;
use App\Enums\ContactSource;
use App\Enums\ContactStatus;
use App\Http\Controllers\admin\SupportController;
use App\Mail\ContactFormMail;
use App\Mail\EnterpriseSupportMail;
use App\Models\Contact;
use App\Models\ContactEvent;
use App\Models\ContactReply;
use App\Models\EmailSendsLog;
use App\Models\Entreprise;
use App\Models\Manager;
use App\Models\SupportTicket;
use App\Models\User;
use App\Services\TicketService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Tests\TestCase;

class ContactFoundationTest extends TestCase
{
    private ?string $schema = null;

    protected function setUp(): void
    {
        parent::setUp();
        if (getenv('CONTACT_TEST_POSTGRES') === '1') {
            config(['database.default' => 'pgsql']);
            $this->guardAgainstNonTestingDatabase();
            $this->schema = 'contact_test_'.bin2hex(random_bytes(8));
            DB::statement('CREATE SCHEMA "'.$this->schema.'"');
            DB::statement('SET search_path TO "'.$this->schema.'"');
        } else {
            config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'database.connections.sqlite.foreign_key_constraints' => true]);
            DB::purge('sqlite');
        }
        foreach (['entreprises', 'managers', 'users'] as $name) {
            Schema::create($name, function ($table) use ($name) {
                $table->id();
                $table->string('name');
                $table->string('email')->nullable();
                if ($name === 'entreprises') {
                    $table->string('invoice_prefix')->default('F');
                    $table->string('invoice_number_format')->default('year_month_number');
                }
                if ($name === 'users') {
                    $table->unsignedBigInteger('entreprise_id')->nullable();
                    $table->timestamp('email_verified_at')->nullable();
                }
                $table->timestamps();
            });
        }
        (require database_path('migrations/2026_02_23_000001_create_support_tickets_table.php'))->up();
        foreach ($this->migrationPaths() as $path) {
            (require $path)->up();
        }
        Mail::fake();
        Notification::fake();
    }

    protected function tearDown(): void
    {
        try {
            if ($this->schema) {
                DB::statement('SET search_path TO public');
                DB::statement('DROP SCHEMA "'.$this->schema.'" CASCADE');
            }
        } finally {
            parent::tearDown();
        }
    }

    private function migrationPaths(): array
    {
        return glob(database_path('migrations/2026_10_29_00000*_*.php'));
    }

    private function ticket(array $overrides = []): Contact
    {
        return app(TicketService::class)->create(...array_replace([
            'name' => 'Visitor', 'email' => 'visitor@example.test', 'subject' => 'Help', 'message' => 'A question',
        ], $overrides));
    }

    public function test_public_creation_without_auth_persists_pending_ticket_and_initial_event_without_sending(): void
    {
        $this->assertGuest();
        $ticket = $this->ticket(['ip' => '2001:db8::1', 'metadata' => ['page' => 'contact']]);
        $this->assertNull($ticket->user_id);
        $this->assertNull($ticket->entreprise_id);
        $this->assertFalse($ticket->is_active_acc);
        $this->assertSame(ContactStatus::Pending, $ticket->status);
        $this->assertSame(ContactSource::Contact, $ticket->source);
        $this->assertSame('2001:db8::1', $ticket->ip);
        $this->assertSame('DT-000001', $ticket->ticket_num);
        $this->assertSame(ContactEventType::Created, $ticket->events()->sole()->event_type);
        $this->assertSame(['page' => 'contact'], $ticket->events()->sole()->metadata);
        Mail::assertNothingSent();
        Mail::assertNothingQueued();
        Notification::assertNothingSent();
    }

    public function test_explicit_null_user_and_ip_are_supported(): void
    {
        $ticket = $this->ticket(['user' => null, 'entreprise' => null, 'ip' => null]);
        $this->assertNull($ticket->user_id);
        $this->assertNull($ticket->ip);
    }

    public function test_explicit_user_and_entreprise_relations_and_historical_snapshot(): void
    {
        $company = Entreprise::create(['name' => 'Company']);
        $user = User::create(['name' => 'Owner', 'email' => 'visitor@example.test', 'entreprise_id' => $company->id]);
        $ticket = $this->ticket(['user' => $user, 'entreprise' => $company]);
        $this->assertTrue($ticket->is_active_acc);
        $this->assertTrue($ticket->user->is($user));
        $this->assertTrue($ticket->entreprise->is($company));
        // Delete at SQL level to isolate FK behavior from unrelated Spatie pivots.
        User::whereKey($user->id)->delete();
        $company->delete();
        $ticket->refresh();
        $this->assertNull($ticket->user_id);
        $this->assertNull($ticket->entreprise_id);
        $this->assertSame('Visitor', $ticket->name);
        $this->assertSame('visitor@example.test', $ticket->email);
        $this->assertTrue($ticket->is_active_acc);
    }

    public function test_email_existence_is_case_insensitive_and_does_not_implicitly_link_any_account(): void
    {
        User::create(['name' => 'Existing', 'email' => ' VISITOR@EXAMPLE.TEST ']);
        $ticket = $this->ticket();
        $this->assertTrue($ticket->is_active_acc);
        $this->assertNull($ticket->user_id);
        $this->assertNull($ticket->entreprise_id);
    }

    public function test_snapshot_does_not_change_when_a_user_registers_later(): void
    {
        $ticket = $this->ticket();
        User::create(['name' => 'Later', 'email' => $ticket->email]);
        $this->assertFalse($ticket->fresh()->is_active_acc);
    }

    public function test_ticket_numbers_are_unique_and_not_reused_after_deleting_all_tickets(): void
    {
        $first = $this->ticket();
        $second = $this->ticket();
        $this->assertSame('DT-000002', $second->ticket_num);
        $first->delete();
        $second->delete();
        $this->assertSame('DT-000003', $this->ticket()->ticket_num);
    }

    public function test_database_rejects_duplicate_ticket_numbers(): void
    {
        $ticket = $this->ticket();
        $this->expectException(QueryException::class);
        DB::table('contacts')->insert(array_merge($ticket->only(['name', 'email', 'subject', 'message', 'ticket_num']), ['created_at' => now(), 'updated_at' => now()]));
    }

    public function test_replies_belong_to_contact_and_platform_manager(): void
    {
        $ticket = $this->ticket();
        $manager = Manager::create(['name' => 'Team', 'email' => 'team@example.test']);
        $reply = $ticket->replies()->create(['manager_id' => $manager->id, 'message' => 'Answer']);
        $this->assertInstanceOf(ContactReply::class, $reply);
        $this->assertTrue($reply->contact->is($ticket));
        $this->assertTrue($reply->manager->is($manager));
        $this->assertCount(1, $ticket->replies);
    }

    public function test_email_logs_support_anonymous_requesters_and_reply_relations(): void
    {
        $ticket = $this->ticket();
        $manager = Manager::create(['name' => 'Team']);
        $reply = $ticket->replies()->create(['manager_id' => $manager->id, 'message' => 'Answer']);
        $log = $ticket->emailLogs()->create(['contact_reply_id' => $reply->id, 'manager_id' => $manager->id,
            'recipient_email' => $ticket->email, 'subject' => 'Reply', 'body' => 'Answer']);
        $this->assertInstanceOf(EmailSendsLog::class, $log);
        $this->assertNull($log->user_id);
        $this->assertSame(ContactEmailStatus::Pending, $log->fresh()->status);
        $this->assertTrue($log->contact->is($ticket));
        $this->assertTrue($log->reply->is($reply));
        $this->assertTrue($log->manager->is($manager));
        $this->assertCount(1, $reply->emailLogs);
    }

    public function test_events_have_json_metadata_and_optional_manager_without_updated_at(): void
    {
        $ticket = $this->ticket();
        $manager = Manager::create(['name' => 'Team']);
        $event = $ticket->events()->create(['manager_id' => $manager->id, 'event_type' => ContactEventType::Closed, 'metadata' => ['reason' => 'resolved']]);
        $this->assertTrue($event->contact->is($ticket));
        $this->assertTrue($event->manager->is($manager));
        $this->assertSame(['reason' => 'resolved'], $event->fresh()->metadata);
        $this->assertNotNull($event->created_at);
        $this->assertFalse(Schema::hasColumn('contact_events', 'updated_at'));
    }

    public function test_contact_deletion_cascades_to_replies_logs_and_events(): void
    {
        $ticket = $this->ticket();
        $manager = Manager::create(['name' => 'Team']);
        $reply = $ticket->replies()->create(['manager_id' => $manager->id, 'message' => 'Answer']);
        $ticket->emailLogs()->create(['contact_reply_id' => $reply->id, 'recipient_email' => $ticket->email, 'subject' => 'Reply', 'body' => 'Answer']);
        $ticket->delete();
        $this->assertSame(0, ContactReply::count());
        $this->assertSame(0, EmailSendsLog::count());
        $this->assertSame(0, ContactEvent::count());
    }

    public function test_deleting_a_reply_keeps_its_email_history(): void
    {
        $ticket = $this->ticket();
        $manager = Manager::create(['name' => 'Team']);
        $reply = $ticket->replies()->create(['manager_id' => $manager->id, 'message' => 'Answer']);
        $log = $ticket->emailLogs()->create(['contact_reply_id' => $reply->id, 'recipient_email' => $ticket->email, 'subject' => 'Reply', 'body' => 'Answer']);
        $reply->delete();
        $this->assertNull($log->fresh()->contact_reply_id);
    }

    public function test_real_ip_validation_rejects_source_labels_as_ips(): void
    {
        $this->expectException(ValidationException::class);
        $this->ticket(['ip' => 'd2-survey']);
    }

    public function test_inconsistent_explicit_tenant_links_are_rejected(): void
    {
        $company = Entreprise::create(['name' => 'Other company']);
        $user = User::create(['name' => 'Owner', 'email' => 'visitor@example.test']);
        $this->expectException(ValidationException::class);
        $this->ticket(['user' => $user, 'entreprise' => $company]);
    }

    public function test_failure_creating_initial_event_rolls_back_the_ticket_and_allocator(): void
    {
        ContactEvent::creating(fn () => throw new RuntimeException('Simulated event failure'));
        try {
            $this->ticket();
            $this->fail('Expected failure');
        } catch (RuntimeException $error) {
            $this->assertSame('Simulated event failure', $error->getMessage());
            $this->assertSame(0, Contact::count());
            $this->assertSame(1, (int) DB::table('contact_ticket_sequences')->value('next_number'));
        } finally {
            ContactEvent::flushEventListeners();
        }
        $this->assertSame('DT-000001', $this->ticket()->ticket_num);
    }

    public function test_legacy_support_stays_independent_and_its_routes_remain_protected(): void
    {
        $company = Entreprise::create(['name' => 'Legacy']);
        $user = User::create(['name' => 'Owner', 'email' => 'owner@example.test', 'entreprise_id' => $company->id]);
        Auth::login($user);
        $request = Request::create('/admin/support', 'POST', ['subject' => 'Old support', 'message' => 'Original flow', 'priority' => 'high']);
        app(SupportController::class)->store($request);
        $legacy = SupportTicket::sole();
        $this->assertSame('open', $legacy->status);
        $this->assertSame($user->id, $legacy->user_id);
        $this->assertSame(0, Contact::count());
        Mail::assertSent(EnterpriseSupportMail::class, 1);
        foreach (['create' => 'create', 'store' => 'store', 'index' => 'index', 'destroy' => 'destroy'] as $route => $method) {
            $definition = Route::getRoutes()->getByName('admin.support.'.$route);
            $this->assertNotNull($definition);
            $this->assertStringContainsString(SupportController::class.'@'.$method, $definition->getActionName());
            $this->assertContains('auth', $definition->gatherMiddleware());
            $this->assertContains('verified', $definition->gatherMiddleware());
            $this->assertContains('role:Admin', $definition->gatherMiddleware());
        }
        $this->ticket();
        $legacy->delete();
        $this->assertSame(1, Contact::count());
    }

    public function test_public_contact_uses_new_ticket_and_keeps_existing_email_independent_from_legacy_support(): void
    {
        $response = $this->from('/contact')->post('/contact', ['name' => 'Visitor', 'email' => 'visitor@example.test', 'subject' => 'Original contact', 'message' => 'Hello']);
        $response->assertRedirect('/contact')->assertSessionHas('success');
        Mail::assertSent(ContactFormMail::class, fn ($mail) => $mail->hasTo('support@dalytrac.com'));
        $this->assertSame(1, Contact::count());
        $this->assertSame(0, SupportTicket::count());
    }

    public function test_migrations_can_be_rolled_back_without_affecting_legacy_support(): void
    {
        foreach (array_reverse($this->migrationPaths()) as $path) {
            (require $path)->down();
        }
        $this->assertFalse(Schema::hasTable('contacts'));
        $this->assertFalse(Schema::hasTable('contact_ticket_sequences'));
        $this->assertFalse(Schema::hasTable('contact_replies'));
        $this->assertFalse(Schema::hasTable('email_sends_log'));
        $this->assertFalse(Schema::hasTable('contact_events'));
        $this->assertTrue(Schema::hasTable('support_tickets'));
    }

    public function test_postgres_concurrent_processes_allocate_distinct_numbers(): void
    {
        if (! $this->schema) {
            $this->markTestSkipped('Process concurrency is exercised with CONTACT_TEST_POSTGRES=1.');
        }
        $start = (string) (microtime(true) + 1);
        $processes = [];
        try {
            for ($i = 0; $i < 2; $i++) {
                $process = proc_open([PHP_BINARY, base_path('tests/Support/contact-number-worker.php'), $this->schema,
                    config('database.connections.pgsql.database'), $start],
                    [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, base_path());
                $this->assertIsResource($process);
                fclose($pipes[0]);
                $processes[] = [$process, $pipes];
            }
            $numbers = [];
            foreach ($processes as [$process, $pipes]) {
                $stdout = stream_get_contents($pipes[1]);
                $stderr = stream_get_contents($pipes[2]);
                fclose($pipes[1]);
                fclose($pipes[2]);
                $this->assertSame(0, proc_close($process), $stderr);
                $numbers = array_merge($numbers, json_decode($stdout, true, 512, JSON_THROW_ON_ERROR));
            }
            $processes = [];
            $this->assertCount(20, array_unique($numbers));
            $this->assertSame(20, Contact::count());
            $this->assertSame(20, ContactEvent::count());
            $this->assertSame('DT-000021', $this->ticket()->ticket_num);
        } finally {
            foreach ($processes as [$process, $pipes]) {
                if (is_resource($process)) {
                    proc_terminate($process);
                    foreach ($pipes as $pipe) {
                        if (is_resource($pipe)) {
                            fclose($pipe);
                        }
                    }
                    proc_close($process);
                }
            }
        }
    }
}
