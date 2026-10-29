<?php

namespace Tests\Feature;

use App\Enums\ContactEmailStatus;
use App\Enums\ContactEventType;
use App\Enums\ContactSource;
use App\Enums\ContactStatus;
use App\Models\Contact;
use App\Models\ContactEvent;
use App\Models\Entreprise;
use App\Models\Manager;
use App\Models\SupportTicket;
use App\Models\User;
use App\Services\TicketService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ManagerContactSupportTest extends TestCase
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

    public function test_guest_and_web_user_cannot_access_list_or_detail(): void
    {
        $ticket = $this->ticket();
        foreach (['guest', 'web'] as $identity) {
            if ($identity === 'web') {
                $this->actingAs(User::create(['name' => 'Client', 'email' => 'client@example.test', 'entreprise_id' => 43]), 'web');
            }
            $this->getJson('/manager/support')->assertUnauthorized();
            $this->getJson('/manager/support/'.$ticket->id)->assertUnauthorized();
            $this->assertGuest('manager');
        }
    }

    public function test_guest_browser_is_sent_to_manager_login(): void
    {
        $this->get('/manager/support')->assertRedirect(route('login'));
    }

    public function test_existing_manager_session_can_access_support_without_additional_proof(): void
    {
        $manager = Manager::create(['name' => 'Legacy session']);
        $this->actingAs($manager, 'manager')->get('/manager/support')->assertOk();
        $this->assertAuthenticatedAs($manager, 'manager');
    }

    public function test_real_credentials_login_unlocks_support(): void
    {
        $manager = Manager::create(['name' => 'Real Manager', 'email' => 'real@example.test', 'password' => 'Strong-test-password']);
        $this->post('/manager/login', ['email' => $manager->email, 'password' => 'Strong-test-password'])->assertRedirect(route('manager.dashboard'));
        $this->assertAuthenticatedAs($manager, 'manager');
        $this->get('/manager/support')->assertOk();
    }

    public function test_invalid_manager_credentials_do_not_authenticate(): void
    {
        Manager::create(['name' => 'Real Manager', 'email' => 'real@example.test', 'password' => 'Strong-test-password']);
        $this->post('/manager/login', ['email' => 'real@example.test', 'password' => 'wrong'])->assertSessionHasErrors('email');
        $this->assertGuest('manager');
    }

    public function test_manager_reads_contacts_only_and_does_not_change_them(): void
    {
        $this->managerLogin();
        $ticket = $this->ticket(['name' => 'Public visitor']);
        $user = User::create(['name' => 'Legacy', 'email' => 'legacy@example.test']);
        SupportTicket::create(['user_id' => $user->id, 'subject' => 'Legacy support hidden', 'message' => 'Old support', 'priority' => 'medium', 'status' => 'open']);
        $this->get('/manager/support')->assertOk()->assertSee($ticket->ticket_num)->assertDontSee('Legacy support hidden')
            ->assertViewHas('contacts', fn ($contacts) => $contacts->total() === 1 && ! array_key_exists('message', $contacts->first()->getAttributes()));
        $this->get('/manager/support/'.$ticket->id)->assertOk()->assertSee('Public visitor')->assertSee('A question');
        $this->assertSame(ContactStatus::Pending, $ticket->fresh()->status);
        $this->assertSame(1, ContactEvent::count());
        Mail::assertNothingSent();
    }

    public function test_search_matches_number_name_email_and_subject_case_insensitively(): void
    {
        $this->managerLogin();
        $ticket = $this->ticket(['name' => 'Alice Target', 'email' => 'alice@example.test', 'subject' => 'Blocking export']);
        $this->ticket(['name' => 'Bob', 'subject' => 'Other']);
        foreach ([$ticket->ticket_num, 'ALICE TARGET', 'alice@example.test', 'BLOCKING EXPORT'] as $search) {
            $this->get('/manager/support?'.http_build_query(['q' => $search]))->assertOk()
                ->assertViewHas('contacts', fn ($contacts) => $contacts->total() === 1 && $contacts->first()->id === $ticket->id);
        }
    }

    public function test_search_treats_sql_wildcards_as_literal_text(): void
    {
        $this->managerLogin();
        $this->ticket(['subject' => 'Literal 100%_!']);
        $this->ticket(['subject' => 'Normal']);
        $this->get('/manager/support?'.http_build_query(['q' => '%_!']))->assertOk()->assertViewHas('contacts', fn ($contacts) => $contacts->total() === 1);
    }

    public function test_filters_combine_and_counters_are_global(): void
    {
        $this->managerLogin();
        $this->ticket();
        $target = $this->ticket(['subject' => 'Target', 'source' => ContactSource::D2Survey]);
        $target->update(['status' => ContactStatus::Replied]);
        $closed = $this->ticket();
        $closed->update(['status' => ContactStatus::Closed]);
        $this->get('/manager/support?q=Target&status=replied&source=d2-survey')->assertOk()
            ->assertViewHas('contacts', fn ($contacts) => $contacts->total() === 1 && $contacts->first()->id === $target->id)
            ->assertViewHas('counts', fn ($counts) => $counts === ['all' => 3, 'closed' => 1, 'pending' => 1, 'replied' => 1]
                || ($counts['all'] === 3 && $counts['pending'] === 1 && $counts['replied'] === 1 && $counts['closed'] === 1));
    }

    public function test_newest_and_oldest_sort_are_stable(): void
    {
        $this->managerLogin();
        $first = $this->ticket();
        $second = $this->ticket();
        $this->get('/manager/support')->assertViewHas('contacts', fn ($contacts) => $contacts->first()->id === $second->id);
        $this->get('/manager/support?sort=oldest')->assertViewHas('contacts', fn ($contacts) => $contacts->first()->id === $first->id);
    }

    public function test_pagination_sizes_and_filter_links_are_preserved(): void
    {
        $this->managerLogin();
        for ($i = 0; $i < 17; $i++) {
            $this->ticket(['subject' => 'Batch']);
        }
        $url = '/manager/support?q=Batch&status=pending&source=contact&sort=oldest&per_page=15';
        $this->get($url)->assertOk()->assertViewHas('contacts', function ($contacts) {
            $link = $contacts->nextPageUrl();
            foreach (['q=Batch', 'status=pending', 'source=contact', 'sort=oldest', 'per_page=15', 'page=2'] as $part) {
                $this->assertStringContainsString($part, $link);
            }

            return $contacts->count() === 15 && $contacts->total() === 17;
        });
        $this->get($url.'&page=2')->assertViewHas('contacts', fn ($contacts) => $contacts->count() === 2);
        foreach ([25, 50, 100] as $size) {
            $this->get('/manager/support?per_page='.$size)->assertViewHas('contacts', fn ($contacts) => $contacts->perPage() === $size);
        }
    }

    public function test_invalid_filters_are_rejected(): void
    {
        $this->managerLogin();
        foreach (['status' => 'open', 'source' => 'invalid', 'sort' => 'injected', 'per_page' => 10000] as $key => $value) {
            $this->getJson('/manager/support?'.http_build_query([$key => $value]))->assertUnprocessable()->assertJsonValidationErrors($key);
        }
    }

    public function test_detail_shows_links_and_escaped_history_without_email_secrets(): void
    {
        $manager = $this->managerLogin();
        $company = Entreprise::create(['name' => 'Linked Company']);
        $user = User::create(['name' => 'Linked User', 'email' => 'linked@example.test', 'entreprise_id' => $company->id]);
        $ticket = $this->ticket(['user' => $user, 'entreprise' => $company, 'message' => "Line one\n<script>alert('unsafe')</script>", 'ip' => '203.0.113.8']);
        $ticket->events()->create(['manager_id' => $manager->id, 'event_type' => ContactEventType::Closed, 'metadata' => ['secret' => 'event-secret-hidden']]);
        $ticket->emailLogs()->create(['recipient_email' => 'recipient@example.test', 'subject' => 'Email subject visible', 'body' => 'email-body-hidden', 'status' => ContactEmailStatus::Failed, 'error_message' => 'smtp-password-hidden', 'attempted_at' => now(), 'failed_at' => now()]);
        $ticket->replies()->create(['manager_id' => $manager->id, 'message' => '<img src=x onerror=alert(1)>']);
        $response = $this->withSession(['locale' => 'fr'])->get('/manager/support/'.$ticket->id)->assertOk();
        foreach (['Linked Company', 'Linked User', '203.0.113.8', 'Ticket créé', 'Ticket fermé', 'recipient@example.test', 'Email subject visible', 'Échec'] as $text) {
            $response->assertSee($text);
        }
        foreach (["<script>alert('unsafe')</script>", '<img src=x onerror=alert(1)>', 'smtp-password-hidden', 'event-secret-hidden', 'email-body-hidden'] as $text) {
            $response->assertDontSee($text, false);
        }
        $response->assertSee('&lt;script&gt;', false)->assertSee('&lt;img', false);
    }

    public function test_detail_histories_are_paginated_and_manager_relations_eager_loaded(): void
    {
        $manager = $this->managerLogin();
        $ticket = $this->ticket();
        for ($i = 0; $i < 23; $i++) {
            $ticket->events()->create(['manager_id' => $manager->id, 'event_type' => ContactEventType::Replied]);
            $ticket->replies()->create(['manager_id' => $manager->id, 'message' => 'Existing reply '.$i]);
        }
        $this->get('/manager/support/'.$ticket->id)->assertOk()
            ->assertViewHas('events', fn ($events) => $events->count() === 20 && $events->total() === 24 && $events->last()->relationLoaded('manager'))
            ->assertViewHas('replies', fn ($replies) => $replies->count() === 20 && $replies->total() === 23 && $replies->last()->relationLoaded('manager'));
    }

    public function test_empty_state_and_missing_contact_are_handled(): void
    {
        $this->managerLogin();
        $this->get('/manager/support')->assertOk()->assertViewHas('counts', ['all' => 0]);
        $ticket = $this->ticket();
        $this->withSession(['locale' => 'fr'])->get('/manager/support/'.$ticket->id)->assertOk()->assertSee('Aucune réponse enregistrée.')->assertSee('Aucun email enregistré.');
        $this->get('/manager/support/99999')->assertNotFound();
    }

    public function test_consultation_routes_and_public_lookup_remain_protected(): void
    {
        $this->managerLogin();
        $ticket = $this->ticket();
        foreach (Route::getRoutes() as $route) {
            if (in_array($route->getName(), ['manager.support.index', 'manager.support.show'], true)) {
                $this->assertSame(['GET', 'HEAD'], $route->methods());
                $this->assertContains('auth:manager', $route->gatherMiddleware());
            }
        }
        $this->post('/manager/support/'.$ticket->id.'/reply')->assertSessionHasErrors(['message', 'submission_key']);
        $this->post('/manager/support/'.$ticket->id)->assertStatus(405);
        $this->get('/ticket/'.$ticket->ticket_num)->assertNotFound();
        $this->assertSame(1, ContactEvent::count());
        Mail::assertNothingSent();
    }

    public function test_support_labels_are_translated_and_sidebar_is_active(): void
    {
        $this->managerLogin();
        $ticket = $this->ticket();
        foreach (['fr', 'en', 'nl', 'es'] as $locale) {
            $this->withSession(['locale' => $locale])->get('/manager/support')->assertOk()->assertSee(trans('support.statuses.pending', [], $locale))->assertSee('manager/support');
            $this->get('/manager/support/'.$ticket->id)->assertOk()->assertSee(trans('support.events.created', [], $locale));
        }
    }

    public function test_desktop_and_mobile_preview_fixture_can_be_rendered(): void
    {
        $this->managerLogin();
        $this->withSession(['locale' => 'fr']);
        $ticket = $this->ticket(['name' => 'Camille Martin', 'email' => 'camille@example.test', 'subject' => 'Impossible de démarrer le suivi du temps', 'message' => "Bonjour,\nJe viens de créer mon projet et je ne parviens pas à lancer le chronomètre.\nPouvez-vous m’aider à vérifier la configuration ?\nMerci.", 'ip' => '203.0.113.20']);
        $ticket->emailLogs()->create(['recipient_email' => 'support@example.test', 'subject' => '['.$ticket->ticket_num.'] Demande de support', 'body' => 'Synthetic preview', 'status' => ContactEmailStatus::Sent, 'attempted_at' => now(), 'sent_at' => now()]);
        $this->ticket(['name' => 'Alex Bernard', 'email' => 'alex@example.test', 'subject' => 'Question sur les exports']);
        $this->ticket(['name' => 'Sofia Garcia', 'email' => 'sofia@example.test', 'subject' => 'Informations sur la configuration du compte'])->update(['status' => ContactStatus::Closed]);
        if (getenv('CONTACT_SUPPORT_PREVIEW') === '1') {
            $this->withVite();
        }
        $list = $this->get('/manager/support')->assertOk();
        $detail = $this->get('/manager/support/'.$ticket->id)->assertOk();
        if (getenv('CONTACT_SUPPORT_PREVIEW') === '1') {
            file_put_contents(base_path('tmp/contact-support-list.html'), $list->getContent());
            file_put_contents(base_path('tmp/contact-support-detail.html'), $detail->getContent());
        }
    }

    public function test_existing_manager_support_ignores_an_unrelated_web_subscription(): void
    {
        $this->managerLogin();
        $user = User::create(['name' => 'Web User', 'email' => 'web@example.test']);
        $this->actingAs($user, 'web');
        $middleware = \Mockery::mock(\App\Http\Middleware\CheckSubscription::class);
        $middleware->shouldNotReceive('handle');
        $this->app->instance(\App\Http\Middleware\CheckSubscription::class, $middleware);
        $this->get('/manager/support')->assertOk();
    }

    public function test_original_shortcut_logs_enterprise_43_user_in_as_first_manager(): void
    {
        Schema::create('roles', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
        });
        Schema::create('model_has_roles', function ($table) {
            $table->unsignedBigInteger('role_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
        });
        $roleId = DB::table('roles')->insertGetId(['name' => 'Admin', 'guard_name' => 'web']);
        $user = User::create(['name' => 'Original shortcut user', 'email' => 'shortcut@example.test', 'entreprise_id' => 43]);
        DB::table('model_has_roles')->insert(['role_id' => $roleId, 'model_type' => User::class, 'model_id' => $user->id]);
        $first = Manager::create(['name' => 'First manager']);
        Manager::create(['name' => 'Second manager']);
        $this->actingAs($user, 'web')->get('/manager/direct-dashboard')->assertRedirect(route('manager.dashboard'));
        $this->assertAuthenticatedAs($first, 'manager');
        $this->get('/manager/support')->assertOk();
        // Existing Manager session is retained by the original shortcut.
        $this->actingAs($user, 'web')->get('/manager/direct-dashboard')->assertRedirect(route('manager.dashboard'));
        $this->assertAuthenticatedAs($first, 'manager');
        $user->update(['entreprise_id' => 44]);
        $this->actingAs($user, 'web')->get('/manager/direct-dashboard')->assertForbidden();
    }
}
