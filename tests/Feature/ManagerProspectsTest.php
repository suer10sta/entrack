<?php

namespace Tests\Feature;

use App\Enums\ProspectStatus;
use App\Models\Manager;
use App\Models\Prospect;
use App\Models\User;
use App\Services\ProspectStatusService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Tests\TestCase;

class ManagerProspectsTest extends TestCase
{
    private ?string $schema = null;

    private Manager $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $database = config('database.connections.pgsql.database');
        if (! is_string($database) || ! preg_match('/(_test|_testing)$/', $database) || $database === $this->developmentDatabaseName()) {
            throw new RuntimeException('CRM UI tests require a dedicated PostgreSQL testing database.');
        }
        config(['database.default' => 'pgsql', 'cache.default' => 'array', 'session.driver' => 'array']);
        $name = 'crm_ui_test_'.bin2hex(random_bytes(8));
        DB::statement('CREATE SCHEMA "'.$name.'"');
        $this->schema = $name;
        DB::statement('SET search_path TO "'.$name.'"');
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
            $table->unsignedBigInteger('entreprise_id')->nullable();
            $table->timestamps();
        });
        foreach (glob(database_path('migrations/2026_10_27_00000*_*.php')) as $path) {
            (require $path)->up();
        }
        (require database_path('migrations/2026_10_27_000100_create_crm_campaigns.php'))->up();
        (require database_path('migrations/2026_10_27_000104_add_campaign_tracking.php'))->up();
        $this->manager = Manager::create(['name' => 'CRM Manager', 'email' => 'manager@example.test']);
        $this->withoutVite();
        Http::preventStrayRequests();
    }

    protected function tearDown(): void
    {
        try {
            if ($this->schema !== null) {
                DB::statement('SET search_path TO public');
                DB::statement('DROP SCHEMA "'.$this->schema.'" CASCADE');
            }
        } finally {
            parent::tearDown();
        }
    }

    private function login(): self
    {
        $this->actingAs($this->manager, 'manager');

        return $this;
    }

    private function prospect(array $data = []): Prospect
    {
        return Prospect::create($data + ['name' => 'Alice Dupont', 'email' => 'alice@example.test']);
    }

    public function test_all_crm_routes_require_manager_even_with_a_web_user(): void
    {
        $prospect = $this->prospect();
        foreach (['guest', 'web'] as $identity) {
            if ($identity === 'web') {
                $this->actingAs(User::create(['name' => 'Client User', 'email' => 'client@example.test']), 'web');
            }
            foreach (['index', 'create', 'show', 'edit'] as $route) {
                $this->getJson(route('manager.prospects.'.$route, in_array($route, ['show', 'edit']) ? $prospect : []))->assertUnauthorized();
            }
            $this->postJson(route('manager.prospects.store'), ['name' => 'Unauthorized'])->assertUnauthorized();
            $this->patchJson(route('manager.prospects.update', $prospect), ['name' => 'Unauthorized'])->assertUnauthorized();
            $this->patchJson(route('manager.prospects.status', $prospect), ['status' => 'client'])->assertUnauthorized();
            $this->postJson(route('manager.prospects.activities.store', $prospect), ['type' => 'note', 'body' => 'Unauthorized'])->assertUnauthorized();
            $this->deleteJson(route('manager.prospects.archive', $prospect))->assertUnauthorized();
        }
        $this->assertSame('Alice Dupont', $prospect->fresh()->name);
        $this->assertSame(1, Prospect::count());
    }

    public function test_manager_can_render_all_pages_and_seo_navigation(): void
    {
        $prospect = $this->prospect(['source' => 'Salon']);
        $this->login()->get(route('manager.prospects.index'))->assertOk()->assertSee('Total prospects')->assertSee('Alice Dupont')->assertSee('Salon')->assertSee(route('manager.prospects.index'));
        $this->get(route('manager.prospects.create'))->assertRedirect(route('manager.prospects.index', ['modal' => 'create']));
        $this->get(route('manager.prospects.index', ['modal' => 'create']))->assertOk()->assertSee('Prénom et nom')->assertViewHas('activeModal', 'create');
        $this->get(route('manager.prospects.show', $prospect))->assertOk()->assertSee('Timeline')->assertSee('Prospect créé');
        $this->get(route('manager.prospects.edit', $prospect))->assertRedirect(route('manager.prospects.show', ['prospect' => $prospect, 'modal' => 'edit']));
        $this->get(route('manager.prospects.show', ['prospect' => $prospect, 'modal' => 'edit']))->assertOk()->assertSee('alice@example.test')->assertSee('data-auto-open', false);
    }

    public function test_search_covers_all_contact_fields_and_escapes_wildcards(): void
    {
        $this->prospect(['phone' => '+331234567', 'company_name' => 'Atelier Signal']);
        $this->prospect(['name' => 'Bob Martin', 'email' => 'bob@example.test']);
        foreach (['alice', 'DUPONT', 'alice@example', '1234567', 'SIGNAL'] as $term) {
            $this->login()->get(route('manager.prospects.index', ['q' => $term]))->assertOk()->assertViewHas('prospects', fn ($rows) => $rows->total() === 1 && $rows->first()->name === 'Alice Dupont');
        }
        $this->get(route('manager.prospects.index', ['q' => '%']))->assertOk()->assertViewHas('prospects', fn ($rows) => $rows->total() === 0);
    }

    public function test_status_source_and_pagination_preserve_filters_and_global_counts(): void
    {
        for ($i = 0; $i < 17; $i++) {
            $prospect = $this->prospect(['name' => 'Salon '.$i, 'source' => 'Salon']);
            app(ProspectStatusService::class)->transition($prospect, ProspectStatus::Contacte, manager: $this->manager);
        }
        $this->prospect(['source' => 'Partenaire']);
        $params = ['q' => 'Salon', 'status' => 'contacte', 'source' => 'Salon', 'sort' => 'oldest'];
        $this->login()->get(route('manager.prospects.index', $params))->assertOk()->assertViewHas('prospects', fn ($rows) => $rows->total() === 17 && $rows->count() === 15 && str_contains($rows->nextPageUrl(), 'source=Salon'))
            ->assertViewHas('counts', fn ($counts) => $counts->sum() === 18);
        $this->get(route('manager.prospects.index', $params + ['page' => 2]))->assertOk()->assertViewHas('prospects', fn ($rows) => $rows->count() === 2);
        $this->get(route('manager.prospects.index', $params + ['per_page' => 25]))->assertOk()->assertViewHas('prospects', fn ($rows) => $rows->count() === 17);
    }

    public function test_activity_sort_uses_history_and_activities(): void
    {
        $first = $this->prospect(['name' => 'Premier']);
        $second = $this->prospect(['name' => 'Second']);
        $first->activities()->create(['type' => 'call', 'body' => 'Appel', 'occurred_at' => now()->addMinute()]);
        $this->login()->get(route('manager.prospects.index', ['sort' => 'activity']))->assertOk()->assertViewHas('prospects', fn ($rows) => $rows->first()->id === $first->id);
        app(ProspectStatusService::class)->transition($second, ProspectStatus::Engage, manager: $this->manager, occurredAt: now()->addMinutes(2));
        $this->get(route('manager.prospects.index', ['sort' => 'activity']))->assertOk()->assertViewHas('prospects', fn ($rows) => $rows->first()->id === $second->id);
    }

    public function test_creation_uses_service_normalizes_email_and_records_initial_note(): void
    {
        $this->login()->post(route('manager.prospects.store'), ['name' => 'Claire Test', 'email' => ' Contact.Name+Tag@EXAMPLE.test ', 'status' => 'engage', 'initial_note' => 'Premier contact', 'utm_campaign' => 'launch', 'linked_user_id' => 999, 'email_normalized' => 'fake'])->assertRedirect();
        $prospect = Prospect::firstOrFail();
        $this->assertSame('contact.name+tag@example.test', $prospect->email_normalized);
        $this->assertSame(ProspectStatus::Engage, $prospect->status);
        $this->assertNull($prospect->linked_user_id);
        $this->assertSame('launch', $prospect->utm_campaign);
        $this->assertSame('nouveau', $prospect->statusHistories->first()->from_status->value);
        $this->assertSame($this->manager->id, $prospect->statusHistories->first()->manager_id);
        $this->assertSame('Premier contact', $prospect->activities->first()->body);
        $this->post(route('manager.prospects.store'), ['name' => 'Default'])->assertRedirect();
        $this->assertSame(ProspectStatus::Nouveau, Prospect::latest('id')->first()->status);
    }

    public function test_update_keeps_account_links_and_does_not_bypass_status_service(): void
    {
        $enterpriseId = DB::table('entreprises')->insertGetId(['name' => 'Entreprise liée']);
        $user = User::create(['name' => 'Linked User', 'email' => 'linked@example.test', 'entreprise_id' => $enterpriseId]);
        $prospect = $this->prospect(['linked_user_id' => $user->id, 'linked_entreprise_id' => $enterpriseId]);
        $this->login()->patch(route('manager.prospects.update', $prospect), ['name' => 'Alice Modifiée', 'email' => 'New.Name+Tag@Example.test', 'status' => 'client', 'linked_user_id' => null])->assertRedirect();
        $prospect->refresh();
        $this->assertSame('new.name+tag@example.test', $prospect->email_normalized);
        $this->assertSame($user->id, $prospect->linked_user_id);
        $this->assertSame($enterpriseId, $prospect->linked_entreprise_id);
        $this->assertSame(ProspectStatus::Nouveau, $prospect->status);
        $this->assertSame(0, $prospect->statusHistories()->count());
    }

    public function test_status_changes_create_one_history_and_timeline_is_escaped(): void
    {
        $prospect = $this->prospect();
        foreach ([1, 2] as $attempt) {
            $this->login()->patch(route('manager.prospects.status', $prospect), ['status' => 'demo_essai', 'note' => '<script>alert(1)</script>'])->assertRedirect();
        }
        $this->assertSame(1, $prospect->statusHistories()->count());
        $this->assertSame($this->manager->id, $prospect->statusHistories()->first()->manager_id);
        $this->get(route('manager.prospects.show', $prospect))->assertOk()->assertSee('Démo / Essai')->assertSee('&lt;script&gt;', false)->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_manual_activities_and_followups_are_recorded_without_sending_email(): void
    {
        \Illuminate\Support\Facades\Mail::fake();
        $prospect = $this->prospect();
        foreach (['note', 'manual_contact', 'call', 'email', 'demo', 'follow_up'] as $type) {
            $this->login()->post(route('manager.prospects.activities.store', $prospect), ['type' => $type, 'body' => 'Action '.$type, 'due_at' => $type === 'follow_up' ? '2026-11-15T10:30' : null])->assertRedirect();
        }
        $this->assertSame(6, $prospect->activities()->count());
        $this->assertSame('2026-11-15 10:30', $prospect->fresh()->next_follow_up_at->format('Y-m-d H:i'));
        $this->get(route('manager.prospects.show', $prospect))->assertOk()->assertSee('Action email')->assertSee($prospect->activities()->where('type', 'follow_up')->sole()->due_at->utc()->format('d/m/Y H:i'));
        \Illuminate\Support\Facades\Mail::assertNothingSent();
        \Illuminate\Support\Facades\Mail::assertNothingQueued();
    }

    public function test_validation_rejects_invalid_status_activity_and_missing_followup_date(): void
    {
        $prospect = $this->prospect();
        $this->login()->postJson(route('manager.prospects.store'), ['name' => '', 'email' => 'invalid'])->assertUnprocessable()->assertJsonValidationErrors(['name', 'email']);
        $this->patchJson(route('manager.prospects.status', $prospect), ['status' => 'unknown'])->assertUnprocessable();
        $this->postJson(route('manager.prospects.activities.store', $prospect), ['type' => 'campaign', 'body' => 'No'])->assertUnprocessable();
        $this->postJson(route('manager.prospects.activities.store', $prospect), ['type' => 'follow_up', 'body' => 'Call'])->assertUnprocessable()->assertJsonValidationErrors('due_at');
        $this->getJson(route('manager.prospects.index', ['per_page' => 999]))->assertUnprocessable();
        $this->assertSame(0, $prospect->activities()->count());
    }

    public function test_archive_preserves_history_activities_and_linked_accounts(): void
    {
        $enterpriseId = DB::table('entreprises')->insertGetId(['name' => 'Entreprise liée']);
        $user = User::create(['name' => 'User', 'email' => 'user@example.test', 'entreprise_id' => $enterpriseId]);
        $prospect = $this->prospect(['linked_user_id' => $user->id, 'linked_entreprise_id' => $enterpriseId]);
        app(ProspectStatusService::class)->transition($prospect, ProspectStatus::Contacte, manager: $this->manager);
        $prospect->activities()->create(['type' => 'note', 'body' => 'Conserver', 'occurred_at' => now()]);
        $this->login()->delete(route('manager.prospects.archive', $prospect))->assertRedirect(route('manager.prospects.index'));
        $this->assertSoftDeleted('prospects', ['id' => $prospect->id]);
        $this->assertSame(1, $prospect->statusHistories()->count());
        $this->assertSame(1, $prospect->activities()->count());
        $this->assertSame(1, User::count());
        $this->assertSame(1, DB::table('entreprises')->count());
        $this->get(route('manager.prospects.show', $prospect))->assertNotFound();
        $this->get(route('manager.prospects.index'))->assertOk()->assertViewHas('prospects', fn ($rows) => $rows->total() === 0);
    }

    public function test_combined_timeline_is_paginated_and_shows_creation_only_on_last_page(): void
    {
        $prospect = $this->prospect();
        for ($i = 0; $i < 21; $i++) {
            $prospect->activities()->create(['type' => 'note', 'body' => 'Note '.$i, 'occurred_at' => now()->addSeconds($i)]);
        }
        $this->login()->get(route('manager.prospects.show', $prospect))->assertOk()
            ->assertViewHas('timeline', fn ($rows) => $rows->total() === 21 && $rows->count() === 20)
            ->assertDontSee('Prospect créé');
        $this->get(route('manager.prospects.show', $prospect).'?page=2')->assertOk()
            ->assertViewHas('timeline', fn ($rows) => $rows->count() === 1)->assertSee('Prospect créé');
    }

    public function test_timeline_normalizes_database_offsets_to_utc_once(): void
    {
        $prospect = Prospect::create(['name' => 'Timezone test']);
        DB::table('prospects')->where('id', $prospect->id)->update(['created_at' => '2026-10-28 13:40:00+00']);
        $prospect->activities()->create(['type' => 'note', 'body' => 'Timezone note', 'occurred_at' => '2026-10-28 13:45:00+00']);
        DB::table('prospect_activities')->where('prospect_id', $prospect->id)->update(['occurred_at' => '2026-10-28 13:45:00+00']);
        foreach (['Europe/Paris', 'UTC'] as $timezone) {
            DB::statement("SET TIME ZONE '".$timezone."'");
            $this->actingAs($this->manager, 'manager')->get(route('manager.prospects.show', $prospect))
                ->assertOk()
                ->assertSee('datetime="2026-10-28T13:40:00+00:00">28/10/2026 13:40 UTC', false)
                ->assertSee('datetime="2026-10-28T13:45:00+00:00">28/10/2026 13:45 UTC', false);
        }
    }

    public function test_automatic_actions_combine_their_status_change_without_hiding_manual_history(): void
    {
        foreach ([
            ['registration', 'user:99:demo_essai', 'Compte DalyTrac créé / email vérifié', ['user_id' => 99], ProspectStatus::DemoEssai, 'Compte DalyTrac créé et email vérifié'],
            ['linkedin_click', null, 'Lien LinkedIn consulté', ['event' => 'linkedin_link_clicked'], ProspectStatus::Engage, 'Lien LinkedIn consulté'],
            ['campaign', null, 'Campagne email envoyée : Découverte', ['campaign_id' => 123], ProspectStatus::Contacte, 'Email de campagne envoyé'],
        ] as [$source, $key, $subject, $metadata, $target, $title]) {
            $prospect = Prospect::create(['name' => 'Combined '.$source]);
            $key ??= $source === 'campaign' ? 'campaign:123:prospect:'.$prospect->id : 'linkedin-click:'.$prospect->id;
            app(ProspectStatusService::class)->transition($prospect, $target, $source, idempotencyKey: $key);
            $prospect->activities()->create(['type' => $source === 'campaign' ? 'campaign' : 'automatic', 'subject' => $subject, 'metadata' => $metadata, 'occurred_at' => now('UTC')]);
            $this->actingAs($this->manager, 'manager')->get(route('manager.prospects.show', $prospect))->assertOk()
                ->assertSee($title)->assertSee('Statut passé de <strong>Nouveau</strong> à <strong>'.$target->label().'</strong>', false)
                ->assertViewHas('timeline', fn ($rows) => $rows->total() === 1 && $rows->first()->from_status === 'nouveau' && $rows->first()->to_status === $target->value);
            app(ProspectStatusService::class)->transition($prospect, ProspectStatus::Perdu, manager: $this->manager);
            $this->get(route('manager.prospects.show', $prospect))->assertOk()
                ->assertViewHas('timeline', fn ($rows) => $rows->total() === 2 && $rows->where('kind', 'status')->count() === 1);
            $this->assertSame(2, $prospect->statusHistories()->count());
        }
    }

    public function test_failed_initial_transition_rolls_back_the_entire_creation(): void
    {
        $this->mock(ProspectStatusService::class)->shouldReceive('transition')->once()->andThrow(new \DomainException('Transition failure'));
        $this->login();
        $this->withoutExceptionHandling();
        try {
            $this->post(route('manager.prospects.store'), ['name' => 'Rollback', 'status' => 'client', 'initial_note' => 'No orphan']);
            $this->fail('The simulated failure was ignored.');
        } catch (\DomainException $exception) {
            $this->assertSame(0, Prospect::count());
            $this->assertSame(0, DB::table('prospect_activities')->count());
        }
    }

    public function test_modal_creation_retains_values_on_validation_and_preserves_success_redirect(): void
    {
        $this->login()->post(route('manager.prospects.store'), ['_prospect_modal' => 'create', 'name' => 'Modal contact', 'email' => 'invalid'])
            ->assertRedirect(route('manager.prospects.index'))->assertSessionHasErrors('email')->assertSessionHas('prospect_modal', 'create');
        $this->get(route('manager.prospects.index'))->assertOk()->assertViewHas('activeModal', 'create')->assertSee('Modal contact');
        $this->post(route('manager.prospects.store'), ['_prospect_modal' => 'create', 'name' => 'Modal contact', 'email' => 'modal@example.test'])
            ->assertRedirect(route('manager.prospects.show', Prospect::sole()));
        $this->assertSame(ProspectStatus::Nouveau, Prospect::sole()->status);
        $this->assertMatchesRegularExpression('/\A[a-f0-9]{48}\z/', Prospect::sole()->public_token);
    }

    public function test_import_modal_reopens_after_invalid_file_and_alias_opens_list(): void
    {
        $this->login()->get(route('manager.prospects.import'))->assertRedirect(route('manager.prospects.index', ['modal' => 'import']));
        $this->post(route('manager.prospects.import.store'), [])->assertRedirect(route('manager.prospects.index'))
            ->assertSessionHasErrors('file')->assertSessionHas('prospect_modal', 'import');
        $this->get(route('manager.prospects.index'))->assertOk()->assertViewHas('activeModal', 'import');
    }

    public function test_detail_timeline_uses_meaningful_french_event_titles_and_keeps_forms(): void
    {
        $prospect = $this->prospect();
        foreach (['subscription_paid', 'Compte DalyTrac créé / email vérifié'] as $subject) {
            $prospect->activities()->create(['type' => 'automatic', 'subject' => $subject, 'body' => 'Confirmation métier.', 'occurred_at' => now()]);
        }
        $prospect->activities()->create(['type' => 'note', 'body' => 'Note du Manager', 'occurred_at' => now()]);
        app(ProspectStatusService::class)->transition($prospect, ProspectStatus::DemoEssai, manager: $this->manager);
        $this->login()->get(route('manager.prospects.show', $prospect))->assertOk()
            ->assertSee('Abonnement DalyTrac payé')->assertSee('Compte DalyTrac créé et email vérifié')
            ->assertSee('Statut passé de Nouveau à Démo / Essai')->assertSee('Note ajoutée')->assertSee('Prospect créé')
            ->assertDontSee('Événement automatique')->assertDontSee('subscription_paid')
            ->assertSee('Compte DalyTrac')->assertSee('Non rattaché')->assertSee('Notes et activités')
            ->assertSee(route('manager.prospects.activities.store', $prospect))->assertSee(route('manager.prospects.status', $prospect));
    }

    public function test_detail_shows_real_linked_account_names_and_escapes_event_subjects(): void
    {
        $enterpriseId = DB::table('entreprises')->insertGetId(['name' => 'Entreprise du contact']);
        $user = User::create(['name' => 'Compte du contact', 'email' => 'linked@example.test', 'entreprise_id' => $enterpriseId]);
        $prospect = $this->prospect(['linked_user_id' => $user->id, 'linked_entreprise_id' => $enterpriseId, 'linked_at' => now()]);
        $prospect->activities()->create(['type' => 'automatic', 'subject' => '<script>alert(1)</script>', 'body' => 'Description', 'occurred_at' => now()]);
        $this->login()->get(route('manager.prospects.show', $prospect))->assertOk()
            ->assertSee('Compte rattaché')->assertSee('Entreprise du contact')->assertSee('Compte du contact')->assertSee('linked@example.test')
            ->assertSee('&lt;script&gt;', false)->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee($prospect->linkedin_url);
    }
    public function test_edit_modal_preserves_hidden_acquisition_and_account_data_and_reopens_on_errors(): void
    {
        $prospect = $this->prospect(['source' => 'Source historique', 'utm_source' => 'linkedin', 'utm_campaign' => 'launch']);
        $token = $prospect->public_token;
        $this->login()->patch(route('manager.prospects.update', $prospect), ['_prospect_modal' => 'edit', 'name' => 'Corrected name', 'email' => 'invalid'])
            ->assertRedirect(route('manager.prospects.show', $prospect))->assertSessionHasErrors('email')->assertSessionHas('prospect_edit', true);
        $this->get(route('manager.prospects.show', $prospect))->assertOk()->assertSee('Corrected name')->assertSee('data-auto-open', false)->assertSee('Source historique');
        $this->patch(route('manager.prospects.update', $prospect), ['_prospect_modal' => 'edit', 'name' => 'Corrected name', 'email' => ' Updated@Example.test ', 'phone' => '+33123456789', 'company_name' => 'Company', 'country' => 'France', 'source' => 'Source historique'])
            ->assertRedirect(route('manager.prospects.show', $prospect));
        $prospect->refresh();
        $this->assertSame('updated@example.test', $prospect->email_normalized);
        $this->assertSame('linkedin', $prospect->utm_source);
        $this->assertSame('launch', $prospect->utm_campaign);
        $this->assertSame('Source historique', $prospect->source);
        $this->assertSame($token, $prospect->public_token);
        $this->assertSame(ProspectStatus::Nouveau, $prospect->status);
        $this->assertSame(0, $prospect->statusHistories()->count());
    }

    public function test_list_edit_modal_escapes_contact_data_and_omits_advanced_fields(): void
    {
        $prospect = $this->prospect(['name' => 'Contact "quoted" <script>alert(1)</script>', 'utm_campaign' => 'hidden-campaign']);
        $this->login()->get(route('manager.prospects.index'))->assertOk()->assertSee('prospect-edit-modal')->assertSee('data-edit-prospect', false)
            ->assertDontSee('<script>alert(1)</script>', false)->assertDontSee('name="utm_campaign"', false)->assertSee(route('manager.prospects.update', $prospect));
    }

}
