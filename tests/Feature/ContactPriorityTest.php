<?php

namespace Tests\Feature;

use App\Enums\ContactPriority;
use App\Enums\ContactSource;
use App\Enums\ContactStatus;
use App\Models\Contact;
use App\Models\Manager;
use App\Services\TicketService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class ContactPriorityTest extends TestCase
{
    private ?string $schema = null;

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'pgsql', 'mail.default' => 'array']);
        $this->guardAgainstNonTestingDatabase();
        $this->schema = 'contact_test_'.bin2hex(random_bytes(8));
        DB::statement('CREATE SCHEMA "'.$this->schema.'"');
        DB::statement('SET search_path TO "'.$this->schema.'"');
        foreach (['entreprises', 'users', 'managers'] as $name) {
            Schema::create($name, function ($table) {
                $table->id();
                $table->string('name');
                $table->string('email')->nullable();
                $table->timestamps();
            });
        }
        foreach (glob(database_path('migrations/2026_10_29_00000*_*.php')) as $path) {
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

    private function ticket(array $attributes = []): Contact
    {
        return app(TicketService::class)->create(...array_replace([
            'name' => 'Visitor', 'email' => 'visitor@example.test', 'subject' => 'Support', 'message' => 'Help',
        ], $attributes));
    }

    private function manager(): void
    {
        $this->actingAs(Manager::create(['name' => 'Manager', 'email' => 'manager@example.test']), 'manager');
        $this->withSession(['locale' => 'fr']);
    }

    public function test_defaults_and_old_positional_signature_keep_normal_priority_and_numbering(): void
    {
        $old = app(TicketService::class)->create('Old caller', 'old@example.test', 'Old signature', 'Message', ContactSource::D1Survey,
            null, null, null, ['caller' => 'legacy']);
        $normal = $this->ticket();
        $high = $this->ticket(['priority' => ContactPriority::High]);
        $this->assertSame(ContactPriority::Normal, $old->priority);
        $this->assertSame(ContactPriority::Normal, $normal->fresh()->priority);
        $this->assertSame(ContactPriority::High, $high->fresh()->priority);
        $this->assertSame(['DT-000001', 'DT-000002', 'DT-000003'], [$old->ticket_num, $normal->ticket_num, $high->ticket_num]);
        $this->assertSame(ContactStatus::Pending, $high->status);
        $this->assertSame(['caller' => 'legacy'], $old->events()->sole()->metadata);
        $this->assertSame(1, $high->events()->count());
        Mail::assertNothingSent();
    }

    public function test_additive_migration_backfills_existing_contacts_and_rolls_back_cleanly(): void
    {
        $migration = require database_path('migrations/2026_10_29_000005_add_priority_to_contacts.php');
        $migration->down();
        $id = DB::table('contacts')->insertGetId(['name' => 'Existing', 'email' => 'existing@example.test',
            'subject' => 'Before migration', 'message' => 'Existing message', 'ticket_num' => 'DT-000099']);
        $migration->up();
        $existing = Contact::findOrFail($id);
        $this->assertSame(ContactPriority::Normal, $existing->priority);
        $this->assertSame(ContactStatus::Pending, $existing->status);
        $this->assertSame(ContactSource::Contact, $existing->source);
        $this->assertSame('Existing message', $existing->message);
        $migration->down();
        $this->assertSame('DT-000099', DB::table('contacts')->where('id', $id)->value('ticket_num'));
        $this->assertFalse(Schema::hasColumn($this->schema.'.contacts', 'priority'));
        $migration->up();
    }

    public function test_database_rejects_unknown_priority_and_null(): void
    {
        $ticket = $this->ticket();
        foreach (['urgent', null] as $priority) {
            try {
                DB::transaction(fn () => DB::table('contacts')->where('id', $ticket->id)->update(['priority' => $priority]));
                $this->fail('Invalid priority accepted.');
            } catch (QueryException $exception) {
                $this->assertContains($exception->getCode(), ['23514', '23502']);
            }
        }
    }

    public function test_manager_cards_show_a_badge_only_for_high_and_detail_shows_priority(): void
    {
        $this->manager();
        $normal = $this->ticket(['subject' => 'Normal ticket']);
        $high = $this->ticket(['subject' => 'High ticket', 'priority' => ContactPriority::High]);
        $response = $this->get(route('manager.support.index'));
        $response->assertOk()->assertSee('Toutes les priorités')->assertSee('name="priority"', false);
        $this->assertSame(1, substr_count($response->getContent(), 'class="cs-badge cs-priority-high"'));
        $this->get(route('manager.support.show', $high))->assertOk()->assertSee('Prioritaire')->assertSee('cs-priority-high');
        $this->get(route('manager.support.show', $normal))->assertOk()->assertSee('Priorité')->assertDontSee('cs-priority-high');
    }

    public function test_normal_high_and_combined_filters_work_on_the_server(): void
    {
        $this->manager();
        $normal = $this->ticket(['subject' => 'Match normal', 'source' => ContactSource::D1Survey]);
        $high = $this->ticket(['subject' => 'Match high', 'source' => ContactSource::D1Survey, 'priority' => ContactPriority::High]);
        $this->ticket(['subject' => 'Other high', 'priority' => ContactPriority::High]);
        $this->get(route('manager.support.index', ['priority' => ContactPriority::Normal->value]))->assertOk()
            ->assertViewHas('contacts', fn ($contacts) => $contacts->pluck('id')->all() === [$normal->id]);
        $this->get(route('manager.support.index', ['priority' => ContactPriority::High->value]))->assertOk()
            ->assertViewHas('contacts', fn ($contacts) => $contacts->total() === 2);
        $this->get(route('manager.support.index', ['priority' => ContactPriority::High->value, 'status' => ContactStatus::Pending->value,
            'source' => ContactSource::D1Survey->value, 'q' => 'Match', 'sort' => 'oldest']))->assertOk()
            ->assertViewHas('contacts', fn ($contacts) => $contacts->pluck('id')->all() === [$high->id]);
        $this->getJson(route('manager.support.index', ['priority' => 'urgent']))->assertUnprocessable()->assertJsonValidationErrors('priority');
    }

    public function test_pagination_preserves_priority_search_status_source_and_sort(): void
    {
        $this->manager();
        for ($i = 0; $i < 17; $i++) {
            $this->ticket(['subject' => 'Match '.$i, 'priority' => ContactPriority::High, 'source' => ContactSource::D1Survey]);
        }
        $filters = ['priority' => ContactPriority::High->value, 'q' => 'Match', 'status' => 'pending',
            'source' => ContactSource::D1Survey->value, 'sort' => 'oldest', 'per_page' => 15];
        $this->get(route('manager.support.index', $filters))->assertOk()->assertViewHas('contacts', function ($contacts) use ($filters) {
            parse_str(parse_url($contacts->nextPageUrl(), PHP_URL_QUERY), $query);
            foreach ($filters as $key => $value) {
                $this->assertSame((string) $value, (string) $query[$key]);
            }

            return $contacts->total() === 17;
        });
        $this->get(route('manager.support.index', $filters + ['page' => 2]))->assertOk()
            ->assertViewHas('contacts', fn ($contacts) => $contacts->count() === 2);
    }

    public function test_default_sort_is_chronological_not_priority_first(): void
    {
        $this->manager();
        $high = $this->ticket(['priority' => ContactPriority::High]);
        $normal = $this->ticket();
        $this->get(route('manager.support.index'))->assertOk()
            ->assertViewHas('contacts', fn ($contacts) => $contacts->pluck('id')->all() === [$normal->id, $high->id]);
        $this->get(route('manager.support.index', ['sort' => 'oldest']))->assertOk()
            ->assertViewHas('contacts', fn ($contacts) => $contacts->pluck('id')->all() === [$high->id, $normal->id]);
    }

    public function test_existing_manager_actions_work_for_high_without_altering_priority(): void
    {
        $this->manager();
        $ticket = $this->ticket(['priority' => ContactPriority::High]);
        $this->post(route('manager.support.reply', $ticket), ['message' => 'Support answer', 'submission_key' => (string) Str::uuid()])
            ->assertSessionHas('success');
        $this->assertSame(ContactStatus::Replied, $ticket->fresh()->status);
        $this->assertSame(ContactPriority::High, $ticket->fresh()->priority);
        $this->post(route('manager.support.close', $ticket), ['confirmed' => 1])->assertSessionHas('success');
        $this->assertSame(ContactStatus::Closed, $ticket->fresh()->status);
        $this->post(route('manager.support.reopen', $ticket))->assertSessionHas('success');
        $this->assertSame(ContactStatus::Pending, $ticket->fresh()->status);
        $this->assertSame(ContactPriority::High, $ticket->fresh()->priority);
        $this->delete(route('manager.support.destroy', $ticket), ['confirmation' => $ticket->ticket_num])->assertSessionHas('success');
        $this->assertSame(0, Contact::count());
        $this->assertSame('DT-000002', $this->ticket()->ticket_num);
    }

    public function test_labels_are_translated_in_all_four_locales(): void
    {
        $this->manager();
        $ticket = $this->ticket(['priority' => ContactPriority::High]);
        foreach (['fr', 'en', 'nl', 'es'] as $locale) {
            $this->withSession(['locale' => $locale])->get(route('manager.support.index'))->assertOk()
                ->assertSee(__('support.all_priorities', [], $locale))->assertSee(__('support.priorities.high', [], $locale));
            $this->get(route('manager.support.show', $ticket))->assertOk()->assertSee(__('support.priority', [], $locale));
        }
    }
}
