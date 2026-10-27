<?php

namespace App\Services;

use App\Enums\ProspectStatus;
use App\Models\Manager;
use App\Models\Prospect;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use Throwable;
use ZipArchive;

class ProspectExcelImport
{
    public const MAX_ROWS = 1000;

    private const MAX_COLUMNS = 32;

    public const FIELDS = ['name', 'email', 'phone', 'company_name', 'country', 'source',
        'utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term', 'status'];

    public function __construct(private ProspectStatusService $statuses) {}

    public function import(UploadedFile $file, Manager $manager): array
    {
        $rows = $this->read($file);

        return DB::transaction(function () use ($rows, $manager) {
            if (DB::connection()->getDriverName() === 'pgsql') {
                DB::statement("SET LOCAL TIME ZONE 'UTC'");
                // Serialize concurrent imports without imposing global email uniqueness.
                DB::select('SELECT pg_advisory_xact_lock(hashtextextended(?, 0))', ['crm.prospect_excel_import']);
            }
            $result = ['total' => count($rows), 'created' => 0, 'duplicated' => 0, 'invalid' => 0, 'skipped' => 0,
                'rows' => [], 'completed_at' => now('UTC')->toISOString()];
            $seen = [];
            foreach ($rows as $row) {
                $data = $row['data'];
                $entry = ['line' => $row['line'], 'name' => isset($data['name']) ? Str::limit($data['name'], 255) : null,
                    'email' => isset($data['email']) ? Str::limit($data['email'], 255) : null, 'messages' => []];
                if (! $row['errors'] && ! array_filter($data, fn ($value) => $value !== null && $value !== '')) {
                    $result['skipped']++;
                    $result['rows'][] = $entry + ['outcome' => 'skipped', 'reason' => 'Ligne vide ignorée.'];

                    continue;
                }
                $status = ProspectStatus::tryFrom(Str::lower(trim($data['status'] ?? ''))) ?? ProspectStatus::Nouveau;
                if (isset($data['status']) && ! ProspectStatus::tryFrom(Str::lower($data['status']))) {
                    $entry['messages'][] = 'Statut non reconnu : Nouveau a été utilisé.';
                }
                unset($data['status']);
                $rules = ['name' => ['required', 'string', 'max:255'], 'email' => ['nullable', 'email:rfc', 'max:255'],
                    'phone' => ['nullable', 'string', 'max:64'], 'country' => ['nullable', 'string', 'max:100']];
                foreach (['company_name', 'source', 'utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term'] as $field) {
                    $rules[$field] = ['nullable', 'string', 'max:255'];
                }
                $validator = Validator::make($data, $rules, [
                    'required' => 'Le champ :attribute est obligatoire.', 'email' => 'Le champ :attribute doit être une adresse email valide.',
                    'max' => 'Le champ :attribute ne doit pas dépasser :max caractères.',
                ], ['name' => 'nom', 'email' => 'email', 'phone' => 'téléphone', 'country' => 'pays']);
                if ($row['errors'] || $validator->fails()) {
                    $result['invalid']++;
                    $entry['messages'] = array_merge($row['errors'], $validator->errors()->all());
                    $result['rows'][] = $entry + ['outcome' => 'invalid', 'reason' => 'Ligne non importée.'];

                    continue;
                }
                $email = Prospect::normalizeEmail($data['email'] ?? null);
                if ($email && (isset($seen[$email]) || Prospect::withTrashed()->where('email_normalized', $email)->exists())) {
                    $result['duplicated']++;
                    $result['rows'][] = $entry + ['outcome' => 'duplicated', 'reason' => isset($seen[$email])
                        ? 'Email déjà rencontré dans ce fichier.' : 'Email déjà présent dans le CRM (y compris les prospects archivés).'];
                    $seen[$email] = true;

                    continue;
                }
                // Explicit validated fields only: no User/Entreprise, registration event or account linkage.
                $prospect = Prospect::create($validator->validated() + ['owner_manager_id' => $manager->id,
                    'source_captured_at' => now('UTC')]);
                $this->statuses->transition($prospect, $status, manager: $manager, note: 'Statut choisi lors de l’import Excel.');
                if ($email) {
                    $seen[$email] = true;
                } else {
                    $entry['messages'][] = 'Sans email : aucun contrôle de doublon par email possible.';
                }
                $result['created']++;
                $result['rows'][] = $entry + ['outcome' => 'created', 'reason' => 'Prospect créé.', 'prospect_id' => $prospect->id];
            }

            return $result;
        }, 3);
    }

    private function read(UploadedFile $file): array
    {
        $path = $file->getRealPath();
        $zip = new ZipArchive;
        if ($zip->open($path) !== true) {
            $this->fileError('Le fichier doit être un classeur Excel .xlsx valide.');
        }
        try {
            $size = 0;
            if ($zip->numFiles > 2000) {
                $this->fileError('Ce classeur contient trop d’éléments. Utilisez un fichier simplifié.');
            }
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $size += $zip->statIndex($i)['size'];
            }
            if ($size > 50 * 1024 * 1024) {
                $this->fileError('Le contenu décompressé du classeur est trop volumineux.');
            }
        } finally {
            $zip->close();
        }
        $book = null;
        try {
            $reader = new Xlsx;
            $info = $reader->listWorksheetInfo($path)[0] ?? null;
            if (! $info || $info['totalRows'] < 2) {
                $this->fileError('Le classeur doit contenir une ligne d’en-têtes et au moins un prospect.');
            }
            if ($info['totalRows'] > self::MAX_ROWS + 1 || $info['totalColumns'] > self::MAX_COLUMNS) {
                $this->fileError('Limite : 1 000 lignes de données et 32 colonnes dans la première feuille.');
            }
            $reader->setReadDataOnly(true)->setReadEmptyCells(false)->setLoadSheetsOnly([$info['worksheetName']]);
            $reader->setReadFilter(new class implements IReadFilter
            {
                public function readCell(string $columnAddress, int $row, string $worksheetName = ''): bool
                {
                    return $row <= ProspectExcelImport::MAX_ROWS + 1 && Coordinate::columnIndexFromString($columnAddress) <= 32;
                }
            });
            $book = $reader->load($path);
            $sheet = $book->getSheet(0);
            $headers = [];
            foreach (range(1, $info['totalColumns']) as $column) {
                $cell = $sheet->getCell([$column, 1]);
                if ($cell->isFormula()) {
                    $this->fileError('Les en-têtes doivent être du texte, sans formule.');
                }
                $raw = $cell->getValue();
                $header = Str::slug(trim((string) ($raw instanceof RichText ? $raw->getPlainText() : $raw)), '_');
                $header = ['company' => 'company_name', 'societe' => 'company_name', 'nom_complet' => 'name', 'nom' => 'last_name',
                    'telephone' => 'phone', 'pays' => 'country', 'statut' => 'status', 'prenom' => 'first_name',
                    'first_name' => 'first_name', 'last_name' => 'last_name', 'nom_de_famille' => 'last_name'][$header] ?? $header;
                if (! in_array($header, [...self::FIELDS, 'first_name', 'last_name'], true)) {
                    continue;
                }
                if (in_array($header, $headers, true)) {
                    $this->fileError('La colonne « '.$header.' » apparaît plusieurs fois.');
                }
                $headers[$column] = $header;
            }
            if (! in_array('name', $headers, true) && ! in_array('first_name', $headers, true) && ! in_array('last_name', $headers, true)) {
                $this->fileError('Ajoutez une colonne name (nom complet), ou first_name et last_name, sur la première ligne.');
            }
            $rows = [];
            for ($line = 2; $line <= $info['totalRows']; $line++) {
                $data = [];
                $errors = [];
                foreach ($headers as $column => $field) {
                    $cell = $sheet->getCell([$column, $line]);
                    if ($cell->isFormula() || $cell->getDataType() === DataType::TYPE_ERROR) {
                        $errors[] = 'La colonne '.$field.' contient une formule ou une erreur Excel ; remplacez-la par une valeur.';
                        $data[$field] = null;

                        continue;
                    }
                    $value = $cell->getValue();
                    $value = trim((string) ($value instanceof RichText ? $value->getPlainText() : $value));
                    $data[$field] = $value === '' ? null : $value;
                }
                if (($data['name'] ?? null) === null || $data['name'] === '') {
                    $data['name'] = trim(($data['first_name'] ?? '').' '.($data['last_name'] ?? '')) ?: null;
                }
                unset($data['first_name'], $data['last_name']);
                $rows[] = compact('line', 'data', 'errors');
            }

            return $rows;
        } catch (ValidationException $error) {
            throw $error;
        } catch (Throwable $error) {
            $this->fileError('Impossible de lire ce fichier. Utilisez un classeur .xlsx valide et non protégé par mot de passe.');
        } finally {
            $book?->disconnectWorksheets();
        }
    }

    private function fileError(string $message): never
    {
        throw ValidationException::withMessages(['file' => $message]);
    }
}
