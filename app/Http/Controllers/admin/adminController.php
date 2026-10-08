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

        $lastMonthRevenue = invoices_history::where('entreprise_id', Auth::user()->entreprise_id)
            ->whereMonth('created_at', now()->copy()->subMonthNoOverflow()->month)
            ->whereYear('created_at', now()->copy()->subMonthNoOverflow()->year)
            ->sum('total_ttc');

        if ($lastMonthRevenue > 0) {
            $monthlyRevenueGrowth = (($monthlyRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100;
        } else {
            $monthlyRevenueGrowth = $monthlyRevenue > 0 ? 100 : 0;
        }

        $monthlyPaidRevenue = invoices_history::where('entreprise_id', Auth::user()->entreprise_id)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->where('Payment_status', 1)
            ->sum('total_ttc');

        $collectionRate = $monthlyRevenue > 0
            ? min(100, max(0, ($monthlyPaidRevenue / $monthlyRevenue) * 100))
            : 0;



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
            'monthlyRevenueGrowth',
            'monthlyPaidRevenue',
            'collectionRate',
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

        $isSuperAdmin = auth()->user()->hasRole('SuperAdmin') || auth()->user()->hasRole('super_admin');

        $employeesData = $employees->map(function ($employee) use ($isSuperAdmin) {
            $employeeUser = \App\Models\User::find($employee->user_id);
            $nowRow = \Carbon\Carbon::now();

            // Same active-timer queries as the dashboard row @php block in
            // components/dashboard/content.blade.php, so the shared
            // employee-row-cells partial renders identical markup on AJAX
            // refresh as it does on the initial page load (single source of truth).
            if ($isSuperAdmin) {
                $rowActiveTasks = \App\Models\tasks_timer::where('employee_id', $employee->id)
                    ->whereNull('end_date')
                    ->orderBy('start_date', 'desc')
                    ->get();
            } else {
                $rowActiveTasks = \App\Models\tasks_timer::where('employee_id', $employee->id)
                    ->whereNull('end_date')
                    ->latest()->take(1)->get();
            }
            $rowTasksToday = $rowActiveTasks->concat(
                \App\Models\tasks_timer::where('employee_id', $employee->id)
                    ->whereDate('start_date', \Carbon\Carbon::today())
                    ->whereNotNull('end_date')
                    ->orderBy('start_date', 'desc')
                    ->get()
            )->groupBy('task_id');

            return [
                'id' => $employee->id,
                'rowCells' => view('components.dashboard.employee-row-cells', [
                    'employee' => $employee,
                    'employeeUser' => $employeeUser,
                    'rowActiveTasks' => $rowActiveTasks,
                    'rowTasksToday' => $rowTasksToday,
                    'nowRow' => $nowRow,
                ])->render(),
                'taskHistory' => $this->renderTaskHistory($employee),
            ];
        });

        return response()->json([
            'employees' => $employeesData,
            'success' => true
        ]);
    }

    // ===== HELPER METHODS =====

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
            return '<p class="text-xs text-slate-500">' . e(__('messages.No completed tasks today.')) . '</p>';
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

            $taskName = $doneTask->task_name ?? ($taskModel->title ?? __('messages.Task'));
            $projectDisplay = $projectName ?? __('messages.No project linked');

            $html .= '<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2">';
            $html .= '<div class="flex flex-col">';
            $html .= '<span class="text-sm font-medium text-slate-900">' . e($taskName) . '</span>';
            $html .= '<span class="text-xs text-slate-500">' . e($projectDisplay) . '</span>';
            $html .= '</div>';
            $html .= '<div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-3 w-full sm:w-auto">';
            $html .= '<span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-700">' . e(__('messages.Completed')) . '</span>';
            $html .= '<span class="text-xs text-slate-700 whitespace-nowrap">' . $h . 'h ' . $m . 'm</span>';

            // Start button form
            $html .= '<form action="' . route('admin.tasks.start', ['tasks' => $doneTask->task_employee_id ?? $doneTask->task_id, 'employee_id' => $doneTask->employee_id]) . '" method="POST" class="history-start-form" data-task-id="' . ($doneTask->task_employee_id ?? $doneTask->task_id) . '">';
            $html .= csrf_field();
            $html .= '<textarea name="comment" placeholder="' . e(__('messages.Enter comment')) . '" rows="1" class="border border-slate-300 rounded px-2 py-1 text-xs text-slate-700 focus:outline-none focus:ring focus:ring-emerald-200 resize-y min-h-[32px] w-full sm:w-56"></textarea>';
            $html .= '<button type="submit" class="inline-flex items-center justify-center px-3 py-1 rounded-full text-[11px] font-semibold bg-emerald-500 text-white hover:bg-emerald-600 w-full sm:w-auto">' . e(__('messages.Start')) . '</button>';
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
