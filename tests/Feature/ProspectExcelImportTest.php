<?php

namespace Tests\Feature;

use App\Enums\ProspectStatus;
use App\Models\Manager;
use App\Models\Prospect;
use App\Models\User;
use App\Services\CrmRegistrationSync;
use App\Services\ProspectStatusService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use RuntimeException;
use Tests\TestCase;

class ProspectExcelImportTest extends TestCase
{
    private ?string $schema = null;

    private Manager $manager;

    private array $files = [];

    protected function setUp(): void
    {
        parent::setUp();
        $database = config('database.connections.pgsql.database');
        if (! is_string($database) || ! preg_match('/(_test|_testing)$/', $database) || $database === $this->developmentDatabaseName()) {
            throw new RuntimeException('Excel import tests require a dedicated PostgreSQL testing database.');
        }
        config(['database.default' => 'pgsql', 'cache.default' => 'array', 'session.driver' => 'array']);
        $name = 'crm_import_test_'.bin2hex(random_bytes(8));
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
        $this->manager = Manager::create(['name' => 'Import Manager', 'email' => 'manager@example.test']);
        $this->withoutVite();
        Http::preventStrayRequests();
        $this->mock(CrmRegistrationSync::class)->shouldNotReceive('registered', 'verified');
    }

    protected function tearDown(): void
    {
        try {
            foreach ($this->files as $path) {
                if (is_file($path)) {
                    unlink($path);
                }
            }
            if ($this->schema !== null) {
                DB::statement('SET search_path TO public');
                DB::statement('DROP SCHEMA "'.$this->schema.'" CASCADE');
            }
        } finally {
            parent::tearDown();
        }
    }

    private function workbook(array $rows, ?callable $edit = null): UploadedFile
    {
        $book = new Spreadsheet;
        foreach ($rows as $row => $values) {
            foreach ($values as $column => $value) {
                if ($value !== null) {
                    $book->getActiveSheet()->setCellValueExplicit([$column + 1, $row + 1], (string) $value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                }
            }
        }
        if ($edit) {
            $edit($book);
        }
        $path = tempnam(sys_get_temp_dir(), 'crm_excel_');
        $this->files[] = $path;
        (new Xlsx($book))->save($path);
        $book->disconnectWorksheets();

        return new UploadedFile($path, 'prospects.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
    }

    private function csv(array $rows, string $delimiter = ',', bool $bom = false): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'crm_csv_');
        $this->files[] = $path;
        $stream = fopen($path, 'wb');
        if ($bom) {
            fwrite($stream, "\xEF\xBB\xBF");
        }
        foreach ($rows as $row) {
            fputcsv($stream, $row, $delimiter, '"', '');
        }
        fclose($stream);

        return new UploadedFile($path, 'prospects.csv', 'text/csv', null, true);
    }

    public function test_csv_uses_the_same_validation_duplicates_and_creation_flow(): void
    {
        Prospect::create(['name' => 'Existing', 'email' => 'existing@example.test']);
        foreach ([',', ';'] as $index => $delimiter) {
            $this->upload($this->csv([
                ['Prénom', 'Nom', 'Email', 'Téléphone', 'Société', 'Pays', 'source', 'utm_campaign', 'status', 'linked_user_id'],
                ['Élodie', 'Test', " Fresh{$index}@Example.test ", '+001234', 'Studio, Paris', 'France', 'LinkedIn', 'launch', 'engage', '999'],
                ['Repeat', 'Test', "FRESH{$index}@example.test", '', '', '', '', '', '', ''],
                ['Existing', 'Test', 'EXISTING@example.test', '', '', '', '', '', '', ''],
                ['Bad', 'Email', 'invalid', '', '', '', '', '', '', ''],
                ['', '', '', '', '', '', '', '', '', ''],
                ['Default', 'Status', "default{$index}@example.test", '00123', '', 'Maroc', '', '', 'unsupported', ''],
            ], $delimiter, true))->assertRedirect();
            $result = $this->importResult();
            $this->assertSame(2, $result['created']);
            $this->assertSame(2, $result['duplicated']);
            $this->assertSame(1, $result['invalid']);
            $this->assertSame(1, $result['skipped']);
            $prospect = Prospect::where('email_normalized', "fresh{$index}@example.test")->sole();
            $this->assertSame('Élodie Test', $prospect->name);
            $this->assertSame('+001234', $prospect->phone);
            $this->assertSame('Studio, Paris', $prospect->company_name);
            $this->assertSame('France', $prospect->country);
            $this->assertSame('launch', $prospect->utm_campaign);
            $this->assertSame(ProspectStatus::Engage, $prospect->status);
            $this->assertNull($prospect->linked_user_id);
            $this->assertNull($prospect->linked_entreprise_id);
            $default = Prospect::where('email_normalized', "default{$index}@example.test")->sole();
            $this->assertSame(ProspectStatus::Nouveau, $default->status);
            $this->assertSame('00123', $default->phone);
        }
        $this->assertSame(0, DB::table('users')->count());
        $this->assertSame(0, DB::table('entreprises')->count());
        $this->assertSame(0, DB::table('crm_lifecycle_events')->count());
        Http::assertNothingSent();
    }

    public function test_selected_import_source_applies_to_xlsx_and_csv_without_touching_duplicates(): void
    {
        $existing = Prospect::create(['name' => 'Existing', 'email' => 'existing@example.test', 'source' => 'Google']);
        $this->actingAs($this->manager, 'manager');
        foreach (['workbook', 'csv'] as $format) {
            $file = $this->$format([['name', 'email', 'source'], ['New', $format.'@example.test', 'Email'], ['Duplicate', 'existing@example.test', 'Email'], ['', '', '']]);
            $this->post(route('manager.prospects.import.store'), ['file' => $file, 'import_source' => 'LinkedIn'])->assertRedirect();
            $this->assertSame('LinkedIn', Prospect::where('email_normalized', $format.'@example.test')->sole()->source);
            $this->assertSame('Google', $existing->fresh()->source);
            $this->assertSame(1, $this->importResult()['created']);
            $this->assertSame(1, $this->importResult()['duplicated']);
            $this->assertSame(1, $this->importResult()['skipped']);
        }
        $this->post(route('manager.prospects.import.store'), ['file' => $this->csv([['name'], ['Invalid source']]), 'import_source' => 'unsupported'])->assertSessionHasErrors('import_source');
        $this->assertSame(3, Prospect::count());
        $this->get(route('manager.prospects.index', ['modal' => 'import']))->assertOk()->assertSee('import-source', false)->assertSee('Utiliser la source du fichier');
    }

    public function test_csv_invalid_headers_and_limits_reject_before_writes(): void
    {
        foreach ([[['email'], ['only@example.test']], [['name', 'Email', 'email'], ['Duplicate', 'a@example.test', 'b@example.test']], array_merge([['name']], array_fill(0, 1001, ['Contact']))] as $rows) {
            $this->upload($this->csv($rows))->assertSessionHasErrors('file');
        }
        $this->assertSame(0, Prospect::count());
    }

    private function upload(UploadedFile $file)
    {
        $this->actingAs($this->manager, 'manager');

        return $this->post(route('manager.prospects.import.store'), ['file' => $file]);
    }

    private function importResult(): array
    {
        return Cache::get('crm:prospect-import:'.$this->manager->id.':'.session('prospect_import_report'));
    }

    public function test_import_routes_require_manager_for_guests_and_regular_users(): void
    {
        foreach (['guest', 'web'] as $identity) {
            if ($identity === 'web') {
                $this->actingAs(User::create(['name' => 'Client', 'email' => 'client@example.test']), 'web');
            }
            $this->getJson(route('manager.prospects.import'))->assertUnauthorized();
            $this->postJson(route('manager.prospects.import.store'))->assertUnauthorized();
        }
        $this->assertSame(0, Prospect::count());
    }

    public function test_manager_sees_import_button_and_upload_form(): void
    {
        $this->actingAs($this->manager, 'manager');
        $this->get(route('manager.prospects.index'))->assertOk()->assertSee('Importer Excel')->assertSee(route('manager.prospects.import'));
        $this->get(route('manager.prospects.index', ['modal' => 'import']))->assertOk()->assertSee('multipart/form-data', false)->assertSee('Valider et importer')->assertSee('utm_campaign');
    }

    public function test_each_imported_prospect_automatically_gets_its_own_public_link(): void
    {
        $this->upload($this->workbook([['name', 'email', 'public_token'], ['First', 'first@example.test', 'forged'], ['Second', 'second@example.test', 'forged']]))->assertRedirect();
        $tokens = Prospect::pluck('public_token')->all();
        $this->assertCount(2, array_unique($tokens));
        foreach (Prospect::all() as $prospect) {
            $this->assertMatchesRegularExpression('/\A[a-f0-9]{48}\z/', $prospect->public_token);
            $this->assertSame('https://dalytrac.com/?utm_source=linkedin&utm_medium=message_prive&ref='.$prospect->public_token, $prospect->linkedin_url);
            $this->assertSame(ProspectStatus::Nouveau, $prospect->status);
        }
    }

    public function test_all_contact_and_utm_fields_are_imported_with_manual_status_history_only(): void
    {
        $file = $this->workbook([
            ['name', 'email', 'phone', 'company', 'country', 'source', 'utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term', 'status', 'linked_user_id', 'linked_entreprise_id', 'email_normalized'],
            ['Ahmed Test', ' Ahmed.Name+CRM@Example.test ', '+3312345', 'Atelier Test', 'France', 'Salon', 'linkedin', 'social', 'launch', 'post', 'crm', 'engage', 999, 888, 'fake'],
        ]);
        $this->upload($file)->assertRedirect(route('manager.prospects.index', ['modal' => 'import']));
        $prospect = Prospect::sole();
        $this->assertSame('ahmed.name+crm@example.test', $prospect->email_normalized);
        $this->assertSame('Ahmed.Name+CRM@Example.test', $prospect->email);
        $this->assertSame('Atelier Test', $prospect->company_name);
        $this->assertSame('+3312345', $prospect->phone);
        $this->assertSame('France', $prospect->country);
        $this->assertSame('Salon', $prospect->source);
        foreach (['utm_source' => 'linkedin', 'utm_medium' => 'social', 'utm_campaign' => 'launch', 'utm_content' => 'post', 'utm_term' => 'crm'] as $field => $value) {
            $this->assertSame($value, $prospect->$field);
        }
        $this->assertSame(ProspectStatus::Engage, $prospect->status);
        $this->assertNull($prospect->linked_user_id);
        $this->assertNull($prospect->linked_entreprise_id);
        $this->assertNull($prospect->trial_started_at);
        $this->assertSame($this->manager->id, $prospect->owner_manager_id);
        $this->assertSame('manual', $prospect->statusHistories()->sole()->source);
        $this->assertSame($this->manager->id, $prospect->statusHistories()->sole()->manager_id);
        $this->assertSame(0, DB::table('users')->count());
        $this->assertSame(0, DB::table('entreprises')->count());
        $this->assertSame(0, DB::table('crm_lifecycle_events')->count());
        Http::assertNothingSent();
        $this->assertSame(1, $this->importResult()['created']);
    }

    public function test_mixed_rows_have_disjoint_counts_and_clear_validation_messages(): void
    {
        Prospect::create(['name' => 'Existing', 'email' => 'existing@example.test']);
        $archived = Prospect::create(['name' => 'Archived', 'email' => 'archived@example.test']);
        $archived->delete();
        $file = $this->workbook([
            ['name', 'email', 'phone', 'status'], ['Fresh', 'fresh@example.test', '00123', null],
            ['Repeat', ' FRESH@EXAMPLE.test ', null, null], ['Existing', 'EXISTING@example.test', null, null],
            ['Archived', 'archived@example.test', null, null], ['Bad email', 'bad-email', null, null],
            ['', 'missing-name@example.test', null, null], [null, null, null, null], ['Other', 'other@example.test', null, 'unknown'],
        ]);
        $this->upload($file)->assertRedirect();
        $result = $this->importResult();
        $this->assertSame(['total' => 8, 'created' => 2, 'duplicated' => 3, 'invalid' => 2, 'skipped' => 1], array_intersect_key($result, array_flip(['total', 'created', 'duplicated', 'invalid', 'skipped'])));
        $this->assertSame(3, Prospect::count()); // Existing active + two created.
        $this->assertSame('00123', Prospect::where('email', 'fresh@example.test')->sole()->phone);
        $this->get(route('manager.prospects.index', ['modal' => 'import']))->assertOk()->assertSee('Doublons ignorés')->assertSee('adresse email valide')->assertSee('obligatoire')->assertSee('Statut non reconnu');
    }

    public function test_only_current_statuses_are_stored_and_missing_or_invalid_values_default_to_new(): void
    {
        $rows = [['name', 'email', 'status']];
        foreach ([...array_column(ProspectStatus::cases(), 'value'), '', 'unsupported'] as $i => $status) {
            $rows[] = ['Status '.$i, 'status'.$i.'@example.test', $status];
        }
        $this->upload($this->workbook($rows))->assertRedirect();
        $this->assertSame(8, Prospect::count());
        $this->assertSame(3, Prospect::where('status', 'nouveau')->count());
        $this->assertSame(5, DB::table('prospect_status_history')->count());
        $this->assertSame(1, Prospect::where('status', 'perdu')->count()); // Explicit Manager import choice.
    }

    public function test_repeat_upload_skips_existing_emails_without_updating_prospects(): void
    {
        $file = $this->workbook([['name', 'email', 'status'], ['Original', 'repeat@example.test', 'contacte']]);
        $this->upload($file)->assertRedirect();
        $this->upload($file)->assertRedirect();
        $this->assertSame(1, Prospect::count());
        $this->assertSame(1, DB::table('prospect_status_history')->count());
        $this->assertSame(0, $this->importResult()['created']);
        $this->assertSame(1, $this->importResult()['duplicated']);
    }

    public function test_split_names_aliases_and_text_phone_values_work(): void
    {
        $this->upload($this->workbook([['Prénom', 'Nom', 'Email', 'Téléphone', 'Société', 'Pays'], ['Alice', 'Dupont', 'alice@example.test', '001234', 'Studio', 'Belgique']]))->assertRedirect();
        $prospect = Prospect::sole();
        $this->assertSame('Alice Dupont', $prospect->name);
        $this->assertSame('001234', $prospect->phone);
        $this->assertSame('Studio', $prospect->company_name);
    }

    public function test_rows_without_email_are_supported_and_the_limitation_is_reported(): void
    {
        $this->upload($this->workbook([['name'], ['Contact sans email']]))->assertRedirect();
        $this->assertNull(Prospect::sole()->email_normalized);
        $this->assertSame(1, $this->importResult()['created']);
        $this->assertStringContainsString('Sans email', $this->importResult()['rows'][0]['messages'][0]);
    }

    public function test_invalid_first_row_does_not_block_a_later_valid_row_with_the_same_email(): void
    {
        $this->upload($this->workbook([['name', 'email'], ['', 'same@example.test'], ['Valid', 'same@example.test']]))->assertRedirect();
        $this->assertSame('Valid', Prospect::sole()->name);
        $this->assertSame(1, $this->importResult()['invalid']);
        $this->assertSame(0, $this->importResult()['duplicated']);
    }

    public function test_formulas_and_excel_errors_are_rejected_without_evaluation(): void
    {
        $file = $this->workbook([['name', 'email'], ['Formula', 'formula@example.test'], ['Error', 'error@example.test']], function ($book) {
            $book->getActiveSheet()->setCellValue('A2', '=1+1');
            $book->getActiveSheet()->setCellValueExplicit('A3', '#REF!', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_ERROR);
        });
        $this->upload($file)->assertRedirect();
        $this->assertSame(0, Prospect::count());
        $this->assertSame(2, $this->importResult()['invalid']);
    }

    public function test_missing_or_duplicate_headers_reject_entire_workbook(): void
    {
        foreach ([[['email'], ['only@example.test']], [['name', 'email', 'Email'], ['Duplicate', 'a@example.test', 'b@example.test']]] as $rows) {
            $this->upload($this->workbook($rows))->assertSessionHasErrors('file');
        }
        $this->assertSame(0, Prospect::count());
    }

    public function test_missing_wrong_type_and_oversized_uploads_are_rejected(): void
    {
        $this->actingAs($this->manager, 'manager');
        $this->postJson(route('manager.prospects.import.store'))->assertUnprocessable();
        $this->upload(UploadedFile::fake()->create('data.xls', 1, 'application/vnd.ms-excel'))->assertSessionHasErrors('file');
        $this->upload(UploadedFile::fake()->create('data.xlsx', 5121, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'))->assertSessionHasErrors('file');
        $this->assertSame(0, Prospect::count());
    }

    public function test_row_limit_rejects_whole_file_before_any_write(): void
    {
        $rows = [['name']];
        for ($i = 0; $i < 1001; $i++) {
            $rows[] = ['Contact '.$i];
        }
        $this->upload($this->workbook($rows))->assertSessionHasErrors('file');
        $this->assertSame(0, Prospect::count());
    }

    public function test_only_first_worksheet_is_imported(): void
    {
        $file = $this->workbook([['name', 'email'], ['First', 'first@example.test']], function ($book) {
            $book->createSheet()->setTitle('Ignored')->fromArray([['name', 'email'], ['Second', 'second@example.test']]);
        });
        $this->upload($file)->assertRedirect();
        $this->assertSame('First', Prospect::sole()->name);
    }

    public function test_unexpected_database_failure_rolls_back_every_created_row(): void
    {
        $real = app(ProspectStatusService::class);
        $this->mock(ProspectStatusService::class)->shouldReceive('transition')->twice()->andReturnUsing(function ($prospect, $status, $source = 'manual', $manager = null, $note = null) use ($real) {
            if ($prospect->name === 'Fail') {
                throw new RuntimeException('Simulated import failure');
            }

            return $real->transition($prospect, $status, $source, $manager, $note);
        });
        $this->upload($this->workbook([['name', 'email'], ['First', 'first@example.test'], ['Fail', 'fail@example.test']]))->assertSessionHasErrors('file');
        $this->assertSame(0, Prospect::count());
        $this->assertSame(0, DB::table('prospect_status_history')->count());
    }

    public function test_report_is_private_to_manager_and_rendered_values_are_escaped(): void
    {
        $this->upload($this->workbook([['name', 'email'], ['<script>alert(1)</script>', 'safe@example.test']]))->assertRedirect();
        $report = session('prospect_import_report');
        $this->get(route('manager.prospects.index', ['modal' => 'import']))->assertOk()->assertSee('&lt;script&gt;', false)->assertDontSee('<script>alert(1)</script>', false);
        $other = Manager::create(['name' => 'Other Manager', 'email' => 'other@example.test']);
        $this->actingAs($other, 'manager')->withSession(['prospect_import_report' => $report])->get(route('manager.prospects.index', ['modal' => 'import']))->assertOk()->assertViewHas('result', null)->assertDontSee('Résultat du dernier import');
    }
}
