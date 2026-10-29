<?php

namespace Tests\Feature;

use App\Enums\ContactStatus;
use App\Models\Contact;
use App\Models\Manager;
use App\Services\TicketService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ManagerContactCardsTest extends TestCase
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

    public function test_cards_show_status_requester_bounded_preview_and_existing_detail_link(): void
    {
        $this->managerLogin();
        $ticket = $this->ticket(['name' => 'Card requester', 'subject' => 'Card subject', 'message' => 'Preview content '.str_repeat('long message ', 1000)]);
        $this->get(route('manager.support.index'))->assertOk()->assertSee('cs-ticket-grid', false)->assertSee('cs-ticket-card', false)
            ->assertSee('Card requester')->assertSee('Card subject')->assertSee('Preview content')
            ->assertSee(route('manager.support.show', $ticket), false)->assertDontSee('<table class="cs-table">', false)
            ->assertViewHas('contacts', function ($contacts) {
                $this->assertFalse(array_key_exists('message', $contacts->first()->getAttributes()));

                return mb_strlen($contacts->first()->message_preview) === 601;
            });
    }

    public function test_only_pending_and_replied_cards_have_dialogs_posting_to_existing_reply_endpoint(): void
    {
        $this->managerLogin();
        $pending = $this->ticket();
        $replied = $this->ticket();
        $replied->update(['status' => ContactStatus::Replied]);
        $closed = $this->ticket();
        $closed->update(['status' => ContactStatus::Closed]);
        $html = $this->get(route('manager.support.index'))->assertOk()->getContent();
        foreach ([$pending, $replied] as $ticket) {
            $this->assertStringContainsString('id="reply-dialog-'.$ticket->id.'"', $html);
            $this->assertStringContainsString('action="'.route('manager.support.reply', $ticket).'"', $html);
        }
        $this->assertStringNotContainsString('id="reply-dialog-'.$closed->id.'"', $html);
        $this->assertStringContainsString(route('manager.support.show', $closed), $html);
        preg_match_all('/name="submission_key" value="([^"]+)"/', $html, $matches);
        $this->assertCount(2, $matches[1]);
        $this->assertCount(2, array_unique($matches[1]));
        foreach ($matches[1] as $key) {
            $this->assertTrue(\Illuminate\Support\Str::isUuid($key));
        }
    }

    public function test_card_and_modal_context_is_html_escaped(): void
    {
        $this->managerLogin();
        $ticket = $this->ticket(['name' => '<img src=x onerror=alert(1)>', 'subject' => '<script>unsafe</script>', 'message' => '<svg onload=alert(1)>']);
        $this->get(route('manager.support.index'))->assertOk()->assertDontSee('<script>unsafe</script>', false)
            ->assertDontSee('<img src=x onerror=alert(1)>', false)->assertDontSee('<svg onload=alert(1)>', false)
            ->assertSee('&lt;script&gt;unsafe&lt;/script&gt;', false);
    }

    public function test_quick_form_submission_keeps_existing_redirect_and_idempotency(): void
    {
        $this->managerLogin();
        $ticket = $this->ticket();
        $html = $this->get(route('manager.support.index'))->getContent();
        preg_match('/name="submission_key" value="([^"]+)"/', $html, $matches);
        $payload = ['message' => 'Quick form answer', 'submission_key' => $matches[1]];
        for ($i = 0; $i < 2; $i++) {
            $this->post(route('manager.support.reply', $ticket), $payload)->assertRedirect(route('manager.support.show', $ticket))->assertSessionHas('success');
        }
        $this->assertSame(1, \App\Models\ContactReply::count());
        $this->assertSame(1, \App\Models\EmailSendsLog::count());
        $this->assertSame(ContactStatus::Replied, $ticket->fresh()->status);
        $this->get(route('manager.support.index'))->assertViewHas('counts', fn ($counts) => $counts['all'] === 1 && $counts['replied'] === 1);
    }

    public function test_quick_reply_strings_are_available_in_all_dashboard_languages(): void
    {
        $this->managerLogin();
        $this->ticket();
        foreach (['fr', 'en', 'nl', 'es'] as $locale) {
            $this->withSession(['locale' => $locale])->get(route('manager.support.index'))->assertOk()
                ->assertSee(trans('support.quick_reply', [], $locale))->assertSee(trans('support.cancel', [], $locale))
                ->assertSee(trans('support.your_reply', [], $locale));
        }
    }
}
