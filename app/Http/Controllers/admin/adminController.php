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
            ->whereNull('parent_id')  // Exclude subprojects
            ->orderBy('client_id')
            ->orderBy('name')
            ->paginate(6, ['*'], 'page', $projectPage);

        $projectsFilter = Project::where('entreprise_id', auth()->user()->entreprise_id)
            ->where(function ($query) {
                $query->whereNull('AcceptedDevisProject')
                    ->orWhere('AcceptedDevisProject', '=', 1);
            })
            ->where('archived', 0)
            ->whereNull('parent_id')  // Exclude subprojects
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
        $today = today()->format('Y-m-d');
        $entreprise_id = auth()->user()->entreprise_id;

        // Show all events in the same enterprise
        $dayEvents = calendars::where('entreprise_id', $entreprise_id)
            // Include events whose period covers today (start_date <= today <= end_date)
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->with('creator.employee')
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
        // Get ALL tasks the employee worked on today (completed + active)
        $today = \Carbon\Carbon::today();
        $currentTasks = \App\Models\tasks_timer::where('employee_id', $employee->id)
            ->whereDate('start_date', $today)
            ->orderBy('start_date', 'desc')
            ->get();

        if ($currentTasks->isEmpty()) {
            return '<span class="text-xs text-gray-400">No Task</span>';
        }

        // Group current tasks by project to keep borders aligned with project/duration/chrono,
        // but still show ALL task names
        $groupedTasks = $currentTasks
            ->map(function ($t) {
                $projectId = \App\Models\Tasks::where('id', $t->task_id)->value('project_id');
                $t->project_for_grouping = $projectId ?? 0;
                return $t;
            })
            ->sortBy('project_for_grouping')
            ->groupBy('project_for_grouping');

        $html = '<div class="space-y-3">';
        $totalGroups = count($groupedTasks);
        $groupIndex = 0;

        foreach ($groupedTasks as $projectId => $tasks) {
            $groupIndex++;
            $hasBorder = $groupIndex < $totalGroups;

            $html .= '<div class="space-y-1' . ($hasBorder ? ' pb-2 border-b border-gray-200' : '') . '">';

            // Show each task name once, even if there are multiple timers for it
            $tasksById = $tasks->groupBy('task_id');

            foreach ($tasksById as $taskId => $taskGroup) {
                /** @var \App\Models\tasks_timer $currentTask */
                $currentTask = $taskGroup->first();

                // Determine if this task is currently active (at least one running timer)
                $hasActiveTimer = $taskGroup->contains(function ($t) {
                    return $t->end_date === null && (int) ($t->paused ?? 0) === 0;
                });

                // Active tasks stay blue; finished tasks are gray
                $baseClasses = 'text-xs font-semibold ';
                $colorClasses = $hasActiveTimer
                    ? 'text-blue-600 hover:underline'
                    : 'text-gray-400';
                $classes = trim($baseClasses . $colorClasses) . ' block';

                $route = '#';
                if (auth()->user()->hasRole('Admin') || auth()->user()->hasRole('SuperAdmin') || auth()->user()->hasRole('super_admin')) {
                    $route = route('admin.tasks.show', $currentTask->task_id);
                } elseif (auth()->user()->hasRole('Responsable de projet')) {
                    $route = route('Responsable_de_projet.tasks.show', $currentTask->task_id);
                }

                $html .= '<a href="' . $route . '" class="' . e($classes) . '">' . e($currentTask->task_name) . '</a>';
            }

            $html .= '</div>';
        }

        $html .= '</div>';
        return $html;
    }

    private function renderCurrentProject($employee)
    {
        // Use ALL timers for today (completed + active) so we list every project
        $today = \Carbon\Carbon::today();
        $activeTasks = \App\Models\tasks_timer::where('employee_id', $employee->id)
            ->whereDate('start_date', $today)
            ->orderBy('start_date', 'desc')
            ->get();

        if ($activeTasks->isEmpty()) {
            return '<span class="text-xs text-gray-400">No Project</span>';
        }

        // Group active tasks by project so project lines line up with tasks/duration/chrono
        $groupedActiveTasks = $activeTasks
            ->map(function ($t) {
                $projectId = \App\Models\Tasks::where('id', $t->task_id)->value('project_id');
                $t->project_for_grouping = $projectId ?? 0;
                return $t;
            })
            ->sortBy('project_for_grouping')
            ->groupBy('project_for_grouping');

        $html = '<div class="space-y-3">';
        $totalGroups = count($groupedActiveTasks);
        $groupIndex = 0;

        foreach ($groupedActiveTasks as $projectId => $projectTasks) {
            $groupIndex++;
            $hasBorder = $groupIndex < $totalGroups;
            $linesCount = count($projectTasks);

            $project = $projectId ? \App\Models\Project::find($projectId) : null;

            // Determine if this project has any active (running) timer
            $hasActiveTimer = $projectTasks->contains(function ($t) {
                return $t->end_date === null && (int) ($t->paused ?? 0) === 0;
            });

            // Active projects stay green; finished projects are gray
            $baseClasses = 'text-xs font-semibold ';
            $colorClasses = $hasActiveTimer
                ? 'text-emerald-600'
                : 'text-gray-400';
            $projectTextClasses = trim($baseClasses . $colorClasses);
            $linkClasses = $hasActiveTimer ? 'hover:underline block' : 'block';

            $html .= '<div class="space-y-1' . ($hasBorder ? ' pb-2 border-b border-gray-200' : '') . '">';

            // First line: project name aligned with first task of this project
            $html .= '<div class="' . e($projectTextClasses) . '">';
            if ($project) {
                $html .= '<a href="' . route('admin.projects.show', $project->id) . '" class="' . e($linkClasses) . '">' . e($project->name) . '</a>';
            } else {
                $html .= '<span class="text-gray-400 block">' . e(__('messages.No Project')) . '</span>';
            }
            $html .= '</div>';

            // Extra spacer lines so tasks/durations/chrono stay aligned
            for ($i = 1; $i < $linesCount; $i++) {
                $html .= '<div class="text-xs">&nbsp;</div>';
            }

            $html .= '</div>';
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
            return '<p class="text-xs font-medium text-gray-600">' . e(__('messages.No Task')) . '</p>';
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
        // Show TOTAL worked time today per task, grouped by project
        $today = \Carbon\Carbon::today();

        // All timers (completed + active) for this employee today
        $timersToday = \App\Models\tasks_timer::where('employee_id', $employee->id)
            ->whereDate('start_date', $today)
            ->orderBy('start_date', 'desc')
            ->get();

        if ($timersToday->isEmpty()) {
            return '<span class="text-xs font-medium text-gray-700">' . e(__('messages.No work today')) . '</span>';
        }

        $now = \Carbon\Carbon::now();

        // Group timers by project first
        $groupedActiveTasks = $timersToday
            ->map(function ($t) {
                $projectId = \App\Models\Tasks::where('id', $t->task_id)->value('project_id');
                $t->project_for_grouping = $projectId ?? 0;
                return $t;
            })
            ->sortBy('project_for_grouping')
            ->groupBy('project_for_grouping');

        $html = '<div class="space-y-3">';
        $totalGroups = count($groupedActiveTasks);
        $groupIndex = 0;

        foreach ($groupedActiveTasks as $projectId => $projectTasks) {
            $groupIndex++;
            $hasBorder = $groupIndex < $totalGroups;

            $html .= '<div class="space-y-1 w-full' . ($hasBorder ? ' pb-2 border-b border-gray-200' : '') . '">';

            // Within this project, group timers by task_id to get a total per task
            $tasksById = $projectTasks->groupBy('task_id');
            $project = $projectId ? \App\Models\Project::find($projectId) : null;

            foreach ($tasksById as $taskId => $taskTimers) {
                // Sum total minutes for this task today (completed + active)
                $totalMinutes = 0;

                foreach ($taskTimers as $timer) {
                    $baseMinutes = (int) ($timer->duration ?? 0);

                    if ($timer->end_date === null) {
                        // Active timer - add live time from start until now (or paused)
                        $startTime = \Carbon\Carbon::parse($timer->start_date);
                        if ($timer->paused == 1) {
                            // Paused: only stored minutes
                            $totalMinutes += $baseMinutes;
                        } else {
                            // Running: stored minutes + elapsed since start
                            $elapsedSeconds = $startTime->diffInSeconds($now);
                            $totalMinutes += $baseMinutes + (int) floor($elapsedSeconds / 60);
                        }
                    } else {
                        // Completed timer: stored duration is already in minutes
                        $totalMinutes += $baseMinutes;
                    }
                }

                $hours = floor($totalMinutes / 60);
                $minutes = $totalMinutes % 60;

                $html .= '<div class="text-xs">';
                $html .= '<div class="font-semibold text-gray-800">';
                $html .= $hours . 'h:' . sprintf('%02d', $minutes) . 'm';
                $html .= '</div></div>';
            }

            // Project total (all time)
            if ($project) {
                $projecttasksId = \App\Models\Tasks::where('project_id', $project->id)->pluck('id');
                $projectTotalMinutes = \App\Models\tasks_timer::where(function ($q) use ($projecttasksId, $project) {
                    $q->whereIn('task_id', $projecttasksId)
                        ->orWhere('project_id', $project->id);
                })->where('employee_id', $employee->id)->sum('duration');
                $projectTotalHours = floor($projectTotalMinutes / 60);
                $projectTotalMins = $projectTotalMinutes % 60;

                $html .= '<div class="text-xs text-gray-500 mt-1 pt-1 border-t border-gray-200">';
                $html .= 'Total: ' . $projectTotalHours . 'h:' . sprintf('%02d', $projectTotalMins) . 'm';
                $html .= '</div>';
            }

            $html .= '</div>';
        }

        $html .= '</div>';
        return $html;
    }

    private function renderAccountStatus($employee)
    {
        $employeeUser = \App\Models\User::find($employee->user_id);

        if ($employeeUser && $employeeUser->is_activate == 1) {
            return '<form action="' . route('admin.employee.deactivate', $employee->id) . '" method="post" data-confirm="' . e(__('messages.Are you sure?')) . '">
                    ' . csrf_field() . '
                    <button type="submit" class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700 hover:bg-emerald-200">
                        ' . e(__('messages.Active')) . '
                    </button>
                </form>';
        }

        return '<form action="' . route('admin.employee.activate', $employee->id) . '" method="post" data-confirm="' . e(__('messages.Are you sure?')) . '">
                ' . csrf_field() . '
                <button type="submit" class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700 hover:bg-red-200">
                    ' . e(__('messages.Inactive')) . '
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
            return '<span class="text-xs text-gray-400">' . e(__('messages.No Active Timer')) . '</span>';
        }

        $html = '';

        if ($isSuperAdmin && $activeTasks->count() > 1) {
            $html .= '<button type="button" class="inline-flex items-center px-3 py-1 mb-2 rounded-full text-xs font-semibold bg-red-100 text-red-700 hover:bg-red-200" onclick="stopAllTimersForEmployee(' . $employee->id . ')">' . e(__('messages.Stop all')) . '</button>';
        }

        // Group active tasks by project for visual separation in Chrono
        $groupedActiveTasks = $activeTasks
            ->map(function ($t) {
                $projectId = \App\Models\Tasks::where('id', $t->task_id)->value('project_id');
                $t->project_for_grouping = $projectId ?? 0;
                return $t;
            })
            ->sortBy('project_for_grouping')
            ->groupBy('project_for_grouping');

        $html .= '<div class="space-y-3 flex flex-col items-center">';
        $totalGroups = count($groupedActiveTasks);
        $groupIndex = 0;

        foreach ($groupedActiveTasks as $projectId => $projectTasks) {
            $groupIndex++;
            $hasBorder = $groupIndex < $totalGroups;

            $html .= '<div class="space-y-1 w-full flex flex-col items-center' . ($hasBorder ? ' pb-2 border-b border-gray-200' : '') . '">';

            foreach ($projectTasks as $activeTask) {
                $currentUserEmployeeId = \App\Models\Employees::where('user_id', auth()->user()->id)->first()?->id;
                $isOwnTask = ($employee->id == $currentUserEmployeeId);

                if ($isOwnTask) {
                    $html .= '<form class="flex items-center justify-center gap-2 stop-form stop-form-' . $employee->id . '" action="' . route('admin.tasks.stop', ['tasks' => $activeTask->task_employee_id, 'employee_id' => $employee->id]) . '" method="POST">';
                    $html .= csrf_field();
                    $html .= '<input type="hidden" name="stop_comment" class="stop-comment-field">';
                    $html .= '<button type="submit" class="stop-button inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-red-500 text-white hover:bg-red-600" data-task-id="' . $activeTask->task_id . '">' . e(__('messages.Stop')) . '</button>';
                    $html .= '</form>';
                } else {
                    $html .= '<form class="flex items-center justify-center gap-2 stop-form-' . $employee->id . '" action="' . route('admin.tasks.stop', ['tasks' => $activeTask->task_employee_id, 'employee_id' => $employee->id]) . '" method="POST" data-confirm="' . e(__('messages.Stop')) . '">';
                    $html .= csrf_field();
                    $html .= '<button type="submit" class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-red-500 text-white hover:bg-red-600">' . e(__('messages.Stop')) . '</button>';
                    $html .= '</form>';
                }
            }

            $html .= '</div>';
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

        // Group timers by employee and collect their projects
        $employeeIdsToday = $timersToday->pluck('employee_id')->filter()->unique();
        $employeesToday = $employeeIdsToday->isNotEmpty()
            ? \App\Models\Employees::whereIn('id', $employeeIdsToday)->get()
            : collect();

        return response()->json([
            'html' => view('components.dashboard.employee-overview-list', compact('employeesToday', 'timersToday'))->render(),
            'success' => true
        ]);
    }
}
