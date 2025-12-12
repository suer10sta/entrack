<?php

namespace App\Http\Controllers\admin;

use App\Models\User;
use App\Models\Tasks;
use App\Models\Teams;
use App\Models\Client;
use App\Models\Project;
use App\Models\Employees;
use App\Models\todoliste;
use App\Models\Entreprise;
use App\Models\invoices_history;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\calendars;
use App\Models\Employee;
use App\Models\CalendarEvent;
use Carbon\Carbon;

class adminController extends Controller
{
    public function index()
    {
        $todoPage = request('todoPage', 1);
        $projectPage = request('page', 1);

        $projects = Project::with('client', 'teams')
            ->where('entreprise_id', auth()->user()->entreprise_id)
            ->where(function ($query) {
                $query->whereNull('AcceptedDevisProject')
                    ->orWhere('AcceptedDevisProject', '=', 1);
            })
            ->where('archived', 0)
            ->orderBy('client_id')
            ->orderBy('name')
            ->paginate(6, ['*'], 'page', $projectPage);

        $projectsFilter = Project::where('entreprise_id', auth()->user()->entreprise_id)
            ->where(function ($query) {
                $query->whereNull('AcceptedDevisProject')
                    ->orWhere('AcceptedDevisProject', '=', 1);
            })
            ->where('archived', 0)
            ->orderBy('client_id')
            ->get();

        $tasks = Tasks::where('user_id', Auth::user()->id)->get();
        $teams = Teams::where('entreprise_id', Auth::user()->entreprise_id)->get();
        $clients = Client::where('entreprise_id', auth()->user()->entreprise_id)
            ->where(function ($query) {
                $query->whereNull('AcceptedDevisClient')
                    ->orWhere('AcceptedDevisClient', '=', 1);
            })
            ->orderBy('client_number')
            ->get();

        // Total number of projects for the stats card (all projects for this enterprise)
        $totalProjectsCount = Project::where('entreprise_id', auth()->user()->entreprise_id)->count();

        $todoliste = todoliste::where('user_id', Auth::user()->id)
            ->orderBy('start', 'asc')
            ->paginate(4, ['*'], 'todoPage', $todoPage);

        $invoices = invoices_history::where('entreprise_id', Auth::user()->entreprise_id)
            ->orderBy('number', 'desc')
            ->paginate(5);

        $monthlyRevenue = invoices_history::where('entreprise_id', Auth::user()->entreprise_id)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total_ttc');

        $enterpriseId = Auth::user()->entreprise_id;
        $employees = Employees::where('entreprise_id', $enterpriseId)
            ->where('archived', 0)
            ->orderBy('name', 'asc')
            ->get();

        $entreprises = Entreprise::all();
        $entreprise_infos = Entreprise::where('id', $enterpriseId)->first();

        foreach ($employees as $employee) {
            $user_id = $employee->user_id;
            $user = User::find($user_id);
            $roles = $user->roles;
            $roleNames = $roles->pluck('name')->toArray();
            $permissions = [];
            foreach ($roles as $role) {
                $permissions = array_merge($permissions, $role->permissions->pluck('name')->toArray());
            }
            $employee->roles = $roleNames;
            $employee->permissions = array_unique($permissions);
        }

        $schedules = todoliste::all();
        $dayEvents = calendars::where('entreprise_id', auth()->user()->entreprise_id)
            ->whereDate('start_date', today())
            ->orderBy('start_date')
            ->get();

        // Handle AJAX request for invoice pagination
        if (request()->ajax() && (request()->has('client') || request()->has('project') || request()->has('month'))) {
            try {
                // Filter invoices
                $query = invoices_history::where('entreprise_id', Auth::user()->entreprise_id);

                if (request()->has('client') && request('client') != '') {
                    $query->where('client_id', request('client'));
                }
                if (request()->has('project') && request('project') != '') {
                    $query->where('project_id', request('project'));
                }
                if (request()->has('month') && request('month') != '') {
                    $query->whereMonth('created_at', request('month'));
                }

                $invoices = $query->orderBy('number', 'desc')->paginate(5);

                return response()->json([
                    'success' => true,
                    'html' => view('admin.partials.invoice-table', compact('invoices', 'entreprise_infos'))->render()
                ]);
            } catch (\Exception $e) {
                \Log::error('Error in AJAX pagination: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while loading data: ' . $e->getMessage()
                ], 500);
            }
        }
        return view("admin.index", compact(
            'entreprises',
            'employees',
            'schedules',
            'tasks',
            'teams',
            'projects',
            'clients',
            'todoliste',
            'invoices',
            'entreprise_infos',
            'projectsFilter',
            'dayEvents',
            'monthlyRevenue',
            'totalProjectsCount'
        ));
    }

    public function filterInvoices(Request $request)
    {
        try {
            $query = invoices_history::where('entreprise_id', Auth::user()->entreprise_id);

            // Apply filters
            if ($request->has('client') && $request->client != '') {
                $query->where('client_id', $request->client);
            }
            if ($request->has('project') && $request->project != '') {
                $query->where('project_id', $request->project);
            }
            if ($request->has('month') && $request->month != '') {
                $query->whereMonth('created_at', $request->month);
            }

            // Get paginated results
            $invoices = $query->orderBy('number', 'desc')->paginate(5);
            $entreprise_infos = Entreprise::where('id', auth()->user()->entreprise_id)->first();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'html' => view('admin.partials.invoice-table', compact('invoices', 'entreprise_infos'))->render()
                ]);
            }

            return view('admin.index', compact('invoices', 'entreprise_infos'));
        } catch (\Exception $e) {
            \Log::error('Error in filterInvoices: ' . $e->getMessage());

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while filtering invoices: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'An error occurred while filtering invoices.');
        }
    }


    // Add this method to your DashboardController class

    public function refreshEmployees(Request $request)
    {
        if (!$request->ajax()) {
            abort(404);
        }

        $employees = \App\Models\Employees::where('entreprise_id', Auth::user()->entreprise_id)
            ->where('archived', 0)
            ->get();

        $employeesData = $employees->map(function ($employee) {
            return [
                'id' => $employee->id,
                'currentStatus' => $this->renderCurrentStatus($employee),
                'currentTask' => $this->renderCurrentTask($employee),
                'currentProject' => $this->renderCurrentProject($employee),
                'taskProgress' => $this->renderTaskProgress($employee),
                'duration' => $this->renderDuration($employee),
                'accountStatus' => $this->renderAccountStatus($employee),
                'chrono' => $this->renderChrono($employee),
                'taskHistory' => $this->renderTaskHistory($employee),
            ];
        });

        return response()->json([
            'employees' => $employeesData,
            'success' => true
        ]);
    }

    // ===== HELPER METHODS =====

    private function renderCurrentStatus($employee)
    {
        $employeeUser = \App\Models\User::find($employee->user_id);

        if (!$employeeUser || $employeeUser->is_loggedIn == 0) {
            return '<span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">Off Work</span>';
        }

        $task_timer = \App\Models\tasks_timer::where('employee_id', $employee->id)
            ->whereDate('start_date', today())
            ->whereNull('end_date')
            ->first();

        if ($task_timer) {
            if ($task_timer->paused == 1) {
                return '<span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">On Pause</span>';
            }
            return '<span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">On Work</span>';
        }

        return '<span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">Off Work</span>';
    }

    private function renderCurrentTask($employee)
    {
        $isSuperAdmin = auth()->user()->hasRole('SuperAdmin') || auth()->user()->hasRole('super_admin');

        if ($isSuperAdmin) {
            $currentTasks = \App\Models\tasks_timer::where('employee_id', $employee->id)
                ->whereDate('start_date', today())
                ->whereNull('end_date')
                ->orderBy('start_date', 'desc')
                ->get();
        } else {
            $currentTasks = \App\Models\tasks_timer::where('employee_id', $employee->id)
                ->whereDate('start_date', today())
                ->whereNull('end_date')
                ->latest()
                ->take(1)
                ->get();
        }

        if ($currentTasks->isEmpty()) {
            return '<span class="text-xs text-gray-400">No Task</span>';
        }

        $html = '<div class="' . ($isSuperAdmin && $currentTasks->count() > 1 ? 'space-y-1 divide-y divide-gray-500' : 'space-y-1') . '">';

        foreach ($currentTasks as $currentTask) {
            $route = '#';
            if (auth()->user()->hasRole('Admin') || auth()->user()->hasRole('SuperAdmin') || auth()->user()->hasRole('super_admin')) {
                $route = route('admin.tasks.show', $currentTask->task_id);
            } elseif (auth()->user()->hasRole('Responsable de projet')) {
                $route = route('Responsable_de_projet.tasks.show', $currentTask->task_id);
            }

            $html .= '<a href="' . $route . '" class="text-xs font-semibold text-blue-600 hover:underline block">' . e($currentTask->task_name) . '</a>';
        }

        $html .= '</div>';
        return $html;
    }

    private function renderCurrentProject($employee)
    {
        $isSuperAdmin = auth()->user()->hasRole('SuperAdmin') || auth()->user()->hasRole('super_admin');

        if ($isSuperAdmin) {
            $activeTasks = \App\Models\tasks_timer::where('employee_id', $employee->id)
                ->whereDate('start_date', today())
                ->whereNull('end_date')
                ->orderBy('start_date', 'desc')
                ->get();
        } else {
            $activeTasks = \App\Models\tasks_timer::where('employee_id', $employee->id)
                ->whereDate('start_date', today())
                ->whereNull('end_date')
                ->latest()
                ->take(1)
                ->get();
        }

        $currentProjects = collect();
        foreach ($activeTasks as $task) {
            $project_id = \App\Models\Tasks::where('id', $task->task_id)->pluck('project_id')->first();
            if ($project_id) {
                $project = \App\Models\Project::find($project_id);
                if ($project && !$currentProjects->contains('id', $project->id)) {
                    $currentProjects->push($project);
                }
            }
        }

        if ($currentProjects->isEmpty()) {
            return '<span class="text-xs text-gray-400">No Project</span>';
        }

        $html = '<div class="' . ($isSuperAdmin && $currentProjects->count() > 1 ? 'space-y-1 divide-y divide-gray-300' : 'space-y-1') . '">';

        foreach ($currentProjects as $project) {
            $html .= '<a href="' . route('admin.projects.show', $project->id) . '" class="text-xs font-semibold text-emerald-600 hover:underline block">' . e($project->name) . '</a>';
        }

        $html .= '</div>';
        return $html;
    }

    private function renderTaskProgress($employee)
    {
        $isSuperAdmin = auth()->user()->hasRole('SuperAdmin') || auth()->user()->hasRole('super_admin');

        if ($isSuperAdmin) {
            $activeTasks = \App\Models\tasks_timer::where('employee_id', $employee->id)
                ->whereDate('start_date', today())
                ->whereNull('end_date')
                ->orderBy('start_date', 'desc')
                ->get();
        } else {
            $activeTasks = \App\Models\tasks_timer::where('employee_id', $employee->id)
                ->whereDate('start_date', today())
                ->whereNull('end_date')
                ->latest()
                ->take(1)
                ->get();
        }

        $firstActiveTask = $activeTasks->first();

        if (!$firstActiveTask) {
            return '<p class="text-xs font-medium text-gray-600">No Task</p>';
        }

        $task = \App\Models\Tasks::where('id', $firstActiveTask->task_id)->first();

        if (!$task || $task->estimated_hours === null) {
            return '<p class="text-xs font-medium text-gray-600">No Estimated Hours Inserted</p>';
        }

        $tasktotalduration = \App\Models\tasks_timer::where('task_id', $firstActiveTask->task_id)
            ->where('employee_id', $employee->id)
            ->sum('duration');

        $taskestimatedMinutes = $task->estimated_hours * 60;
        $taskprogressPercentage = $taskestimatedMinutes > 0 ? ($tasktotalduration / $taskestimatedMinutes) * 100 : 0;
        $taskformattedProgressPercentage = number_format($taskprogressPercentage, 2);

        $colorClass = 'bg-emerald-500';
        if ($taskprogressPercentage >= 75) {
            $colorClass = 'bg-red-500';
        } elseif ($taskprogressPercentage >= 50) {
            $colorClass = 'bg-amber-500';
        }

        return '<div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
                <div class="h-2 rounded-full text-[10px] text-white flex items-center justify-center ' . $colorClass . '" style="width: ' . $taskformattedProgressPercentage . '%;">
                </div>
            </div>
            <p class="mt-1 text-xs font-medium text-gray-700">' . $taskformattedProgressPercentage . '%</p>';
    }

    private function renderDuration($employee)
    {
        $isSuperAdmin = auth()->user()->hasRole('SuperAdmin') || auth()->user()->hasRole('super_admin');

        if ($isSuperAdmin) {
            $activeTasks = \App\Models\tasks_timer::where('employee_id', $employee->id)
                ->whereDate('start_date', today())
                ->whereNull('end_date')
                ->orderBy('start_date', 'desc')
                ->get();
        } else {
            $activeTasks = \App\Models\tasks_timer::where('employee_id', $employee->id)
                ->whereDate('start_date', today())
                ->whereNull('end_date')
                ->latest()
                ->take(1)
                ->get();
        }

        if ($activeTasks->isEmpty()) {
            $today = \Carbon\Carbon::today();
            $totalDurationMinutes = \App\Models\tasks_timer::where('employee_id', $employee->id)
                ->whereDate('start_date', $today)
                ->whereNotNull('duration')
                ->sum('duration');
            $h = floor($totalDurationMinutes / 60);
            $m = $totalDurationMinutes % 60;
            return '<span class="text-xs font-medium text-gray-700">Total today: ' . $h . 'h:' . sprintf('%02d', $m) . 'm</span>';
        }

        $now = \Carbon\Carbon::now();
        $html = '<div class="' . ($isSuperAdmin && $activeTasks->count() > 1 ? 'space-y-1 divide-y divide-gray-500' : 'space-y-1') . '">';

        foreach ($activeTasks as $activeTask) {
            $startTime = \Carbon\Carbon::parse($activeTask->start_date);
            $isPaused = $activeTask->paused == 1;
            $baseSeconds = ($activeTask->duration ?? 0) * 60;
            $currentDurationSeconds = $isPaused ? $baseSeconds : ($startTime->diffInSeconds($now) + $baseSeconds);
            $taskhours = floor($currentDurationSeconds / 3600);
            $taskminutes = floor(($currentDurationSeconds % 3600) / 60);
            $taskseconds = $currentDurationSeconds % 60;
            $taskName = \App\Models\employees_tasks::where('id', $activeTask->task_employee_id)->value('task_name');
            $shouldShowName = $activeTasks->count() > 1;

            $html .= '<div class="text-xs font-semibold text-gray-800">';
            if ($shouldShowName) {
                $html .= '<span class="text-gray-600">' . e($taskName ?? ('Task ' . $activeTask->task_employee_id)) . ':</span> ';
            }
            $html .= '<span class="live-duration" data-start="' . $activeTask->start_date . '" data-extra-seconds="' . $baseSeconds . '" data-paused="' . ($isPaused ? 1 : 0) . '">';
            $html .= $taskhours . 'h:' . sprintf('%02d', $taskminutes) . 'm:' . sprintf('%02d', $taskseconds) . 's';
            $html .= '</span></div>';
        }

        $html .= '</div>';
        return $html;
    }

    private function renderAccountStatus($employee)
    {
        $employeeUser = \App\Models\User::find($employee->user_id);

        if ($employeeUser && $employeeUser->is_activate == 1) {
            return '<form action="' . route('admin.employee.deactivate', $employee->id) . '" method="post" data-confirm="Are you sure?">
                    ' . csrf_field() . '
                    <button type="submit" class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700 hover:bg-emerald-200">
                        Active
                    </button>
                </form>';
        }

        return '<form action="' . route('admin.employee.activate', $employee->id) . '" method="post" data-confirm="Are you sure?">
                ' . csrf_field() . '
                <button type="submit" class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700 hover:bg-red-200">
                    Inactive
                </button>
            </form>';
    }

    private function renderChrono($employee)
    {
        $isSuperAdmin = auth()->user()->hasRole('SuperAdmin') || auth()->user()->hasRole('super_admin');

        if ($isSuperAdmin) {
            $activeTasks = \App\Models\tasks_timer::where('employee_id', $employee->id)
                ->whereDate('start_date', today())
                ->whereNull('end_date')
                ->orderBy('start_date', 'desc')
                ->get();
        } else {
            $activeTasks = \App\Models\tasks_timer::where('employee_id', $employee->id)
                ->whereDate('start_date', today())
                ->whereNull('end_date')
                ->latest()
                ->take(1)
                ->get();
        }

        if ($activeTasks->isEmpty()) {
            return '<span class="text-xs text-gray-400">No Active Timer</span>';
        }

        $html = '';

        if ($isSuperAdmin && $activeTasks->count() > 1) {
            $html .= '<button type="button" class="inline-flex items-center px-3 py-1 mb-2 rounded-full text-xs font-semibold bg-red-100 text-red-700 hover:bg-red-200" onclick="stopAllTimersForEmployee(' . $employee->id . ')">Stop all</button>';
        }

        $html .= '<div class="space-y-1">';

        foreach ($activeTasks as $activeTask) {
            $taskName = \App\Models\employees_tasks::where('id', $activeTask->task_employee_id)->value('task_name');
            $shouldShowName = $activeTasks->count() > 1;

            $html .= '<form class="inline-flex items-center gap-2 stop-form-' . $employee->id . '" action="' . route('admin.tasks.stop', ['tasks' => $activeTask->task_employee_id, 'employee_id' => $employee->id]) . '" method="POST" data-confirm="Stop timer for this task?">';
            $html .= csrf_field();

            if ($shouldShowName) {
                $html .= '<span class="text-xs text-gray-700">' . e($taskName ?? ('Task ' . $activeTask->task_employee_id)) . '</span>';
            }

            $html .= '<button type="submit" class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-red-500 text-white hover:bg-red-600">Stop</button>';
            $html .= '</form>';
        }

        $html .= '</div>';
        return $html;
    }

    private function renderTaskHistory($employee)
    {
        $isSuperAdmin = auth()->user()->hasRole('SuperAdmin') || auth()->user()->hasRole('super_admin');

        // Get active task IDs to exclude from history
        if ($isSuperAdmin) {
            $activeTimers = \App\Models\tasks_timer::where('employee_id', $employee->id)
                ->whereDate('start_date', \Carbon\Carbon::today())
                ->whereNull('end_date')
                ->get();
        } else {
            $activeTimers = \App\Models\tasks_timer::where('employee_id', $employee->id)
                ->whereDate('start_date', \Carbon\Carbon::today())
                ->whereNull('end_date')
                ->latest()
                ->take(1)
                ->get();
        }

        $activeTaskIds = $activeTimers->pluck('task_id')->filter()->unique();

        // Get completed tasks today
        $completedTasksToday = \App\Models\tasks_timer::where('employee_id', $employee->id)
            ->whereDate('start_date', \Carbon\Carbon::today())
            ->whereNotNull('end_date')
            ->when($activeTaskIds->isNotEmpty(), fn($q) => $q->whereNotIn('task_id', $activeTaskIds))
            ->get()
            ->groupBy(fn($t) => $t->task_id ?? 'name:' . ($t->task_name ?? 'unknown'))
            ->map(function ($group) {
                $latest = $group->sortByDesc('end_date')->first();
                $latest->total_duration = $group->sum('duration');
                return $latest;
            })
            ->sortByDesc('end_date');

        if ($completedTasksToday->isEmpty()) {
            return '<p class="text-sm text-gray-500">No tasks completed today</p>';
        }

        $html = '';
        foreach ($completedTasksToday as $doneTask) {
            $durationMinutes = (int) ($doneTask->total_duration ?? $doneTask->duration);
            $h = floor($durationMinutes / 60);
            $m = $durationMinutes % 60;
            $taskModel = \App\Models\Tasks::find($doneTask->task_id);
            $projectName = null;

            if (!empty($doneTask->project_id)) {
                $projectName = optional(\App\Models\Project::find($doneTask->project_id))->name;
            }
            if (!$projectName && $taskModel) {
                $projectName = optional($taskModel->project)->name ?? null;
            }

            $taskName = $doneTask->task_name ?? ($taskModel->title ?? 'Task');
            $projectDisplay = $projectName ?? 'No project linked';

            $html .= '<div class="flex items-center justify-between rounded-lg border border-gray-200 bg-white px-3 py-2">';
            $html .= '<div class="flex flex-col">';
            $html .= '<span class="text-sm font-medium text-gray-900">' . e($taskName) . '</span>';
            $html .= '<span class="text-xs text-gray-500">' . e($projectDisplay) . '</span>';
            $html .= '</div>';
            $html .= '<div class="flex items-center gap-3">';
            $html .= '<span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-700">Completed</span>';
            $html .= '<span class="text-xs text-gray-700 whitespace-nowrap">' . $h . 'h ' . $m . 'm</span>';

            // Start button form
            $html .= '<form action="' . route('admin.tasks.start', ['tasks' => $doneTask->task_employee_id ?? $doneTask->task_id, 'employee_id' => $doneTask->employee_id]) . '" method="POST" class="history-start-form" data-task-id="' . ($doneTask->task_employee_id ?? $doneTask->task_id) . '">';
            $html .= csrf_field();
            $html .= '<textarea name="comment" placeholder="Enter comment" rows="1" class="border border-slate-300 rounded px-2 py-1 text-xs text-slate-700 focus:outline-none focus:ring focus:ring-emerald-200 resize-y min-h-[32px]"></textarea>';
            $html .= '<button type="submit" class="inline-flex items-center px-3 py-1 rounded-full text-[11px] font-semibold bg-emerald-500 text-white hover:bg-emerald-600">Start</button>';
            $html .= '</form>';

            $html .= '</div></div>';
        }

        return $html;
    }

    public function refreshProjects(Request $request)
    {
        if (!$request->ajax()) {
            abort(404);
        }

        $todayStart = \Carbon\Carbon::today();
        $todayEnd = \Carbon\Carbon::today()->endOfDay();
        $entrepriseId = optional(Auth::user())->entreprise_id;

        // Collect timers that overlap today
        $timersToday = \App\Models\tasks_timer::where(function ($q) use ($todayStart, $todayEnd) {
            $q->where('start_date', '<=', $todayEnd)
                ->where(function ($qq) use ($todayStart) {
                    $qq->whereNull('end_date')
                        ->orWhere('end_date', '>=', $todayStart);
                });
        })
            ->when($entrepriseId, fn($q) => $q->where('entreprise_id', $entrepriseId))
            ->get();

        // Gather project IDs from timers directly and via tasks
        $projectIdsDirect = $timersToday->pluck('project_id')->filter();
        $taskIds = $timersToday->pluck('task_id')->filter();
        $projectIdsFromTasks = $taskIds->isNotEmpty()
            ? \App\Models\Tasks::whereIn('id', $taskIds)->pluck('project_id')
            : collect();
        $projectIdsToday = $projectIdsDirect->merge($projectIdsFromTasks)->unique()->filter();

        $projectsToday = $projectIdsToday->isNotEmpty()
            ? \App\Models\Project::whereIn('id', $projectIdsToday)->get()
            : collect();

        return response()->json([
            'html' => view('components.dashboard.project-overview-list', compact('projectsToday'))->render(),
            'success' => true
        ]);
    }
}
