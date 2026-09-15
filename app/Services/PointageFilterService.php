<?php

namespace App\Services;

use App\Models\Client;
use App\Models\employees_tasks;
use App\Models\Employees;
use App\Models\invoices_history;
use App\Models\Project;
use App\Models\Tasks;
use App\Models\tasks_timer;
use App\Models\Teams;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class PointageFilterService
{
    public function parseDates(?string $startDate, ?string $endDate): array
    {
        $start = filled($startDate) ? Carbon::parse($startDate) : null;
        $end = filled($endDate) ? Carbon::parse($endDate) : null;

        return [$start, $end];
    }

    public function applyDateRange(Builder $query, ?Carbon $startDate, ?Carbon $endDate, bool $finishedOnly = false): Builder
    {
        if ($startDate && $endDate) {
            $query->where('start_date', '>=', $startDate->copy()->startOfDay())
                ->where('start_date', '<=', $endDate->copy()->endOfDay());
        } elseif ($startDate) {
            $query->whereDate('start_date', $startDate->toDateString());
        } else {
            $query->whereDate('start_date', Carbon::today()->toDateString());
        }

        if ($finishedOnly) {
            $query->whereNotNull('end_date')->where('duration', '>', 0);
        }

        return $query;
    }

    public function buildTimersQuery(int $entrepriseId, array $filters): Builder
    {
        [$startDate, $endDate] = $this->parseDates(
            $filters['start_date'] ?? null,
            $filters['end_date'] ?? null
        );

        $query = tasks_timer::query()->where('entreprise_id', $entrepriseId);

        $this->applyDateRange($query, $startDate, $endDate, (bool) ($filters['finished_only'] ?? false));

        if (!empty($filters['client_id'])) {
            $projectIds = Project::where('client_id', $filters['client_id'])->pluck('id');
            $query->whereIn('project_id', $projectIds);
        }

        if (!empty($filters['project_type'])) {
            if ($filters['project_type'] === 'regie') {
                $projectIds = Project::where('regie', 1)->pluck('id');
                $query->whereIn('project_id', $projectIds);
            } elseif ($filters['project_type'] === 'devis') {
                $projectIds = Project::where('AcceptedDevisProject', 1)->pluck('id');
                $query->whereIn('project_id', $projectIds);
            }
        }

        if (!empty($filters['employee_id'])) {
            $query->where('employee_id', $filters['employee_id']);
        }

        if (!empty($filters['project_id'])) {
            $query->where('project_id', $filters['project_id']);
        }

        $taskIds = $this->normalizeTaskIds($filters['task_ids'] ?? []);
        if (!empty($taskIds)) {
            $query->whereIn('task_id', $taskIds);
        }

        return $query;
    }

    public function getGroupedTimers(int $entrepriseId, array $filters)
    {
        return $this->buildTimersQuery($entrepriseId, $filters)
            ->orderBy('start_date', 'asc')
            ->get()
            ->groupBy(fn ($timer) => Carbon::parse($timer->start_date)->format('l - Y-m-d'));
    }

    public function getProjectsWithTasks(int $entrepriseId, array $filters): array
    {
        $filters['finished_only'] = true;

        return $this->buildTimersQuery($entrepriseId, $filters)
            ->whereNotNull('project_id')
            ->distinct()
            ->pluck('project_id')
            ->toArray();
    }

    public function getPeriodInvoices(int $entrepriseId, Carbon $startDate, Carbon $endDate): array
    {
        return invoices_history::where('entreprise_id', $entrepriseId)
            ->where('type', 'Period')
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate->toDateString(), $endDate->toDateString()])
                    ->orWhereBetween('end_date', [$startDate->toDateString(), $endDate->toDateString()])
                    ->orWhere(function ($q) use ($startDate, $endDate) {
                        $q->where('start_date', '<=', $startDate->toDateString())
                            ->where('end_date', '>=', $endDate->toDateString());
                    });
            })
            ->pluck('project_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->toArray();
    }

    public function getProjectsWithExactInvoices(int $entrepriseId, Carbon $startDate, Carbon $endDate): array
    {
        return invoices_history::where('entreprise_id', $entrepriseId)
            ->where('type', 'Period')
            ->whereDate('start_date', $startDate->toDateString())
            ->whereDate('end_date', $endDate->toDateString())
            ->pluck('project_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->toArray();
    }

    public function getCommonPageData(int $entrepriseId, int $userId, ?int $projectId = null): array
    {
        $projects = Project::where('entreprise_id', $entrepriseId)
            ->where(function ($query) {
                $query->whereNull('AcceptedDevisProject')
                    ->orWhere('AcceptedDevisProject', '=', 1);
            })
            ->where('archived', 0)
            ->whereNull('parent_id')
            ->select('id', 'name', 'tva')
            ->orderBy('name')
            ->get();

        $employees = Employees::where('entreprise_id', $entrepriseId)
            ->where('archived', 0)
            ->orderBy('name')
            ->get();

        $teams = Teams::where('entreprise_id', $entrepriseId)->get();

        $allTasksQuery = Tasks::where('entreprise_id', $entrepriseId);
        if ($projectId) {
            $allTasksQuery->where('project_id', $projectId);
        }
        $allTasks = $allTasksQuery->orderBy('title')->get();

        $tasks = Tasks::where('user_id', $userId)->get();

        $employees_tasks = employees_tasks::where('entreprise_id', $entrepriseId)->get();

        $clients = Client::where('entreprise_id', $entrepriseId)
            ->where(function ($query) {
                $query->whereNull('AcceptedDevisClient')
                    ->orWhere('AcceptedDevisClient', '=', 1);
            })
            ->whereIn('id', function ($query) use ($entrepriseId) {
                $query->select('client_id')
                    ->from('projects')
                    ->where('entreprise_id', $entrepriseId)
                    ->where(function ($q) {
                        $q->whereNull('AcceptedDevisProject')
                            ->orWhere('AcceptedDevisProject', '=', 1);
                    })
                    ->where('archived', 0);
            })
            ->orderBy('name')
            ->get();

        return compact('projects', 'employees', 'teams', 'allTasks', 'tasks', 'employees_tasks', 'clients');
    }

    public function filtersFromRequest(Request $request): array
    {
        return [
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'client_id' => $request->input('client') ?: null,
            'employee_id' => $request->input('employee') ?: null,
            'project_id' => $request->input('project') ?: null,
            'project_type' => $request->input('project_type') ?: null,
            'task_ids' => (array) $request->query('task', []),
        ];
    }

    public function resolveDateRangeForInvoices(array $filters): array
    {
        [$startDate, $endDate] = $this->parseDates(
            $filters['start_date'] ?? null,
            $filters['end_date'] ?? null
        );

        if (!$startDate) {
            $startDate = Carbon::today();
        }

        if (!$endDate) {
            $endDate = $startDate->copy();
        }

        return [$startDate, $endDate];
    }

    public function getPointageViewData(int $entrepriseId, int $userId, array $filters): array
    {
        $projectId = !empty($filters['project_id']) ? (int) $filters['project_id'] : null;

        $data = $this->getCommonPageData($entrepriseId, $userId, $projectId);

        [$invoiceStart, $invoiceEnd] = $this->resolveDateRangeForInvoices($filters);

        $data['periodInvoices'] = $this->getPeriodInvoices($entrepriseId, $invoiceStart, $invoiceEnd);
        $data['projectsWithExactInvoices'] = $this->getProjectsWithExactInvoices($entrepriseId, $invoiceStart, $invoiceEnd);
        $data['tasks_timer'] = $this->getGroupedTimers($entrepriseId, $filters);
        $data['projectsWithTasks'] = $this->getProjectsWithTasks($entrepriseId, $filters);

        return $data;
    }

    private function normalizeTaskIds(array $taskIds): array
    {
        $flatTaskIds = [];
        foreach ($taskIds as $val) {
            if (is_array($val)) {
                foreach ($val as $v) {
                    $flatTaskIds[] = $v;
                }
            } else {
                $flatTaskIds[] = $val;
            }
        }

        return array_values(array_filter($flatTaskIds, fn ($v) => $v !== '' && $v !== null && $v !== 'all'));
    }
}
