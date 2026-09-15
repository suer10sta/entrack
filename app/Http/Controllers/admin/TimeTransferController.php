<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Employees;
use App\Models\Project;
use App\Services\TimeTransferService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TimeTransferController extends Controller
{
    public function __construct(private TimeTransferService $timeTransferService)
    {
    }

    public function exportForm(Project $project)
    {
        $this->authorizeProject($project);

        return view('admin.projects.time-transfer.export', compact('project'));
    }

    public function export(Request $request, Project $project)
    {
        $this->authorizeProject($project);

        $validated = $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $startDate = !empty($validated['start_date']) ? Carbon::parse($validated['start_date']) : null;
        $endDate = !empty($validated['end_date']) ? Carbon::parse($validated['end_date']) : null;

        return $this->downloadExport($project, $startDate, $endDate);
    }

    public function exportFromPointage(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|integer|exists:projects,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $project = Project::findOrFail($validated['project_id']);
        $this->authorizeProject($project);

        $startDate = !empty($validated['start_date']) ? Carbon::parse($validated['start_date']) : null;
        $endDate = !empty($validated['end_date']) ? Carbon::parse($validated['end_date']) : null;

        $response = $this->downloadExport($project, $startDate, $endDate);

        if ($response instanceof \Illuminate\Http\RedirectResponse) {
            return $response->withInput();
        }

        return $response;
    }

    private function downloadExport(Project $project, ?Carbon $startDate, ?Carbon $endDate)
    {
        $rows = $this->timeTransferService->exportProjectTimers($project, $startDate, $endDate);

        if (empty($rows)) {
            return back()->with('error', 'Aucune heure à exporter pour cette période.');
        }

        $csv = $this->timeTransferService->rowsToCsv($rows);
        $filename = 'transfert-' . \Str::slug($project->name) . '-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($csv) {
            echo $csv;
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function importForm(Project $project)
    {
        $this->authorizeProject($project);

        $employees = Employees::where('entreprise_id', Auth::user()->entreprise_id)
            ->where('archived', 0)
            ->orderBy('name')
            ->get();

        $importEmployee = $employees->first(function (Employees $employee) {
            return mb_strtolower(trim((string) $employee->name)) === mb_strtolower(TimeTransferService::IMPORT_EMPLOYEE_NAME);
        });

        return view('admin.projects.time-transfer.import', compact('project', 'employees', 'importEmployee'));
    }

    public function preview(Request $request, Project $project)
    {
        $this->authorizeProject($project);

        $validated = $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:5120',
            'employee_mode' => 'required|in:existing,create',
            'employee_id' => 'nullable|integer|exists:employees,id',
        ]);

        if ($validated['employee_mode'] === 'existing' && empty($validated['employee_id'])) {
            return back()->withInput()->with('error', 'Veuillez sélectionner un employé.');
        }

        try {
            $content = file_get_contents($validated['file']->getRealPath());
            $rows = $this->timeTransferService->parseCsv($content);
            $preview = $this->timeTransferService->previewImport($project, $rows);
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        if ($validated['employee_mode'] === 'existing') {
            $employee = Employees::where('id', $validated['employee_id'])
                ->where('entreprise_id', $project->entreprise_id)
                ->where('archived', 0)
                ->firstOrFail();
            $employeeId = $employee->id;
        } else {
            $employee = (object) ['name' => TimeTransferService::IMPORT_EMPLOYEE_NAME];
            $employeeId = null;
        }

        $sessionKey = 'time_transfer_import_' . $project->id;
        session([
            $sessionKey => [
                'rows' => $rows,
                'employee_id' => $employeeId,
                'employee_mode' => $validated['employee_mode'],
            ],
        ]);

        return view('admin.projects.time-transfer.preview', compact('project', 'preview', 'employee'));
    }

    public function confirm(Project $project)
    {
        $this->authorizeProject($project);

        $sessionKey = 'time_transfer_import_' . $project->id;
        $payload = session($sessionKey);

        if (!$payload || empty($payload['rows'])) {
            return redirect()
                ->route('admin.projects.time-transfer.import', $project)
                ->with('error', 'Session d\'import expirée. Veuillez recharger le fichier.');
        }

        if (($payload['employee_mode'] ?? 'existing') === 'create' || empty($payload['employee_id'])) {
            $employee = $this->timeTransferService->findOrCreateImportEmployee($project->entreprise_id);
        } else {
            $employee = Employees::where('id', $payload['employee_id'])
                ->where('entreprise_id', $project->entreprise_id)
                ->where('archived', 0)
                ->firstOrFail();
        }

        $result = $this->timeTransferService->import(
            $project,
            $employee,
            $payload['rows'],
            Auth::id()
        );

        session()->forget($sessionKey);

        return redirect()
            ->route('admin.projects.show', $project)
            ->with('message', sprintf(
                'Import terminé : %d lignes importées, %d tâche(s) créée(s), %d enregistrement(s) de temps ajouté(s).',
                count($payload['rows']),
                $result['created_tasks'],
                $result['created_timers']
            ));
    }

    private function authorizeProject(Project $project): void
    {
        if ((int) $project->entreprise_id !== (int) Auth::user()->entreprise_id) {
            abort(403);
        }
    }
}
