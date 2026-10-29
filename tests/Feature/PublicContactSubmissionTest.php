<?php

namespace Tests\Feature;

use App\Enums\ContactEmailStatus;
use App\Enums\ContactEventType;
use App\Enums\ContactSource;
use App\Enums\ContactStatus;
use App\Mail\ContactFormMail;
use App\Models\Contact;
use App\Models\ContactEvent;
use App\Models\EmailSendsLog;
use App\Models\Entreprise;
use App\Models\Manager;
use App\Models\SupportTicket;
use App\Models\User;
use App\Services\TicketService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Tests\TestCase;

class PublicContactSubmissionTest extends TestCase
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

    private function payload(array $changes = []): array
    {
        return array_replace(['name' => 'Visitor', 'email' => 'visitor@example.test', 'subject' => 'Help', 'message' => "Hello\nDetails"], $changes);
    }

    public function test_anonymous_submission_persists_ticket_event_and_sent_log_with_number(): void
    {
        config(['support.email' => 'help@example.test', 'mail.from.address' => 'sender@example.test', 'mail.from.name' => 'DalyTrac']);
        $response = $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.42'])->from('/contact')->post('/contact', $this->payload());
        $response->assertRedirect('/contact')->assertSessionHas('success');
        $ticket = Contact::sole();
        $this->assertSame(ContactSource::Contact, $ticket->source);
        $this->assertSame(ContactStatus::Pending, $ticket->status);
        $this->assertSame('203.0.113.42', $ticket->ip);
        $this->assertSame('DT-000001', $ticket->ticket_num);
        $this->assertNull($ticket->user_id);
        $this->assertNull($ticket->entreprise_id);
        $this->assertFalse($ticket->is_active_acc);
        $this->assertSame(ContactEventType::Created, $ticket->events()->sole()->event_type);
        $this->assertStringContainsString($ticket->ticket_num, session('success'));
        $log = $ticket->emailLogs()->sole();
        $this->assertSame(ContactEmailStatus::Sent, $log->status);
        $this->assertSame('help@example.test', $log->recipient_email);
        $this->assertNull($log->contact_reply_id);
        $this->assertNotNull($log->attempted_at);
        $this->assertNotNull($log->sent_at);
        $this->assertNull($log->failed_at);
        $this->assertStringContainsString($ticket->ticket_num, $log->subject);
        $this->assertStringContainsString($ticket->ticket_num, $log->body);
        Mail::assertSent(ContactFormMail::class, function ($mail) use ($ticket) {
            $this->assertSame('sender@example.test', $mail->envelope()->from->address);
            $this->assertSame($ticket->email, $mail->envelope()->replyTo[0]->address);
            $this->assertStringContainsString($ticket->ticket_num, $mail->render());

            return $mail->hasTo('help@example.test');
        });
        $this->assertSame(0, SupportTicket::count());
    }

    public function test_email_match_sets_snapshot_but_never_links_an_anonymous_visitor(): void
    {
        User::create(['name' => 'Existing', 'email' => 'VISITOR@EXAMPLE.TEST']);
        $this->post('/contact', $this->payload(['email' => ' visitor@example.test ']))->assertSessionHas('success');
        $ticket = Contact::sole();
        $this->assertTrue($ticket->is_active_acc);
        $this->assertNull($ticket->user_id);
        $this->assertNull($ticket->entreprise_id);
    }

    public function test_authenticated_web_user_is_linked_even_when_reply_email_is_different(): void
    {
        $this->withoutMiddleware(\App\Http\Middleware\CheckSubscription::class);
        $company = Entreprise::create(['name' => 'Company']);
        $user = User::create(['name' => 'Owner', 'email' => 'owner@example.test', 'entreprise_id' => $company->id]);
        $this->actingAs($user, 'web')->post('/contact', $this->payload())->assertSessionHas('success');
        $ticket = Contact::sole();
        $this->assertSame($user->id, $ticket->user_id);
        $this->assertSame($company->id, $ticket->entreprise_id);
        $this->assertSame('visitor@example.test', $ticket->email);
        $this->assertFalse($ticket->is_active_acc);
    }

    public function test_dangling_entreprise_is_not_linked(): void
    {
        $this->withoutMiddleware(\App\Http\Middleware\CheckSubscription::class);
        $user = User::create(['name' => 'Owner', 'email' => 'visitor@example.test', 'entreprise_id' => 999]);
        $this->actingAs($user, 'web')->post('/contact', $this->payload())->assertSessionHas('success');
        $this->assertSame($user->id, Contact::sole()->user_id);
        $this->assertNull(Contact::sole()->entreprise_id);
    }

    public function test_manager_session_is_not_a_web_user_link(): void
    {
        $manager = Manager::create(['name' => 'Manager']);
        $this->actingAs($manager, 'manager')->post('/contact', $this->payload())->assertSessionHas('success');
        $this->assertNull(Contact::sole()->user_id);
        $this->assertNull(Contact::sole()->entreprise_id);
    }

    private function transportFailure(\Throwable $error): void
    {
        $pending = \Mockery::mock(\Illuminate\Mail\PendingMail::class);
        Mail::shouldReceive('to')->once()->with(config('support.email'))->andReturn($pending);
        $pending->shouldReceive('send')->once()->andReturnUsing(function () use ($error) {
            $this->assertSame(0, DB::transactionLevel());
            $this->assertSame(1, Contact::count());
            $this->assertSame(1, ContactEvent::count());
            $this->assertSame(ContactEmailStatus::Pending, EmailSendsLog::sole()->status);
            throw $error;
        });
    }

    public function test_explicit_smtp_rejection_keeps_ticket_and_records_failed_without_secrets(): void
    {
        $this->transportFailure(new \Symfony\Component\Mailer\Exception\TransportException('Expected response code 250 but got code "550", password=never-store-this'));
        $this->from('/contact')->post('/contact', $this->payload())->assertSessionHas('success')->assertSessionMissing('error');
        $this->assertSame(1, Contact::count());
        $this->assertSame(1, ContactEvent::count());
        $log = EmailSendsLog::sole();
        $this->assertSame(ContactEmailStatus::Failed, $log->status);
        $this->assertNotNull($log->failed_at);
        $this->assertNull($log->sent_at);
        $this->assertStringNotContainsString('never-store-this', $log->error_message);
        $this->assertStringContainsString(Contact::sole()->ticket_num, session('success'));
    }

    public function test_ambiguous_transport_timeout_is_uncertain_without_automatic_retry(): void
    {
        $this->transportFailure(new \Symfony\Component\Mailer\Exception\TransportException('Timeout awaiting SMTP acknowledgement'));
        $this->post('/contact', $this->payload())->assertSessionHas('success');
        $this->assertSame(ContactEmailStatus::Uncertain, EmailSendsLog::sole()->status);
        $this->assertNull(EmailSendsLog::sole()->failed_at);
        $this->assertSame(1, Contact::count());
    }

    public function test_ticket_failure_never_sends_email_or_claims_success(): void
    {
        $mock = \Mockery::mock(TicketService::class);
        $mock->shouldReceive('create')->once()->andThrow(new RuntimeException('DB unavailable'));
        $this->app->instance(TicketService::class, $mock);
        $this->from('/contact')->post('/contact', $this->payload())->assertSessionHas('error')->assertSessionMissing('success');
        $this->assertSame(0, Contact::count());
        Mail::assertNothingSent();
    }

    public function test_invalid_notification_config_keeps_ticket_and_records_failure(): void
    {
        config(['support.email' => 'invalid']);
        $this->post('/contact', $this->payload())->assertSessionHas('success');
        $this->assertSame(1, Contact::count());
        $this->assertSame(ContactEmailStatus::Failed, EmailSendsLog::sole()->status);
        Mail::assertNothingSent();
    }

    public function test_honeypot_creates_nothing_and_sends_nothing(): void
    {
        $this->post('/contact', $this->payload(['website' => 'bot.example']))->assertRedirect(route('welcome'));
        $this->assertSame(0, Contact::count());
        $this->assertSame(0, EmailSendsLog::count());
        Mail::assertNothingSent();
    }

    public function test_validation_rejects_invalid_email_oversize_message_and_header_newlines(): void
    {
        foreach ([['email' => 'invalid'], ['message' => str_repeat('x', 50001)], ['subject' => "Header\nInjected"], ['name' => str_repeat('x', 256)]] as $changes) {
            $this->post('/contact', $this->payload($changes))->assertSessionHasErrors(array_key_first($changes));
        }
        $this->assertSame(0, Contact::count());
        Mail::assertNothingSent();
    }

    public function test_rate_limit_is_five_submissions_per_minute_and_isolated_by_ip(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.10'])->post('/contact', $this->payload())->assertStatus(302);
        }
        $this->post('/contact', $this->payload())->assertStatus(429)->assertHeader('Retry-After');
        $this->assertSame(5, Contact::count());
        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.11'])->post('/contact', $this->payload())->assertStatus(302);
        $this->assertSame(6, Contact::count());
        $this->travel(61)->seconds();
        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.10'])->post('/contact', $this->payload())->assertStatus(302);
    }

    public function test_confirmation_is_translated_in_all_four_supported_languages(): void
    {
        foreach (['fr', 'en', 'nl', 'es'] as $locale) {
            $this->withSession(['locale' => $locale])->post('/contact', $this->payload())->assertSessionHas('success');
            $ticket = Contact::latest('id')->first();
            $this->assertSame(trans('messages.contact_ticket_received', ['ticket' => $ticket->ticket_num], $locale), session('success'));
            $this->assertStringContainsString($ticket->ticket_num, session('success'));
        }
    }

    public function test_real_array_transport_acceptance_is_logged_as_sent(): void
    {
        // Real Laravel/Symfony MIME pipeline; array transport cannot send externally.
        $manager = Mail::getFacadeRoot()->manager;
        Mail::swap($manager);
        config(['mail.default' => 'array']);
        $this->post('/contact', $this->payload(['subject' => str_repeat('é', 255)]))->assertSessionHas('success');
        $log = EmailSendsLog::sole();
        $this->assertSame(ContactEmailStatus::Sent, $log->status);
        $this->assertSame(255, mb_strlen($log->subject));
        $this->assertStringContainsString(str_repeat('é', 255), $log->body);
        $this->assertSame(str_repeat('é', 255), Contact::sole()->subject);
    }

    public function test_real_mailer_cancellation_is_a_certain_failure(): void
    {
        Mail::swap(Mail::getFacadeRoot()->manager);
        config(['mail.default' => 'array']);
        \Illuminate\Support\Facades\Event::listen(\Illuminate\Mail\Events\MessageSending::class, fn () => false);
        $this->post('/contact', $this->payload())->assertSessionHas('success');
        $this->assertSame(ContactEmailStatus::Failed, EmailSendsLog::sole()->status);
        $this->assertNotNull(EmailSendsLog::sole()->failed_at);
        $this->assertSame(1, Contact::count());
    }

    public function test_connection_refused_is_failed_rather_than_uncertain(): void
    {
        $this->transportFailure(new \Symfony\Component\Mailer\Exception\TransportException('Connection could not be established: connection refused'));
        $this->post('/contact', $this->payload())->assertSessionHas('success');
        $this->assertSame(ContactEmailStatus::Failed, EmailSendsLog::sole()->status);
        $this->assertNotNull(EmailSendsLog::sole()->failed_at);
    }

    public function test_untrusted_forwarded_header_cannot_override_visitor_ip(): void
    {
        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.45', 'HTTP_X_FORWARDED_FOR' => '198.51.100.7'])
            ->post('/contact', $this->payload())->assertSessionHas('success');
        $this->assertSame('203.0.113.45', Contact::sole()->ip);
    }
}
