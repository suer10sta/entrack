<?php

namespace App\Services;

use App\Models\Employees;
use App\Models\employees_tasks;
use App\Models\Project;
use App\Models\Tasks;
use App\Models\tasks_timer;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TimeTransferService
{
    public const IMPORT_EMPLOYEE_NAME = 'Import / Prestataire externe';

    public const CSV_HEADERS = ['start_date', 'end_date', 'task_name', 'duration_minutes', 'comment'];

    public function __construct(private PointageFilterService $pointageFilterService)
    {
    }

    public function exportProjectTimers(Project $project, ?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $query = tasks_timer::query()
            ->where('project_id', $project->id)
            ->where('entreprise_id', $project->entreprise_id);

        $this->pointageFilterService->applyDateRange($query, $startDate, $endDate, finishedOnly: true);

        $rows = [];

        foreach ($query->orderBy('start_date')->get() as $timer) {
            $rows[] = [
                'start_date' => Carbon::parse($timer->start_date)->format('Y-m-d H:i:s'),
                'end_date' => Carbon::parse($timer->end_date)->format('Y-m-d H:i:s'),
                'task_name' => trim((string) $timer->task_name),
                'duration_minutes' => (int) $timer->duration,
                'comment' => filled($timer->comment) ? trim((string) $timer->comment) : null,
            ];
        }

        return $rows;
    }

    public function rowsToCsv(array $rows): string
    {
        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, self::CSV_HEADERS);

        foreach ($rows as $row) {
            fputcsv($handle, [
                $row['start_date'],
                $row['end_date'],
                $row['task_name'],
                $row['duration_minutes'],
                $row['comment'] ?? '',
            ]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle) ?: '';
        fclose($handle);

        return "\xEF\xBB\xBF" . $csv;
    }

    public function parseCsv(string $content): array
    {
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content) ?? $content;
        $lines = preg_split('/\R/', trim($content));

        if (!$lines || count($lines) < 2) {
            throw new \InvalidArgumentException('Le fichier est vide ou ne contient pas de données.');
        }

        $delimiter = $this->detectDelimiter($lines[0]);
        $headers = array_map(fn ($h) => strtolower(trim($h)), str_getcsv($lines[0], $delimiter));
        $this->assertValidHeaders($headers);

        $headerMap = $this->buildHeaderMap($headers);
        $rows = [];
        $errors = [];

        for ($i = 1; $i < count($lines); $i++) {
            $line = trim($lines[$i]);
            if ($line === '') {
                continue;
            }

            $cells = str_getcsv($line, $delimiter);
            $lineNumber = $i + 1;

            try {
                $rows[] = $this->parseRow($cells, $headerMap, $lineNumber);
            } catch (\InvalidArgumentException $e) {
                $errors[] = $e->getMessage();
            }
        }

        if (!empty($errors)) {
            throw new \InvalidArgumentException(implode("\n", $errors));
        }

        if (empty($rows)) {
            throw new \InvalidArgumentException('Aucune ligne valide trouvée dans le fichier.');
        }

        return $rows;
    }

    public function previewImport(Project $project, array $rows): array
    {
        $existingTasks = Tasks::where('project_id', $project->id)
            ->where('entreprise_id', $project->entreprise_id)
            ->get();

        $existingByName = [];
        foreach ($existingTasks as $task) {
            $existingByName[mb_strtolower(trim($task->title))] = $task;
        }

        $tasksToCreate = [];
        $totalMinutes = 0;
        $previewRows = [];

        foreach ($rows as $row) {
            $normalizedName = mb_strtolower($row['task_name']);
            $exists = isset($existingByName[$normalizedName]);
            $willCreate = !$exists;

            if ($willCreate && !isset($tasksToCreate[$normalizedName])) {
                $tasksToCreate[$normalizedName] = $row['task_name'];
            }

            $totalMinutes += $row['duration_minutes'];
            $previewRows[] = [
                'start_date' => $row['start_date'],
                'end_date' => $row['end_date'],
                'task_name' => $row['task_name'],
                'duration_minutes' => $row['duration_minutes'],
                'comment' => $row['comment'] ?? '',
                'action' => $willCreate ? 'create' : 'reuse',
            ];
        }

        return [
            'line_count' => count($rows),
            'total_minutes' => $totalMinutes,
            'total_hours' => round($totalMinutes / 60, 2),
            'tasks_to_create' => count($tasksToCreate),
            'rows' => $previewRows,
        ];
    }

    public function import(Project $project, Employees $employee, array $rows, int $userId): array
    {
        $createdTasks = 0;
        $createdTimers = 0;

        DB::transaction(function () use ($project, $employee, $rows, $userId, &$createdTasks, &$createdTimers) {
            $employeeDisplayName = trim((string) ($employee->name ?? self::IMPORT_EMPLOYEE_NAME));

            foreach ($rows as $row) {
                $task = $this->findOrCreateTask($project, $row['task_name'], $userId, $createdTasks);

                $employeeTask = employees_tasks::firstOrCreate(
                    [
                        'task_id' => $task->id,
                        'employee_id' => $employee->id,
                    ],
                    [
                        'task_name' => $task->title,
                        'employee_name' => $employeeDisplayName,
                        'entreprise_id' => $project->entreprise_id,
                    ]
                );

                $start = Carbon::parse($row['start_date']);
                $end = Carbon::parse($row['end_date']);
                $durationMinutes = (int) $row['duration_minutes'];
                $employeeCostPerHour = (float) ($employee->cost_per_hour ?? 0);

                $timer = new tasks_timer();
                $timer->task_id = $task->id;
                $timer->task_name = $task->title;
                $timer->task_employee_id = $employeeTask->id;
                $timer->project_id = $project->id;
                $timer->employee_id = $employee->id;
                $timer->start_date = $start->format('Y-m-d H:i:s');
                $timer->end_date = $end->format('Y-m-d H:i:s');
                $timer->duration = $durationMinutes;
                $timer->cost = $employeeCostPerHour * ($durationMinutes / 60);
                $timer->entreprise_id = $project->entreprise_id;
                $timer->paused = 0;
                $timer->comment = filled($row['comment'] ?? null) ? $row['comment'] : null;
                $timer->save();

                $createdTimers++;
            }
        });

        return [
            'created_tasks' => $createdTasks,
            'created_timers' => $createdTimers,
        ];
    }

    public function findOrCreateImportEmployee(int $entrepriseId): Employees
    {
        $existing = Employees::where('entreprise_id', $entrepriseId)
            ->where('archived', 0)
            ->whereRaw('LOWER(name) = ?', [mb_strtolower(self::IMPORT_EMPLOYEE_NAME)])
            ->first();

        if ($existing) {
            return $existing;
        }

        return Employees::create([
            'name' => self::IMPORT_EMPLOYEE_NAME,
            'email' => 'import-prestataire-' . $entrepriseId . '@internal.local',
            'password' => bcrypt(Str::random(32)),
            'phoneNumber' => '',
            'salary' => 0,
            'cost_per_hour' => 0,
            'workHours_limit' => 0,
            'entreprise_id' => $entrepriseId,
            'user_id' => 0,
            'archived' => 0,
        ]);
    }

    private function findOrCreateTask(Project $project, string $taskName, int $userId, int &$createdTasks): Tasks
    {
        $normalizedName = mb_strtolower(trim($taskName));

        $task = Tasks::where('project_id', $project->id)
            ->where('entreprise_id', $project->entreprise_id)
            ->whereRaw('LOWER(title) = ?', [$normalizedName])
            ->first();

        if ($task) {
            return $task;
        }

        $task = Tasks::create([
            'title' => trim($taskName),
            'description' => 'Créée automatiquement lors de l\'import transfert',
            'project_name' => $project->name,
            'project_id' => $project->id,
            'entreprise_id' => $project->entreprise_id,
            'user_id' => $userId,
            'consumed_hours' => 0,
        ]);

        $createdTasks++;

        return $task;
    }

    private function detectDelimiter(string $headerLine): string
    {
        $semicolons = substr_count($headerLine, ';');
        $commas = substr_count($headerLine, ',');

        return $semicolons > $commas ? ';' : ',';
    }

    private function assertValidHeaders(array $headers): void
    {
        $hasDateRange = in_array('start_date', $headers, true) && in_array('end_date', $headers, true);
        $hasLegacyDate = in_array('date', $headers, true);

        if (!$hasDateRange && !$hasLegacyDate) {
            throw new \InvalidArgumentException(
                'Colonnes obligatoires manquantes : start_date et end_date (ou date pour l\'ancien format). Attendu : start_date, end_date, task_name, duration_minutes, comment (optionnel).'
            );
        }

        $missing = array_diff(['task_name', 'duration_minutes'], $headers);

        if (!empty($missing)) {
            throw new \InvalidArgumentException(
                'Colonnes obligatoires manquantes : ' . implode(', ', $missing) . '. Attendu : start_date, end_date, task_name, duration_minutes, comment (optionnel).'
            );
        }
    }

    private function buildHeaderMap(array $headers): array
    {
        $allowed = array_merge(self::CSV_HEADERS, ['date']);
        $map = [];
        foreach ($headers as $index => $header) {
            if (in_array($header, $allowed, true)) {
                $map[$header] = $index;
            }
        }

        return $map;
    }

    private function parseRow(array $cells, array $headerMap, int $lineNumber): array
    {
        $taskName = trim((string) ($cells[$headerMap['task_name']] ?? ''));
        $durationRaw = trim((string) ($cells[$headerMap['duration_minutes']] ?? ''));
        $comment = isset($headerMap['comment']) ? trim((string) ($cells[$headerMap['comment']] ?? '')) : '';

        if ($taskName === '' || $durationRaw === '') {
            throw new \InvalidArgumentException("Ligne {$lineNumber} : task_name et duration_minutes sont obligatoires.");
        }

        if (!is_numeric($durationRaw)) {
            throw new \InvalidArgumentException("Ligne {$lineNumber} : duration_minutes doit être un nombre entier.");
        }

        $durationMinutes = (int) $durationRaw;
        if ($durationMinutes <= 0) {
            throw new \InvalidArgumentException("Ligne {$lineNumber} : duration_minutes doit être supérieur à 0.");
        }

        if (isset($headerMap['start_date'], $headerMap['end_date'])) {
            $startRaw = trim((string) ($cells[$headerMap['start_date']] ?? ''));
            $endRaw = trim((string) ($cells[$headerMap['end_date']] ?? ''));

            if ($startRaw === '' || $endRaw === '') {
                throw new \InvalidArgumentException("Ligne {$lineNumber} : start_date et end_date sont obligatoires.");
            }

            try {
                $start = Carbon::parse($startRaw);
                $end = Carbon::parse($endRaw);
            } catch (\Throwable $e) {
                throw new \InvalidArgumentException("Ligne {$lineNumber} : start_date ou end_date invalide.");
            }

            if ($end->lte($start)) {
                throw new \InvalidArgumentException("Ligne {$lineNumber} : end_date doit être postérieure à start_date.");
            }
        } else {
            $date = trim((string) ($cells[$headerMap['date']] ?? ''));
            if ($date === '') {
                throw new \InvalidArgumentException("Ligne {$lineNumber} : date est obligatoire.");
            }

            try {
                $start = Carbon::parse($date);
            } catch (\Throwable $e) {
                throw new \InvalidArgumentException("Ligne {$lineNumber} : date invalide ({$date}).");
            }

            $end = $start->copy()->addMinutes($durationMinutes);
        }

        return [
            'start_date' => $start->format('Y-m-d H:i:s'),
            'end_date' => $end->format('Y-m-d H:i:s'),
            'task_name' => $taskName,
            'duration_minutes' => $durationMinutes,
            'comment' => $comment !== '' ? $comment : null,
        ];
    }
}
