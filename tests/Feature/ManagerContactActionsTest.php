<?php

namespace Tests\Feature;

use App\Enums\ContactEmailStatus;
use App\Enums\ContactEventType;
use App\Enums\ContactStatus;
use App\Mail\ContactReplyMail;
use App\Models\Contact;
use App\Models\ContactEvent;
use App\Models\ContactReply;
use App\Models\EmailSendsLog;
use App\Models\Manager;
use App\Models\User;
use App\Services\ContactManagementService;
use App\Services\TicketService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Symfony\Component\Mailer\Exception\TransportException;
use Tests\TestCase;

class ManagerContactActionsTest extends TestCase
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
                $table->string('password')->nullable();
                $table->string('remember_token')->nullable();
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
        $this->withoutVite();
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

    private function managerLogin(): Manager
    {
        $manager = Manager::create(['name' => 'Support team', 'email' => 'team@example.test']);
        $this->actingAs($manager, 'manager');

        return $manager;
    }

    private function replyTo(Contact $ticket, array $changes = [])
    {
        return $this->from(route('manager.support.show', $ticket))->post(route('manager.support.reply', $ticket), array_replace([
            'message' => "Support answer\nNext step", 'submission_key' => (string) Str::uuid(),
        ], $changes));
    }

    public function test_manager_reply_persists_author_status_event_log_and_sends_to_historical_email(): void
    {
        $manager = $this->managerLogin();
        $ticket = $this->ticket(['email' => 'historical@example.test']);
        $this->replyTo($ticket, ['manager_id' => 999, 'status' => 'closed', 'contact_id' => 999])->assertRedirect(route('manager.support.show', $ticket))->assertSessionHas('success');
        $reply = ContactReply::sole();
        $this->assertSame($manager->id, $reply->manager_id);
        $this->assertSame($ticket->id, $reply->contact_id);
        $this->assertSame(ContactStatus::Replied, $ticket->fresh()->status);
        $event = $ticket->events()->where('event_type', ContactEventType::Replied->value)->sole();
        $this->assertSame($manager->id, $event->manager_id);
        $this->assertSame($reply->id, $event->metadata['contact_reply_id']);
        $log = EmailSendsLog::sole();
        $this->assertSame($ticket->id, $log->contact_id);
        $this->assertSame($reply->id, $log->contact_reply_id);
        $this->assertSame($manager->id, $log->manager_id);
        $this->assertSame($ticket->email, $log->recipient_email);
        $this->assertSame(ContactEmailStatus::Sent, $log->status);
        $this->assertNotNull($log->sent_at);
        $this->assertNotNull($log->attempted_at);
        Mail::assertSent(ContactReplyMail::class, function ($mail) use ($ticket) {
            $this->assertStringContainsString($ticket->ticket_num, $mail->notificationSubject());
            $this->assertStringContainsString($ticket->ticket_num, $mail->render());
            $this->assertStringContainsString($ticket->subject, $mail->render());

            return $mail->hasTo($ticket->email);
        });
    }

    public function test_replied_ticket_can_receive_another_intentional_reply(): void
    {
        $this->managerLogin();
        $ticket = $this->ticket();
        $this->replyTo($ticket)->assertSessionHas('success');
        $this->replyTo($ticket, ['message' => 'Another intentional answer'])->assertSessionHas('success');
        $this->assertSame(2, ContactReply::count());
        $this->assertSame(2, EmailSendsLog::count());
    }

    public function test_same_submission_key_never_duplicates_reply_event_or_email(): void
    {
        $this->managerLogin();
        $ticket = $this->ticket();
        $key = (string) Str::uuid();
        $this->replyTo($ticket, ['submission_key' => $key])->assertSessionHas('success');
        $this->replyTo($ticket, ['submission_key' => $key])->assertSessionHas('success');
        $this->assertSame(1, ContactReply::count());
        $this->assertSame(1, EmailSendsLog::count());
        $this->assertSame(2, ContactEvent::count());
        Mail::assertSent(ContactReplyMail::class, 1);
        $this->replyTo($ticket, ['submission_key' => $key, 'message' => 'Changed answer'])->assertStatus(409);
    }

    private function failTransport(TransportException $error): void
    {
        $pending = \Mockery::mock(\Illuminate\Mail\PendingMail::class);
        Mail::shouldReceive('to')->once()->andReturn($pending);
        $pending->shouldReceive('send')->once()->andReturnUsing(function () use ($error) {
            $this->assertSame(0, DB::transactionLevel());
            $this->assertSame(1, ContactReply::count());
            $this->assertSame(ContactStatus::Replied, Contact::sole()->status);
            $this->assertSame(ContactEmailStatus::Pending, EmailSendsLog::sole()->status);
            throw $error;
        });
    }

    public function test_smtp_rejection_preserves_reply_event_and_status_and_does_not_retry_replay(): void
    {
        $this->managerLogin();
        $ticket = $this->ticket();
        $key = (string) Str::uuid();
        $this->failTransport(new TransportException('Expected response code 250 but got code "550", smtp-password=secret-value'));
        $this->replyTo($ticket, ['submission_key' => $key])->assertSessionHas('warning');
        $this->replyTo($ticket, ['submission_key' => $key])->assertSessionHas('warning');
        $this->assertSame(1, ContactReply::count());
        $this->assertSame(2, ContactEvent::count());
        $this->assertSame(ContactStatus::Replied, $ticket->fresh()->status);
        $log = EmailSendsLog::sole();
        $this->assertSame(ContactEmailStatus::Failed, $log->status);
        $this->assertNotNull($log->failed_at);
        $this->assertNull($log->sent_at);
        $this->assertStringNotContainsString('secret-value', $log->error_message);
    }

    public function test_ambiguous_timeout_is_uncertain_and_preserves_reply(): void
    {
        $this->managerLogin();
        $ticket = $this->ticket();
        $this->failTransport(new TransportException('Timeout awaiting acknowledgement'));
        $this->replyTo($ticket)->assertSessionHas('warning');
        $this->assertSame(ContactEmailStatus::Uncertain, EmailSendsLog::sole()->status);
        $this->assertNull(EmailSendsLog::sole()->failed_at);
        $this->assertSame(1, ContactReply::count());
    }

    public function test_closed_ticket_must_be_reopened_before_replying(): void
    {
        $this->managerLogin();
        $ticket = $this->ticket();
        $ticket->update(['status' => ContactStatus::Closed]);
        $this->replyTo($ticket)->assertSessionHasErrors('message');
        $this->assertSame(0, ContactReply::count());
        $this->assertSame(0, EmailSendsLog::count());
        $this->get(route('manager.support.show', $ticket))->assertOk()->assertDontSee('id="reply-message"', false)->assertSee(__('support.reopen'));
        $this->post(route('manager.support.reopen', $ticket))->assertSessionHas('success');
        $this->replyTo($ticket)->assertSessionHas('success');
    }

    public function test_close_and_reopen_are_idempotent_and_record_the_manager(): void
    {
        $manager = $this->managerLogin();
        $ticket = $this->ticket();
        $this->post(route('manager.support.close', $ticket))->assertSessionHasErrors('confirmed');
        for ($i = 0; $i < 2; $i++) {
            $this->post(route('manager.support.close', $ticket), ['confirmed' => 1])->assertSessionHas('success');
        }
        $this->assertSame(ContactStatus::Closed, $ticket->fresh()->status);
        $closed = $ticket->events()->where('event_type', ContactEventType::Closed->value)->sole();
        $this->assertSame($manager->id, $closed->manager_id);
        for ($i = 0; $i < 2; $i++) {
            $this->post(route('manager.support.reopen', $ticket))->assertSessionHas('success');
        }
        $this->assertSame(ContactStatus::Pending, $ticket->fresh()->status);
        $this->assertSame($manager->id, $ticket->events()->where('event_type', ContactEventType::Reopened->value)->sole()->manager_id);
        $this->assertSame(3, ContactEvent::count());
    }

    public function test_permanent_delete_requires_exact_reference_cascades_and_never_reuses_number(): void
    {
        $this->managerLogin();
        $ticket = $this->ticket();
        $this->replyTo($ticket)->assertSessionHas('success');
        $this->delete(route('manager.support.destroy', $ticket), ['confirmation' => 'WRONG'])->assertSessionHasErrors('confirmation');
        $this->assertSame(1, Contact::count());
        $this->delete(route('manager.support.destroy', $ticket), ['confirmation' => $ticket->ticket_num])->assertRedirect(route('manager.support.index'))->assertSessionHas('success');
        $this->assertSame(0, Contact::count());
        $this->assertSame(0, ContactReply::count());
        $this->assertSame(0, ContactEvent::count());
        $this->assertSame(0, EmailSendsLog::count());
        $this->assertSame('DT-000002', $this->ticket()->ticket_num);
    }

    public function test_non_managers_cannot_call_any_management_endpoint(): void
    {
        $ticket = $this->ticket();
        foreach (['guest', 'web'] as $identity) {
            if ($identity === 'web') {
                $this->actingAs(User::create(['name' => 'Client', 'email' => 'web@example.test']), 'web');
            }
            foreach (['reply', 'close', 'reopen'] as $action) {
                $this->postJson(route('manager.support.'.$action, $ticket))->assertUnauthorized();
            }
            $this->deleteJson(route('manager.support.destroy', $ticket))->assertUnauthorized();
        }
        $this->assertSame(0, ContactReply::count());
        $this->assertSame(1, Contact::count());
    }

    public function test_validation_rejects_empty_long_reply_and_invalid_token_without_changes(): void
    {
        $this->managerLogin();
        $ticket = $this->ticket();
        foreach ([['message' => ''], ['message' => str_repeat('x', 50001)], ['submission_key' => 'bad']] as $changes) {
            $this->replyTo($ticket, $changes)->assertSessionHasErrors();
        }
        $this->assertSame(0, ContactReply::count());
        $this->assertSame(ContactStatus::Pending, $ticket->fresh()->status);
    }

    public function test_database_rejects_a_log_referencing_another_contacts_reply(): void
    {
        $manager = $this->managerLogin();
        $one = $this->ticket();
        $two = $this->ticket();
        $reply = $one->replies()->create(['manager_id' => $manager->id, 'message' => 'Existing reply']);
        $this->expectException(QueryException::class);
        DB::table('email_sends_log')->insert(['contact_id' => $two->id, 'contact_reply_id' => $reply->id, 'recipient_email' => 'recipient@example.test', 'subject' => 'Mismatch', 'body' => 'Mismatch']);
    }

    public function test_reply_parent_cannot_be_changed_to_break_an_existing_email_log(): void
    {
        $this->managerLogin();
        $one = $this->ticket();
        $two = $this->ticket();
        $this->replyTo($one)->assertSessionHas('success');
        $this->expectException(QueryException::class);
        DB::table('contact_replies')->where('id', ContactReply::sole()->id)->update(['contact_id' => $two->id]);
    }

    public function test_deleting_a_reply_still_keeps_its_email_history(): void
    {
        $this->managerLogin();
        $ticket = $this->ticket();
        $this->replyTo($ticket)->assertSessionHas('success');
        ContactReply::sole()->delete();
        $this->assertNull(EmailSendsLog::sole()->contact_reply_id);
    }

    public function test_log_insert_failure_rolls_back_reply_and_event_before_any_email(): void
    {
        $manager = $this->managerLogin();
        $ticket = $this->ticket();
        EmailSendsLog::creating(fn () => throw new \RuntimeException('Simulated DB failure'));
        try {
            app(ContactManagementService::class)->reply($ticket, $manager, 'Answer', (string) Str::uuid());
            $this->fail('Expected rollback');
        } catch (\RuntimeException $error) {
            $this->assertSame(0, ContactReply::count());
            $this->assertSame(1, ContactEvent::count());
            $this->assertSame(ContactStatus::Pending, $ticket->fresh()->status);
        } finally {
            EmailSendsLog::flushEventListeners();
        }
        Mail::assertNothingSent();
    }

    public function test_real_array_transport_renders_escaped_dalytrac_reply(): void
    {
        $this->managerLogin();
        Mail::swap(Mail::getFacadeRoot()->manager);
        config(['mail.default' => 'array']);
        $ticket = $this->ticket();
        $this->replyTo($ticket, ['message' => "Hello\n<script>alert(1)</script>"])->assertSessionHas('success');
        $this->assertSame(ContactEmailStatus::Sent, EmailSendsLog::sole()->status);
        $this->assertStringNotContainsString('<script>alert(1)</script>', EmailSendsLog::sole()->body);
        $this->assertStringContainsString('&lt;script&gt;', EmailSendsLog::sole()->body);
    }

    public function test_forms_confirmations_and_feedback_are_translated_in_four_languages(): void
    {
        $this->managerLogin();
        $ticket = $this->ticket();
        foreach (['fr', 'en', 'nl', 'es'] as $locale) {
            $this->withSession(['locale' => $locale])->get(route('manager.support.show', $ticket))->assertOk()->assertSee(trans('support.send_reply', [], $locale))->assertSee(trans('support.delete', [], $locale));
            $this->replyTo($ticket, ['message' => ''])->assertSessionHasErrors('message');
            $this->assertStringContainsString(trans('support.reply_required', [], $locale), session('errors')->first('message'));
        }
    }

    public function test_postgres_parallel_reply_submissions_produce_one_reply_and_log(): void
    {
        if (! $this->schema) {
            $this->markTestSkipped('PostgreSQL process concurrency run only.');
        }
        $manager = $this->managerLogin();
        $ticket = $this->ticket();
        $key = (string) Str::uuid();
        $start = (string) (microtime(true) + 1);
        $processes = [];
        try {
            for ($i = 0; $i < 2; $i++) {
                $process = proc_open([PHP_BINARY, base_path('tests/Support/contact-reply-worker.php'), $this->schema,
                    config('database.connections.pgsql.database'), (string) $ticket->id, (string) $manager->id, $key, $start],
                    [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, base_path());
                $this->assertIsResource($process);
                fclose($pipes[0]);
                $processes[] = [$process, $pipes];
            }
            $ids = [];
            foreach ($processes as [$process, $pipes]) {
                $out = stream_get_contents($pipes[1]);
                $err = stream_get_contents($pipes[2]);
                fclose($pipes[1]);
                fclose($pipes[2]);
                $this->assertSame(0, proc_close($process), $err);
                $ids[] = json_decode($out, true, 512, JSON_THROW_ON_ERROR)['log_id'];
            }
            $processes = [];
            $this->assertSame($ids[0], $ids[1]);
            $this->assertSame(1, ContactReply::count());
            $this->assertSame(1, EmailSendsLog::count());
            $this->assertSame(2, ContactEvent::count());
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
