<?php

namespace App\Http\Controllers\admin;

use DateTime;
use DateInterval;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Tasks;
use App\Models\Teams;
use App\Models\Project;
use App\Models\Employees;
use App\Models\Client;
use App\Models\team_tasks;
use App\Models\tasks_timer;
use App\Models\team_members;
use App\Models\project_teams;
use App\Models\Entreprise;
use App\Models\work_days_repports;
use Illuminate\Http\Request;
use App\Models\employees_tasks;
use App\Models\client_contacts_projects;
use App\Models\client_contacts;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\calendars;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\invoices_history;
use App\Mail\CustomerReportMail;


class tasksController extends Controller
{

    /**
     * Normalize incoming IDs (JSON string | array | collection | scalar) to array of integers.
     */
    private function normalizeIds($ids)
    {
        if ($ids === null || $ids === '') {
            return [];
        }
        if (is_string($ids)) {
            $decoded = json_decode($ids, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $ids = $decoded;
            } else {
                $ids = [$ids];
            }
        }
        if ($ids instanceof \Illuminate\Support\Collection) {
            $ids = $ids->toArray();
        }
        if (!is_array($ids)) {
            $ids = [$ids];
        }
        // Cast to integers where possible and filter empties
        return array_values(array_filter(array_map(function ($v) {
            return is_numeric($v) ? (int) $v : $v;
        }, $ids), function ($v) {
            return $v !== null && $v !== '';
        }));
    }

    /**
     * Check if an employee is a SuperAdmin
     */
    private function isEmployeeSuperAdmin($employee)
    {
        if (!$employee || !$employee->user_id) {
            return false;
        }

        $user = User::find($employee->user_id);
        if (!$user) {
            return false;
        }

        return $user->hasRole('SuperAdmin') || $user->hasRole('super_admin');
    }

    /**
     * Safely resolve an employee display name, falling back to the related user name.
     */
    private function getEmployeeDisplayName($employee)
    {
        if (!$employee) {
            return 'Unknown';
        }

        $name = trim((string) ($employee->name ?? ''));
        if ($name === '' && $employee->user) {
            $name = trim((string) ($employee->user->name ?? ''));
        }

        return $name !== '' ? $name : 'Unknown';
    }

    /**
     * Validate that non-SuperAdmin employees can only have one ongoing/active task
     * If there's no ongoing task timer, employee is "off work" and can be assigned a new task
     */
    private function validateEmployeeTaskLimit($employee, $currentTaskId = null)
    {
        // SuperAdmin employees can have multiple ongoing tasks
        if ($this->isEmployeeSuperAdmin($employee)) {
            return true;
        }

        // Check if employee has any active/ongoing task timers (tasks that are currently running)
        // An active task timer is one where end_date is null
        $activeTaskTimersQuery = tasks_timer::where('employee_id', $employee->id)
            ->whereNull('end_date')
            ->where('entreprise_id', Auth::user()->entreprise_id);

        // Exclude current task if updating (check if current task has an active timer)
        if ($currentTaskId) {
            // Get the employee_task record for this task
            $currentEmployeeTask = employees_tasks::where('employee_id', $employee->id)
                ->where('task_id', $currentTaskId)
                ->where('entreprise_id', Auth::user()->entreprise_id)
                ->first();

            if ($currentEmployeeTask) {
                // Exclude timers for the current task being updated
                $activeTaskTimersQuery->where('task_employee_id', '!=', $currentEmployeeTask->id);
            }
        }

        $activeTaskCount = $activeTaskTimersQuery->count();

        // Non-SuperAdmin employees can only have one ongoing task at a time
        // If there's no ongoing task, they're "off work" and can be assigned a new task
        if ($activeTaskCount > 0) {
            return false;
        }

        return true;
    }

    public function index()
    {
        $projects = Project::where('entreprise_id', Auth::user()->entreprise_id)->where(function ($query) {
            $query->whereNull('AcceptedDevisProject')
                ->orWhere('AcceptedDevisProject', '=', 1);
        })
            ->where('archived', 0)
            ->get();
        $enterpriseId = Auth::user()->entreprise_id;
        $employees = employees::where('entreprise_id', $enterpriseId)->where('archived', 0)->orderBy('name', 'asc')->get();
        $teams = Teams::where('entreprise_id', Auth::user()->entreprise_id)->get();
        $tasks = Tasks::where('entreprise_id', Auth::user()->entreprise_id)->orderBy('created_at', 'desc')->get();
        $tasks_ordered = Tasks::where('user_id', Auth::user()->id)->orderBy('title', 'asc')->get();
        return view('admin.tasks.index', compact('employees', 'tasks', 'tasks_ordered', 'teams', 'projects'));
    }


    public function create($projectId)
    {

        $superAdminIds = User::whereHas('roles', function ($q) {
            $q->where('name', 'SuperAdmin');
        })
            ->where('entreprise_id', Auth::user()->entreprise_id)
            ->pluck('id');


        $employee_users = collect();
        $employees = employees::where('entreprise_id', Auth::user()->entreprise_id)
            // ->whereNotIn('id', $superAdminIds)
            ->where('archived', 0)
            ->orderBy('name', 'asc')
            ->get();

        foreach ($employees as $employee) {
            $employee_user = User::where('id', $employee->user_id)
                ->where('is_activate', 1)
                ->get();
            $employee_users = $employee_users->concat($employee_user);
        }
        $teams = Teams::where('entreprise_id', Auth::user()->entreprise_id)->get();
        $project = Project::where('id', $projectId)->first();

        return view('admin.tasks.create', compact('employee_users', 'teams', 'project'));
    }


    public function store(Request $request, $projectId)
    {
        // Get the authenticated admin's enterprise_id
        $enterpriseId = Auth::user()->entreprise_id;

        $validated = $request->validate([
            "title" => "required",
            "end" => "nullable",
            "description" => "nullable",
        ]);

        $user_id = Auth::user()->id;

        $project = Project::where('id', $projectId)->first();
        // Create the associated task
        $task = new Tasks([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'project_name' => $project->name,
            'project_id' => $project->id,
            'entreprise_id' => $enterpriseId, // Associate with the admin's enterprise_id
            'user_id' => $user_id,
            'estimated_hours' => $request->input('estimated_hours'),
            'start' => $request->input('start'),
            'end' => $request->input('end'),
        ]);

        $task->save();

        $teams = $request->input('teams', []);

        $teams_collection = Teams::whereIn('id', $teams)->get();
        $teams_employees = collect();

        foreach ($teams_collection as $team) {
            $team_employees_id = team_members::where('team_id', $team->id)->pluck('employee_id');
            $teams_employees = $teams_employees->concat($team_employees_id);
        }
        $teams_employees = $teams_employees->unique();

        foreach ($teams_employees as $employee_id) {
            $employee = Employees::where('id', $employee_id)->first();

            $employee_task = new employees_tasks;
            $employee_task->employee_id = $employee->id;
            $employee_task->employee_name = $this->getEmployeeDisplayName($employee);
            $employee_task->task_id = $task->id;
            $employee_task->task_name = $task->title;
            $employee_task->entreprise_id = Auth::user()->entreprise_id;
            $employee_task->save();
        }

        foreach ($teams as $team) {
            $team_task = new team_tasks;
            $team_task->team_id = $team;
            $team_task->task_id = $task->id;
            $team_task->task_name = $task->title;
            $team_task->entreprise_id = Auth::user()->entreprise_id;
            $team_task->save();

            $project_teams = new project_teams;
            $project_teams->team_id = $team;
            $project_teams->team_name = Teams::where('id', $team)->first()->name;
            $project_teams->project_id = $project->id;
            $project_teams->entreprise_id = Auth::user()->entreprise_id;
            $project_teams->save();

        }



        $employees = $request->input('employees', []);
        foreach ($employees as $emp) {
            $employee = employees::where('user_id', $emp)->where('entreprise_id', Auth::user()->entreprise_id)->first();

            $employee_task = new employees_tasks;
            $employee_task->employee_id = $employee->id;
            $employee_task->employee_name = $this->getEmployeeDisplayName($employee);
            $employee_task->task_id = $task->id;
            $employee_task->task_name = $task->title;
            $employee_task->entreprise_id = Auth::user()->entreprise_id;
            $employee_task->save();
        }

        // Get all SuperAdmin users and create task assignments for each
        $superAdminIds = User::whereHas('roles', function ($q) {
            $q->where('name', 'SuperAdmin');
        })
            ->where('entreprise_id', Auth::user()->entreprise_id)
            ->pluck('id');

        $superAdminEmployees = Employees::whereIn('user_id', $superAdminIds)->get();

        foreach ($superAdminEmployees as $superAdminEmployee) {
            $admin_task = new employees_tasks;
            $admin_task->employee_id = $superAdminEmployee->id;
            $admin_task->employee_name = $this->getEmployeeDisplayName($superAdminEmployee);
            $admin_task->task_id = $task->id;
            $admin_task->task_name = $task->title;
            $admin_task->entreprise_id = Auth::user()->entreprise_id;
            $admin_task->save();
        }

        // Create a calendar planning event linked to this task
        // Use task/project/client data and task dates
        $startDate = $task->start ? Carbon::parse($task->start)->startOfDay() : Carbon::now()->startOfDay();
        $endDate = $task->end
            ? Carbon::parse($task->end)->endOfDay()
            : $startDate->copy()->endOfDay();

        $event = calendars::create([
            'title'        => $task->title,
            'description'  => $task->description,
            // Default color for task-created events (indigo-600)
            'color'        => '#4f46e5',
            'is_finished'  => false,
            'start_date'   => $startDate,
            'end_date'     => $endDate,
            'entreprise_id'=> $enterpriseId,
            'user_id'      => $user_id,
            'client_id'    => $project->client_id ?? null,
            'project_id'   => $project->id,
            'task_id'      => $task->id,
        ]);

        // Attach all employees assigned to this task to the calendar event
        $eventEmployeeIds = employees_tasks::where('task_id', $task->id)
            ->where('entreprise_id', $enterpriseId)
            ->pluck('employee_id')
            ->unique()
            ->values()
            ->all();

        if (!empty($eventEmployeeIds)) {
            $event->employees()->sync($eventEmployeeIds);
        }


        if (Auth::user()->hasRole('Admin')) {
            return redirect()->route('admin.projects.show', $projectId)->with('message', 'Task Created Successfully');
        }
        if (Auth::user()->hasRole('Responsable de projet')) {
            return redirect()->route('Responsable_de_projet.projects.show', $projectId)->with('message', 'Task Created Successfully');
        }
        if (Auth::user()->hasRole('Responsable de projet et gestionnaire')) {
            return redirect()->route('Responsable_gestionnaire.projects.show', $projectId)->with('message', 'Task Created Successfully');
        }

    }

    public function edit(Tasks $task)
    {
        $tasksTimer = tasks_timer::where('task_id', $task->id)
            ->whereNull('end_date')
            ->get();
        if ($tasksTimer->isNotEmpty()) {
            return back()->with('alertmessage', 'You cannot edit a task that is currently running.');
        }
        $admins_id = User::where('is_activate', 1)->whereHas('roles', function ($q) {
            $q->where('name', 'admin');
        })->pluck('id');
        $admiemployee = employees::whereIn('user_id', $admins_id)->get();
        //$admiemployee = employees::where('user_id',  Auth::user()->id)->first();
        $old_employee_users = collect();
        $old_employees = employees_tasks::where('task_id', $task->id)
            ->whereNotIn('employee_id', $admiemployee->pluck('id')->toArray())
            ->get();
        if ($old_employees) {
            foreach ($old_employees as $old_emp) {
                $old_employee = employees::where('id', $old_emp->employee_id)->first();
                $old_employee_usr = User::where('id', $old_employee->user_id)
                    ->where('is_activate', 1)
                    ->get();
                $old_employee_users = $old_employee_users->concat($old_employee_usr);
            }

            $employee_users = collect();
            $oldEmployeeIds = employees_tasks::where('task_id', $task->id)
                ->pluck('employee_id');
            $employees = employees::where('entreprise_id', Auth::user()->entreprise_id)
                ->whereNotIn('id', $oldEmployeeIds)
                ->where('archived', 0)
                ->orderBy('name', 'asc')
                ->get();
            foreach ($employees as $employee) {
                $employee_user = User::where('id', $employee->user_id)
                    ->where('is_activate', 1)
                    ->get();
                $employee_users = $employee_users->concat($employee_user);
            }

            $teams = Teams::where('entreprise_id', Auth::user()->entreprise_id)->get();
            $projects = Project::where('entreprise_id', Auth::user()->entreprise_id)->where(function ($query) {
                $query->whereNull('AcceptedDevisProject')
                    ->orWhere('AcceptedDevisProject', '=', 1);
            })
                ->where('archived', 0)
                ->get();

            return view('admin.tasks.edit', compact('task', 'projects', 'employee_users', 'teams', 'old_employee_users'));

        } else {
            $employee_users = collect();
            $employees = employees::where('entreprise_id', Auth::user()->entreprise_id)
                ->whereNotIn('id', $admiemployee->pluck('id')->toArray())
                ->where('archived', 0)
                ->orderBy('name', 'asc')
                ->get();
            foreach ($employees as $employee) {
                $employee_user = User::where('id', $employee->user_id)
                    ->where('is_activate', 1)
                    ->get();
                $employee_users = $employee_users->concat($employee_user);
            }

            $teams = Teams::where('entreprise_id', Auth::user()->entreprise_id)->get();
            $projects = Project::where('entreprise_id', Auth::user()->entreprise_id)->where(function ($query) {
                $query->whereNull('AcceptedDevisProject')
                    ->orWhere('AcceptedDevisProject', '=', 1);
            })
                ->where('archived', 0)
                ->get();

            return view('admin.tasks.edit', compact('task', 'projects', 'employee_users', 'teams'));

        }

    }

    public function update(Request $request, Tasks $task)
    {
        // Get the authenticated admin's enterprise_id
        $enterpriseId = Auth::user()->entreprise_id;
        $projectId = $task->project_id;

        $validated = $request->validate([
            "title" => "required",
            "end" => "nullable",
            "description" => "nullable",
        ]);

        //$project = Project::where('name', $validated['project_name'])->first();

        // Update the task
        $task->update([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'estimated_hours' => $request->input('estimated_hours'),
            'start' => $request->input('start'),
            'end' => $request->input('end'),
        ]);


        $tasks_timer = tasks_timer::where('task_id', $task->id)->get();
        foreach ($tasks_timer as $task_timer) {
            $task_timer->task_name = $validated['title'];
            $task_timer->save();
        }
        // Delete existing team and employee associations
        team_tasks::where('task_id', $task->id)->delete();
        //employees_tasks::where('task_id', $task->id)->delete();

        // Update team associations
        $teams = $request->input('teams', []);
        foreach ($teams as $team_id) {
            $team_task = new team_tasks;
            $team_task->team_id = $team_id;
            $team_task->task_id = $task->id;
            $team_task->task_name = $task->title;
            $team_task->entreprise_id = $enterpriseId;
            $team_task->save();

            // Add team members to the task
            $team_members = team_members::where('team_id', $team_id)->get();
            foreach ($team_members as $member) {
                $employee = Employees::find($member->employee_id);
                if ($employee) {
            
                    $employee_task = new employees_tasks;
                    $employee_task->employee_id = $employee->id;
                    $employee_task->employee_name = $this->getEmployeeDisplayName($employee);
                    $employee_task->task_id = $task->id;
                    $employee_task->task_name = $task->title;
                    $employee_task->entreprise_id = $enterpriseId;
                    $employee_task->save();
                }
            }
        }
        if (Auth::user()->hasRole('Responsable de projet') || Auth::user()->hasRole('Responsable de projet et gestionnaire')) {
            // Update individual employee associations
            $admins_id = User::where('is_activate', 1)->whereHas('roles', function ($q) {
                $q->where('name', 'admin');
            })->pluck('id');
            $adminemployee = employees::whereIn('user_id', $admins_id)->get();

            employees_tasks::where('task_id', $task->id)->whereNotIn('employee_id', $adminemployee->pluck('id')->toArray())->delete();
        }
        if (Auth::user()->hasRole('Admin')) {
            // Update individual employee associations
            employees_tasks::where('task_id', $task->id)->delete();
        }

        $employees = $request->input('employees', []);
        foreach ($employees as $employee_id) {
            $employee = Employees::where('user_id', $employee_id)->first();
            if ($employee) {
                $employee_task = new employees_tasks;
                $employee_task->employee_id = $employee->id;
                $employee_task->employee_name = $this->getEmployeeDisplayName($employee);
                $employee_task->task_id = $task->id;
                $employee_task->task_name = $task->title;
                $employee_task->entreprise_id = $enterpriseId;
                $employee_task->save();
            }
        }



        if (Auth::user()->hasRole('Admin')) {
            return redirect()->route('admin.projects.show', $projectId)->with('message', 'Task Updated Successfully');
        }
        if (Auth::user()->hasRole('Responsable de projet')) {
            return redirect()->route('Responsable_de_projet.projects.show', $projectId)->with('message', 'Task Updated Successfully');
        }
        if (Auth::user()->hasRole('Responsable de projet et gestionnaire')) {
            return redirect()->route('Responsable_gestionnaire.projects.show', $projectId)->with('message', 'Task Updated Successfully');
        }
    }


    public function show($task_id)
    {
        $task = Tasks::where('id', $task_id)->first();

        return view('admin.tasks.show', compact('task'));
    }

    public function destroy(Tasks $task)
    {
        $team_tasks = team_tasks::where('task_id', $task->id)->get();
        foreach ($team_tasks as $team_task) {
            $team_task->delete();
        }
        $employees_tasks = employees_tasks::where('task_id', $task->id)->get();
        foreach ($employees_tasks as $employee_task) {
            $employee_task->delete();
        }

        $task->delete();
        return back()->with('message', 'task Deleted Successfully');
    }

    /**
     * Delete a single tasks_timer entry from pointage table.
     */
    public function destroyTimer($id)
    {
        $timer = tasks_timer::where('id', $id)
            ->where('entreprise_id', Auth::user()->entreprise_id)
            ->first();

        if (!$timer) {
            return redirect()->back()->with('error', 'Timer entry not found or you are not authorized to delete it.');
        }

        $timer->delete();

        return redirect()->back()->with('message', 'Timer entry deleted successfully.');
    }

    public function status(Tasks $task, Request $request, $status)
    {

        //Status = 0 --> Inactive
        if ($status == 0) {
            $task->update(['status' => 0]);
        }
        //Status = 1 --> Working
        if ($status == 1) {
            $task->update(['status' => 1]);
        }
        //Status = 2 --> Done
        if ($status == 2) {
            $task->update(['status' => 2]);
        }
        return back()->with('message', 'task Status Updated Successfully');
    }

    public function start(Request $request, $task_id, $employee_id)
    {

        $employee = Employees::where('id', $employee_id)->first();
        $employee_user = User::where('id', $employee->user_id)->first();

        $comment = $request->input('comment');

        $employee_user->is_loggedIn = 1;
        $employee_user->save();

        // Get the task details
        $task_details = employees_tasks::where('id', $task_id)
            ->where('employee_id', $employee_id)
            ->first();

        if (!$task_details) {
            return back()->with('alertmessage', 'Task not found for this employee.');
        }

        $task_name = $task_details->task_name;

        // Check if there's an existing timer for this task that hasn't stopped yet
        $existing_timer = tasks_timer::where('task_employee_id', $task_id)
            ->whereNull('end_date')
            ->first();

        if ($existing_timer) {
            return back()->with('alertmessage', 'A timer is already running for this task. Please stop the existing timer before starting a new one.');
        }

        // Check if the employee is a SuperAdmin (to allow multiple tasks)
        // We check the employee's user, not the current authenticated user
        $isEmployeeSuperAdmin = false;
        if ($employee_user) {
            $isEmployeeSuperAdmin = $employee_user->hasRole('SuperAdmin') || $employee_user->hasRole('super_admin');
        }

        // Check if there's any other task timer running for this employee
        // SuperAdmin employees can run multiple tasks, but regular employees are limited to one task at a time
        if (!$isEmployeeSuperAdmin) {
            $existing_task_timers = tasks_timer::where('employee_id', $employee_id)
                ->whereNull('end_date')
                ->get();

            if ($existing_task_timers->isNotEmpty()) {
                return back()->with('alertmessage', 'A Task is already running. Please stop it before starting a new one.');
            }
        }

        $project_id = Tasks::where('id', $task_details->task_id)->first()->project_id;

        // Start the timer for the task
        $task_timer = new tasks_timer;
        $task_timer->task_employee_id = $task_id;
        $task_timer->task_name = $task_name;
        $task_timer->task_id = $task_details->task_id;
        $task_timer->project_id = $project_id;
        $task_timer->employee_id = $employee_id;
        $task_timer->start_date = now();
        $task_timer->comment = $comment;
        $task_timer->entreprise_id = Auth::user()->entreprise_id;
        $task_timer->save();

        return back()->with('success', 'Task started successfully.');
    }


    public function pause($task, $employee_id)
    {
        $tasks = employees_tasks::where('id', $task)->where('employee_id', $employee_id)->first();
        $employee = Employees::where('id', $employee_id)->first();


        if (!$tasks) {
            return back()->with('error', 'Task not found.');
        }

        $task_timer = tasks_timer::where('task_employee_id', $tasks->id)->latest()->first();

        if ($task_timer && $task_timer->pause_date === null && $task_timer->end_date === null) {
            $task_timer->update(['pause_date' => now()]);
            $start = Carbon::parse($task_timer->start_date);
            $pause = Carbon::parse($task_timer->pause_date);
            $duration = $start->diffInMinutes($pause);
            $task_timer->duration = $duration;
            $task_timer->paused = 1;
            $task_timer->save();
            return back()->with('success', 'Task paused successfully.');
        } elseif ($task_timer && $task_timer->pause_date != null && $task_timer->resume_date != null && $task_timer->end_date === null) {
            $task_timer->update(['pause_date' => now()]);
            $resume = Carbon::parse($task_timer->resume_date);
            $pause = Carbon::parse($task_timer->pause_date);
            $duration1 = $resume->diffInMinutes($pause);
            $task_timer->duration = $task_timer->duration + $duration1;
            $task_timer->paused = 1;
            $task_timer->save();
            return back()->with('success', 'Task paused successfully.');
        } else {
            return back()->with('alertmessage', 'The timer was not initiated to pause it.');
        }
    }

    public function resume($task, $employee_id)
    {
        $tasks = employees_tasks::where('id', $task)->where('employee_id', $employee_id)->first();
        $employee = Employees::where('id', $employee_id)->first();


        if (!$tasks) {
            return back()->with('error', 'Task not found.');
        }

        $task_timer = tasks_timer::where('task_employee_id', $tasks->id)->latest()->first();

        if ($task_timer && $task_timer->pause_date != null && $task_timer->end_date === null) {
            $task_timer->update(['resume_date' => now()]);
            $task_timer->paused = 0;
            $task_timer->save();

            return back()->with('success', 'Task resumed successfully.');
        } else {
            return back()->with('alertmessage', 'The timer was not initiated to pause it.');
        }
    }



    public function stop($task, $employee_id)
    {
        $tasks = employees_tasks::where('id', $task)->where('employee_id', $employee_id)->first();
        $employee = Employees::where('id', $employee_id)->first();

        $work_days_repports = work_days_repports::where('employee_id', $employee->id)->where('month', date('m'))->where('year', date('Y'))->first();

        $employee_user = User::where('id', $employee->user_id)->first();

        // Check active tasks to determine if status should change to Off Work
        // If the employee has only 1 (or fewer) active tasks, they are stopping the last one, so set to Off Work.
        // Otherwise, they still have other tasks running, so keep them On Work.
        $active_tasks_count = tasks_timer::where('employee_id', $employee_id)
            ->whereNull('end_date')
            ->count();

        if ($active_tasks_count <= 1) {
            $employee_user->is_loggedIn = 0;
            $employee_user->save();
        }


        if (!$tasks) {
            return back()->with('error', 'Task not found.');
        }

        $task_timer = tasks_timer::where('task_employee_id', $tasks->id)->latest()->first();


        if ($task_timer && $task_timer->pause_date === null && $task_timer->end_date === null) {
            $task_timer->update(['end_date' => now()]);

            $start_date = Carbon::parse($task_timer->start_date);
            $end_date = Carbon::parse($task_timer->end_date);

            if ($start_date->diffInSeconds($end_date) < 30) {
                $task_timer->delete();
            } elseif ($start_date->diffInMinutes($end_date) < 5) {
                $add15min = new DateInterval('PT15M');
                $task_timer->end_date = clone $start_date; // Clone to avoid modifying original start_date
                $task_timer->end_date->add($add15min); // Add 15 minutes to the start date
                $task_timer->duration = 15;
                $cost_per_hour = $employee->cost_per_hour;
                $cost = $cost_per_hour * ((float) 15 / 60);
                $task_timer->cost = $cost;
                $task_timer->save();


            } elseif ($start_date->diffInMinutes($end_date) > ($employee->workHours_limit * 60)) {

                // Use minutes directly to support fractional hour limits (e.g. 8.5h = 510 minutes)
                $work_hours_minutes = (int) round($employee->workHours_limit * 60);
                $task_timer->end_date = (clone $start_date)->addMinutes($work_hours_minutes);
                $task_timer->duration = $work_hours_minutes;
                $cost_per_hour = $employee->cost_per_hour;
                $cost = $cost_per_hour * ((float) $employee->workHours_limit);
                $task_timer->cost = $cost;
                $task_timer->save();



            } else {
                // Calculate the duration of the task
                $duration = $start_date->diffInMinutes($end_date);
                $task_timer->duration = $duration;

                // Calculate the cost of the task
                $cost_per_hour = $employee->cost_per_hour;
                $cost = $cost_per_hour * ((float) $duration / 60);
                $task_timer->cost = $cost;
                $task_timer->save();


            }

            return back()->with('success', 'Task ended successfully.');

        } elseif ($task_timer && $task_timer->pause_date != null && $task_timer->end_date === null) {

            $task_timer->update(['end_date' => now()]);

            $start_date = Carbon::parse($task_timer->start_date);
            $pause_date = Carbon::parse($task_timer->pause_date);
            $resume_date = Carbon::parse($task_timer->resume_date);
            $end_date = Carbon::parse($task_timer->end_date);

            // Calculate total active time (excluding paused periods)
            $duration1 = $task_timer->duration;
            $duration2 = $resume_date->diffInMinutes($end_date); // Time after resume
            $taskduration = $duration1 + $duration2;

            if ($taskduration < 5) {
                // For very short tasks, set minimum duration to 15 minutes
                $add15min = new DateInterval('PT15M');
                $task_timer->end_date = clone $start_date;
                $task_timer->end_date->add($add15min);
                $task_timer->duration = 15;
                $cost_per_hour = $employee->cost_per_hour;
                $cost = $cost_per_hour * ((float) 15 / 60);
                $task_timer->cost = $cost;
            } elseif ($taskduration > ($employee->workHours_limit * 60)) {
                // Cap duration at work hours limit, supporting fractional hours
                $work_hours_minutes = (int) round($employee->workHours_limit * 60);
                $task_timer->end_date = (clone $start_date)->addMinutes($work_hours_minutes);
                $task_timer->duration = $work_hours_minutes;
                $cost_per_hour = $employee->cost_per_hour;
                $cost = $cost_per_hour * ((float) $employee->workHours_limit);
                $task_timer->cost = $cost;
            } else {
                // Normal case - use actual calculated duration
                $task_timer->duration = $taskduration;
                $cost_per_hour = $employee->cost_per_hour;
                $cost = $cost_per_hour * ((float) $taskduration / 60);
                $task_timer->cost = $cost;
            }
            $task_timer->save();

            return back()->with('success', 'Task ended successfully.');
        } else {
            return back()->with('alertmessage', 'The timer was not initiated to stop it.');
        }
    }

    public function checkTasksAndTime()
    {
        // Get the tasks that are still running
        $runningTasks = tasks_timer::whereNull('end_date')
            //->where('entreprise_id', 26)
            ->get();

        // If there are running tasks
        if (!$runningTasks->isEmpty()) {
            foreach ($runningTasks as $task) {
                $employee = Employees::where('id', $task->employee_id)->first();
                if (!$employee)
                    continue;

                $start_date = Carbon::parse($task->start_date);
                $current_time = now();

                // If task is paused, handle differently
                if ($task->pause_date !== null) {
                    $duration = $task->duration; // Keep existing duration for paused tasks
                } else {
                    $duration = $start_date->diffInMinutes($current_time);
                }

                // Get all completed tasks for today for this employee
                $today_completed_tasks = tasks_timer::where('employee_id', $task->employee_id)
                    ->whereDate('start_date', Carbon::today())
                    ->whereNotNull('end_date')
                    ->where('id', '!=', $task->id)
                    ->sum('duration');

                // Calculate total duration including current task
                $total_duration = $today_completed_tasks + $duration;

                // Convert work hours limit to minutes - ensure we handle decimals correctly
                $work_minutes_limit = (float) $employee->workHours_limit * 60;

                // If no limit is defined or set to 0, don't auto-stop the task
                if ($work_minutes_limit <= 0) {
                    continue;
                }

                // Only stop the task if total duration exceeds work hours limit
                if ($total_duration > $work_minutes_limit) {
                    $remaining_minutes = $work_minutes_limit - $today_completed_tasks;

                    if ($remaining_minutes <= 0) {
                        // No time left in the work day
                        $task->delete();
                    } else {
                        // Set the task duration to remaining time
                        $task->duration = $remaining_minutes;
                        $task->cost = $employee->cost_per_hour * ((float) $remaining_minutes / 60);
                        $task->end_date = $start_date->copy()->addMinutes($remaining_minutes);
                        $task->save();
                    }
                }
                // Don't stop the task if we haven't reached the limit yet
            }
        }
    }


    public function pointage()
    {
        $projects = Project::where('entreprise_id', Auth::user()->entreprise_id)
            ->where(function ($query) {
                $query->whereNull('AcceptedDevisProject')
                    ->orWhere('AcceptedDevisProject', '=', 1);
            })
            ->where('archived', 0)
            ->whereNull('parent_id')  // Exclude subprojects
            ->select('id', 'name', 'tva')  // Explicitly select the TVA field
            ->orderBy('name')
            ->get();
        $enterpriseId = Auth::user()->entreprise_id;
        $employees = employees::where('entreprise_id', $enterpriseId)->where('archived', 0)->orderBy('name')->get();
        $teams = Teams::where('entreprise_id', Auth::user()->entreprise_id)->get();
        
        // Filter tasks by project if project is selected in request
        $project_id = request()->query('project');
        $allTasks = Tasks::where('entreprise_id', Auth::user()->entreprise_id);
        if ($project_id != null && $project_id != '') {
            $allTasks = $allTasks->where('project_id', $project_id);
        }
        $allTasks = $allTasks->orderBy('title')->get();
        
        $tasks = Tasks::where('user_id', Auth::user()->id)->get();

        $employees_tasks = employees_tasks::where('entreprise_id', Auth::user()->entreprise_id)->get();
        $clients = Client::where('entreprise_id', Auth::user()->entreprise_id)
            ->where(function ($query) {
                $query->whereNull('AcceptedDevisClient')
                    ->orWhere('AcceptedDevisClient', '=', 1);
            })
            ->whereIn('id', function ($query) {
                $query->select('client_id')
                    ->from('projects')
                    ->where('entreprise_id', Auth::user()->entreprise_id)
                    ->where(function ($q) {
                        $q->whereNull('AcceptedDevisProject')
                            ->orWhere('AcceptedDevisProject', '=', 1);
                    })
                    ->where('archived', 0);
            })
            ->orderBy('name')
            ->get();

        $today = Carbon::today()->toDateString();

        // Get period invoices for today (any overlapping period)
        $periodInvoices = invoices_history::where('entreprise_id', Auth::user()->entreprise_id)
            ->where('type', 'Period')
            ->where(function ($query) use ($today) {
                $query->where('start_date', '<=', $today)
                    ->where('end_date', '>=', $today);
            })
            ->pluck('project_id')
            ->unique()
            ->values()
            ->toArray();

        // Get projects that have a period invoice exactly for today (start_date == end_date == $today)
        $projectsWithExactInvoices = invoices_history::where('entreprise_id', Auth::user()->entreprise_id)
            ->where('type', 'Period')
            ->whereDate('start_date', $today)
            ->whereDate('end_date', $today)
            ->pluck('project_id')
            ->map(function ($id) {
                return (int) $id;
            })
            ->unique()
            ->values()
            ->toArray();

        // Fetch tasks information for the selected start date
        $tasks_timer = tasks_timer::whereDate('start_date', $today)
            ->where('entreprise_id', Auth::user()->entreprise_id)
            ->orderBy('start_date', 'asc') // Order by start_date
            ->get()
            ->groupBy(function ($date) {
                return Carbon::parse($date->start_date)->format('l - Y-m-d'); // Group by date
            });

        // Get project IDs that have tasks_hours in the selected date range
        $projectsWithTasks = tasks_timer::whereDate('start_date', $today)
            ->where('entreprise_id', Auth::user()->entreprise_id)
            ->whereNotNull('project_id')
            ->distinct()
            ->pluck('project_id')
            ->toArray();

        return view('admin.tasks.pointage', compact(
            'employees_tasks',
            'projects',
            'employees',
            'teams',
            'tasks',
            'allTasks',
            'tasks_timer',
            'clients',
            'periodInvoices',
            'projectsWithTasks',
            'projectsWithExactInvoices'
        ));
    }


    public function filterPointageByDate(Request $request)
    {
        $projects = Project::where('entreprise_id', Auth::user()->entreprise_id)
            ->where(function ($query) {
                $query->whereNull('AcceptedDevisProject')
                    ->orWhere('AcceptedDevisProject', '=', 1);
            })
            ->where('archived', 0)
            ->whereNull('parent_id')  // Exclude subprojects
            ->select('id', 'name', 'tva')  // Explicitly select the TVA field
            ->orderBy('name')
            ->get();
        $enterpriseId = Auth::user()->entreprise_id;
        $employees = employees::where('entreprise_id', $enterpriseId)->where('archived', 0)->orderBy('name')->get();
        $teams = Teams::where('entreprise_id', Auth::user()->entreprise_id)->get();
        $allTasks = Tasks::where('entreprise_id', Auth::user()->entreprise_id)->orderBy('title')->get();
        $tasks = Tasks::where('user_id', Auth::user()->id)->get();
        $clients = Client::where('entreprise_id', Auth::user()->entreprise_id)
            ->where(function ($query) {
                $query->whereNull('AcceptedDevisClient')
                    ->orWhere('AcceptedDevisClient', '=', 1);
            })
            ->whereIn('id', function ($query) {
                $query->select('client_id')
                    ->from('projects')
                    ->where('entreprise_id', Auth::user()->entreprise_id)
                    ->where(function ($q) {
                        $q->whereNull('AcceptedDevisProject')
                            ->orWhere('AcceptedDevisProject', '=', 1);
                    })
                    ->where('archived', 0);
            })
            ->orderBy('name')
            ->get();

        $employees_tasks = employees_tasks::where('entreprise_id', Auth::user()->entreprise_id)->get();

        $startDate = Carbon::parse($request->input('start_date'))->toDateString();
        $endDate = Carbon::parse($request->input('end_date'))->toDateString();

        // Get period invoices for the date range (any overlapping period)
        $periodInvoices = invoices_history::where('entreprise_id', Auth::user()->entreprise_id)
            ->where('type', 'Period')
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function ($q) use ($startDate, $endDate) {
                        $q->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                    });
            })
            ->pluck('project_id')
            ->map(function ($id) {
                return (int) $id;
            })
            ->unique()
            ->values()
            ->toArray();

        // Get projects that have a period invoice exactly matching the selected range
        $projectsWithExactInvoices = invoices_history::where('entreprise_id', Auth::user()->entreprise_id)
            ->where('type', 'Period')
            ->whereDate('start_date', $startDate)
            ->whereDate('end_date', $endDate)
            ->pluck('project_id')
            ->map(function ($id) {
                return (int) $id;
            })
            ->unique()
            ->values()
            ->toArray();


        if ($request->input('start_date') != null && $request->input('end_date') != null) {
            $tasks_timer = tasks_timer::where('start_date', '>=', $startDate)
                ->where('entreprise_id', Auth::user()->entreprise_id)
                ->where(function ($query) use ($endDate) {
                    // Add one day to the endDate for inclusive comparison
                    $inclusiveEndDate = Carbon::parse($endDate)->addDay()->format('Y-m-d');
                    $query->where(function ($q) use ($inclusiveEndDate) {
                        $q->where('end_date', '<', $inclusiveEndDate)
                            ->whereNotNull('end_date')
                            ->orWhereNull('end_date'); // Include tasks that are still working
                    });
                })
                ->orderBy('start_date', 'asc') // Order by start_date
                ->get()
                ->groupBy(function ($date) {
                    return Carbon::parse($date->start_date)->format('l - Y-m-d'); // Group by date
                });
            
            // Get project IDs that have tasks_hours in the selected date range
            $projectsWithTasks = tasks_timer::where('start_date', '>=', $startDate)
                ->where('entreprise_id', Auth::user()->entreprise_id)
                ->where(function ($query) use ($endDate) {
                    $inclusiveEndDate = Carbon::parse($endDate)->addDay()->format('Y-m-d');
                    $query->where(function ($q) use ($inclusiveEndDate) {
                        $q->where('end_date', '<', $inclusiveEndDate)
                            ->whereNotNull('end_date')
                            ->orWhereNull('end_date');
                    });
                })
                ->whereNotNull('project_id')
                ->distinct()
                ->pluck('project_id')
                ->toArray();
        } else {
            $tasks_timer = tasks_timer::whereDate('start_date', $startDate)
                ->where('entreprise_id', Auth::user()->entreprise_id)
                ->orderBy('start_date', 'asc') // Order by start_date
                ->get()
                ->groupBy(function ($date) {
                    return Carbon::parse($date->start_date)->format('l - Y-m-d'); // Group by date
                });
            
            // Get project IDs that have tasks_hours in the selected date range
            $projectsWithTasks = tasks_timer::whereDate('start_date', $startDate)
                ->where('entreprise_id', Auth::user()->entreprise_id)
                ->whereNotNull('project_id')
                ->distinct()
                ->pluck('project_id')
                ->toArray();
        }

        return view('admin.tasks.pointage', compact(
            'employees_tasks',
            'projects',
            'employees',
            'teams',
            'tasks',
            'allTasks',
            'tasks_timer',
            'clients',
            'periodInvoices',
            'projectsWithTasks',
            'projectsWithExactInvoices'
        ));
    }

    public function filterPointageTable(Request $request)
    {
        $projects = Project::where('entreprise_id', Auth::user()->entreprise_id)
            ->where(function ($query) {
                $query->whereNull('AcceptedDevisProject')
                    ->orWhere('AcceptedDevisProject', '=', 1);
            })
            ->where('archived', 0)
            ->whereNull('parent_id')  // Exclude subprojects
            ->select('id', 'name', 'tva')  // Explicitly select the TVA field
            ->orderBy('name')
            ->get();
        $enterpriseId = Auth::user()->entreprise_id;
        $employees = employees::where('entreprise_id', $enterpriseId)->where('archived', 0)->orderBy('name')->get();
        $teams = Teams::where('entreprise_id', Auth::user()->entreprise_id)->get();
        
        // Filter tasks by project if project is selected
        $project_id = $request->query('project');
        $allTasks = Tasks::where('entreprise_id', Auth::user()->entreprise_id);
        if ($project_id != null && $project_id != '') {
            $allTasks = $allTasks->where('project_id', $project_id);
        }
        $allTasks = $allTasks->orderBy('title')->get();
        
        $tasks = Tasks::where('user_id', Auth::user()->id)->get();
        $clients = Client::where('entreprise_id', Auth::user()->entreprise_id)
            ->where(function ($query) {
                $query->whereNull('AcceptedDevisClient')
                    ->orWhere('AcceptedDevisClient', '=', 1);
            })
            ->whereIn('id', function ($query) {
                $query->select('client_id')
                    ->from('projects')
                    ->where('entreprise_id', Auth::user()->entreprise_id)
                    ->where(function ($q) {
                        $q->whereNull('AcceptedDevisProject')
                            ->orWhere('AcceptedDevisProject', '=', 1);
                    })
                    ->where('archived', 0);
            })
            ->orderBy('name')
            ->get();

        $employees_tasks = employees_tasks::where('entreprise_id', Auth::user()->entreprise_id)->get();

        $startDate = Carbon::parse($request->query('start_date'))->toDateString();
        $endDate = Carbon::parse($request->query('end_date'))->toDateString();
        $employee_id = $request->query('employee');
        $project_id = $request->query('project');
        $taskIds = (array) $request->query('task');
        $client_id = $request->query('client');
        $project_type = $request->query('project_type');

        // Get period invoices for the date range (any overlapping period)
        $periodInvoices = invoices_history::where('entreprise_id', Auth::user()->entreprise_id)
            ->where('type', 'Period')
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function ($q) use ($startDate, $endDate) {
                        $q->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                    });
            })
            ->pluck('project_id')
            ->map(function ($id) {
                return (int) $id;
            })
            ->unique()
            ->values()
            ->toArray();

        // Get projects that have a period invoice exactly matching the selected range
        $projectsWithExactInvoices = invoices_history::where('entreprise_id', Auth::user()->entreprise_id)
            ->where('type', 'Period')
            ->whereDate('start_date', $startDate)
            ->whereDate('end_date', $endDate)
            ->pluck('project_id')
            ->map(function ($id) {
                return (int) $id;
            })
            ->unique()
            ->values()
            ->toArray();

        // Base query builder
        $query = tasks_timer::where('entreprise_id', Auth::user()->entreprise_id);

        // Add date range conditions
        if ($request->input('start_date') != null && $request->input('end_date') != null) {
            $query->where('start_date', '>=', $startDate)
                ->where(function ($query) use ($endDate) {
                    $inclusiveEndDate = Carbon::parse($endDate)->addDay()->format('Y-m-d');
                    $query->where(function ($q) use ($inclusiveEndDate) {
                        $q->where('end_date', '<', $inclusiveEndDate)
                            ->whereNotNull('end_date')
                            ->orWhereNull('end_date'); // Include tasks that are still working
                    });
                });
        } else {
            $query->whereDate('start_date', $startDate);
        }

        // Add client filter if provided
        if ($client_id != null) {
            $projectIds = Project::where('client_id', $client_id)->pluck('id');
            $query->whereIn('project_id', $projectIds);
        }

        // Add project type filter if provided
        if ($project_type != null) {
            if ($project_type == 'regie') {
                $projectIds = Project::where('regie', 1)->pluck('id');
                $query->whereIn('project_id', $projectIds);
            } elseif ($project_type == 'devis') {
                $projectIds = Project::where('AcceptedDevisProject', 1)->pluck('id');
                $query->whereIn('project_id', $projectIds);
            }
        }

        // Add other filters
        if ($employee_id != null) {
            $query->where('employee_id', $employee_id);
        }
        if ($project_id != null) {
            $query->where('project_id', $project_id);
        }

        // Filter by multiple tasks if provided
        // If "all" is in the array, don't filter by tasks (show all tasks)
        if (!empty($taskIds)) {
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
            // Filter out empty values and "all" option
            $flatTaskIds = array_filter($flatTaskIds, function ($v) {
                return $v !== '' && $v !== null && $v !== 'all';
            });
            // Only apply task filter if there are specific tasks selected (not "all")
            if (!empty($flatTaskIds)) {
                $query->whereIn('task_id', $flatTaskIds);
            }
        }

        // Execute query and group results
        $tasks_timer = $query->orderBy('start_date', 'asc')
            ->get()
            ->groupBy(function ($date) {
                return Carbon::parse($date->start_date)->format('l - Y-m-d');
            });

        // Get project IDs that have tasks_hours in the selected date range
        $projectsWithTasksQuery = tasks_timer::where('entreprise_id', Auth::user()->entreprise_id);
        
        if ($request->input('start_date') != null && $request->input('end_date') != null) {
            $projectsWithTasksQuery->where('start_date', '>=', $startDate)
                ->where(function ($query) use ($endDate) {
                    $inclusiveEndDate = Carbon::parse($endDate)->addDay()->format('Y-m-d');
                    $query->where(function ($q) use ($inclusiveEndDate) {
                        $q->where('end_date', '<', $inclusiveEndDate)
                            ->whereNotNull('end_date')
                            ->orWhereNull('end_date');
                    });
                });
        } else {
            $projectsWithTasksQuery->whereDate('start_date', $startDate);
        }
        
        $projectsWithTasks = $projectsWithTasksQuery
            ->whereNotNull('project_id')
            ->distinct()
            ->pluck('project_id')
            ->toArray();

        return view('admin.tasks.pointage', compact(
            'employees_tasks',
            'projects',
            'employees',
            'teams',
            'tasks',
            'allTasks',
            'tasks_timer',
            'clients',
            'periodInvoices',
            'projectsWithTasks',
            'projectsWithExactInvoices'
        ));
    }

    public function get_timer_state()
    {
        $user = Auth::user();
        $employee = App\Models\employees::where('user_id', $user->id)->first();
        $tasksTimer = App\Models\tasks_timer::where('employee_id', $employee->id)
            ->whereDate('start_date', '=', now()->toDateString())
            ->whereNull('end_date')
            ->first();

        return response()->json(['tasksTimer' => $tasksTimer]);
    }

    public function getTasksInfo($startDate)
    {
        // Convert the start date to a proper format, if needed 
        $startDate = Carbon::parse($startDate)->toDateString();

        // Fetch tasks information for the selected start date
        $tasks_timer = tasks_timer::whereDate('start_date', $startDate)
            ->where('entreprise_id', Auth::user()->entreprise_id)
            ->orderBy('start_date', 'asc') // Order by start_date
            ->get();

        // Return the tasks information as JSON response
        return response()->json($tasks_timer);
    }

    public function getTasksInfoInterval($startDate, $endDate)
    {
        // Convert the start and end dates to a proper format, if needed 
        $startDate = Carbon::parse($startDate)->toDateString();
        $endDate = Carbon::parse($endDate)->toDateString();

        // Fetch tasks information for the selected start and end dates
        $tasks_timer = tasks_timer::where('start_date', '>=', $startDate)
            ->where('entreprise_id', Auth::user()->entreprise_id)
            ->where(function ($query) use ($endDate) {
                // Add one day to the endDate for inclusive comparison
                $inclusiveEndDate = Carbon::parse($endDate)->addDay()->format('Y-m-d');
                $query->where(function ($q) use ($inclusiveEndDate) {
                    $q->where('end_date', '<', $inclusiveEndDate)
                        ->whereNotNull('end_date')
                        ->orWhereNull('end_date'); // Include tasks that are still working
                });
            })
            ->orderBy('start_date', 'asc') // Order by start_date
            ->get();

        // Return the tasks information as JSON response
        return response()->json($tasks_timer);
    }

    public function getTasksInfoIntervalEmployee($startDate, $endDate, $employee)
    {
        // Convert the start and end dates to a proper format, if needed 
        $startDate = Carbon::parse($startDate)->toDateString();
        $endDate = Carbon::parse($endDate)->toDateString();

        // Fetch tasks information for the selected start and end dates
        $tasks_timer = tasks_timer::where('employee_id', $employee)
            ->where('start_date', '>=', $startDate)
            ->where(function ($query) use ($endDate) {
                // Add one day to the endDate for inclusive comparison
                $inclusiveEndDate = Carbon::parse($endDate)->addDay()->format('Y-m-d');
                $query->where('end_date', '<', $endDate)
                    ->whereNotNull('end_date');
            })
            ->orderBy('start_date', 'asc') // Order by start_date
            ->get();

        // Return the tasks information as JSON response
        return response()->json($tasks_timer);
    }
    public function getProjectName($taskID)
    {
        $project = Tasks::where('id', $taskID)->first();
        $project_cost = Project::where('id', $project->project_id)->first();
        $projectData = [
            'id' => $project->project_id,
            'name' => $project->project_name,
            'cost_per_hour' => $project_cost->cost_per_hour
        ];
        return response()->json($projectData);
    }

    public function getEmployeeName($employeeId)
    {
        $employee = Employees::where('id', $employeeId)->first();
        $employeeData = [
            'id' => $employee->id,
            'name' => $employee->name,
            'cost_per_hour' => $employee->cost_per_hour
        ];
        return response()->json($employeeData);
    }


    public function Markpointage()
    {

        $projects = Project::where('entreprise_id', Auth::user()->entreprise_id)->where(function ($query) {
            $query->whereNull('AcceptedDevisProject')
                ->orWhere('AcceptedDevisProject', '=', 1);
        })
            ->where('archived', 0)
            ->get();
        $tasks = Tasks::where('user_id', Auth::user()->id)->orderBy('title', 'asc')->get();
        $employees = Employees::where('entreprise_id', auth()->user()->entreprise_id)->where('archived', 0)->orderBy('name', 'asc')->get();
        $clients = Client::where('entreprise_id', Auth::user()->entreprise_id)->where(function ($query) {
            $query->whereNull('AcceptedDevisClient')
                ->orWhere('AcceptedDevisClient', '=', 1);
        })->get();
        $teams = Teams::where('entreprise_id', Auth::user()->entreprise_id)->get();


        $employee_id = Employees::where('user_id', Auth::user()->id)->first()->id;
        $employees_tasks = employees_tasks::where('employee_id', $employee_id)->get();

        return view('admin.tasks.markPointage', compact('employees_tasks', 'employee_id', 'projects', 'tasks', 'employees', 'clients', 'teams'));
    }

    public function updateDuration(Request $request, $task_id)
    {
        // Retrieve the task
        $task = tasks_timer::where('id', $task_id)->first();
        $employee = Employees::where('id', $task->employee_id)->first();

        // Extract hours and minutes from the request string
        list($hours, $minutes) = sscanf($request->duration, "%d:%d");
        // Convert hours to minutes and add to minutes
        $durationInMinutes = $hours * 60 + $minutes;

        // Update task duration and cost
        $task->duration = $durationInMinutes;
        $task->cost = $employee->cost_per_hour * ($durationInMinutes / 60);

        // Calculate and update the end date
        $startDate = Carbon::createFromFormat('Y-m-d H:i:s', $task->start_date);
        $endDate = $startDate->copy()->addMinutes($durationInMinutes);
        $task->end_date = $endDate->format('Y-m-d H:i:s');

        // Save the updated task
        $task->save();

        // Return success response
        return response()->json(['success' => 'Duration updated successfully.']);
    }


    public function updateField(Request $request, $field, $id)
    {
        try {
            // Validate the request (allow clearing comments)
            $rules = [];
            if ($field === 'comment') {
                $rules[$field] = 'nullable|string';
            } else {
                $rules[$field] = 'required|string';
            }
            $request->validate($rules);

            // Find the task by ID
            $task = tasks_timer::where('id', $id)->first();

            if (!$task) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Task not found'
                ], 404);
            }

            // If updating start_date, check for conflicts with previous tasks
            if ($field === 'start_date') {
                $newStartDate = Carbon::parse($request->start_date);
                $taskDate = Carbon::parse($task->start_date)->startOfDay();

                // Find previous task for the same employee on the same day
                $previousTask = tasks_timer::where('employee_id', $task->employee_id)
                    ->where('id', '!=', $task->id)
                    ->whereDate('start_date', $taskDate)
                    ->where('start_date', '<', $task->start_date)
                    ->orderBy('start_date', 'desc')
                    ->first();

                if ($previousTask && $previousTask->end_date) {
                    $previousEndDate = Carbon::parse($previousTask->end_date);
                    if ($newStartDate->lt($previousEndDate)) {
                        return response()->json([
                            'status' => 'error',
                            'message' => 'New start date cannot be before the end date of the previous task'
                        ], 422);
                    }
                }
            }

            // Update the specified field (normalize empty comment to null)
            $value = $request->$field;
            if ($field === 'comment' && is_string($value) && trim($value) === '') {
                $value = null;
            }
            $task->$field = $value;

            // If start_date or end_date is being updated, calculate and update duration
            if ($field === 'start_date' || $field === 'end_date') {
                // Make sure both dates exist before calculating duration
                if ($task->start_date && $task->end_date) {
                    $startDate = Carbon::parse($task->start_date);
                    $endDate = Carbon::parse($task->end_date);

                    // Calculate duration in minutes
                    $durationInMinutes = $endDate->diffInMinutes($startDate);

                    // Update the duration
                    $task->duration = $durationInMinutes;
                }
            }

            $task->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Field updated successfully',
                'data' => [
                    'id' => $task->id,
                    $field => $task->$field,
                    'duration' => $task->duration
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update the value: ' . $e->getMessage()
            ], 500);
        }
    }


    public function downloadTasksRepports()
    {
        // Increase limits a bit to better handle larger reports
        // (consider also raising limits in php.ini for production if needed)
        @ini_set('max_execution_time', 300);
        @ini_set('memory_limit', '512M');

        $entrepriseId = auth()->user()->entreprise_id;

        // Only select the columns we actually need in the PDF
        $tasks = Tasks::where('entreprise_id', $entrepriseId)
            ->orderBy('project_id', 'asc')
            ->select('id', 'title', 'project_name', 'start', 'end', 'description')
            ->get();

        // If there are no tasks, return a very small PDF instead of doing heavy work
        $entreprise = Entreprise::where('id', $entrepriseId)->first();
        if ($tasks->isEmpty()) {
            $pdf = PDF::loadView('admin.tasks.downloadRepports', [
                'tasks' => $tasks,
                'entreprise' => $entreprise,
                'employeesByTask' => collect(),
                'teamsByTask' => collect(),
            ]);
            return $pdf->download('tasksRepports.pdf');
        }

        $taskIds = $tasks->pluck('id');

        // Preload employees and teams for all tasks in a few queries instead of inside the Blade loop
        $employeesByTask = employees_tasks::with('employee:id,name')
            ->whereIn('task_id', $taskIds)
            ->get()
            ->groupBy('task_id');

        $teamsByTask = team_tasks::with('team:id,name')
            ->whereIn('task_id', $taskIds)
            ->get()
            ->groupBy('task_id');

        $pdf = PDF::loadView('admin.tasks.downloadRepports', [
            'tasks' => $tasks,
            'entreprise' => $entreprise,
            'employeesByTask' => $employeesByTask,
            'teamsByTask' => $teamsByTask,
        ])->setPaper('a4', 'landscape');

        return $pdf->download('tasksRepports.pdf');
    }

    public function downloadPointageRepports($startDate, $endDate)
    {
        $startDate = Carbon::createFromFormat('Y-m-d', $startDate)->startOfDay();
        $endDate = Carbon::createFromFormat('Y-m-d', $endDate)->endOfDay();

        $projectId = request('projectId');
        $employeeId = request('employeeId');
        $clientId = request('clientId');
        $taskIds = request('taskIds');
        $fileName = request('fileName', 'report');

        $query = tasks_timer::where('entreprise_id', Auth::user()->entreprise_id)
            ->whereBetween('start_date', [$startDate, $endDate]);

        if ($projectId) {
            $query->where('project_id', $projectId);
        }

        if ($employeeId) {
            $query->where('employee_id', $employeeId);
        }

        if ($clientId) {
            $projectIds = Project::where('client_id', $clientId)->pluck('id');
            $query->whereIn('project_id', $projectIds);
        }

        if ($taskIds) {
            $taskIds = $this->normalizeIds($taskIds);
            if (!empty($taskIds)) {
                $query->whereIn('task_id', $taskIds);
            }
        }

        $tasks_timer = $query->orderBy('start_date')->get();
        $entreprise = Entreprise::find(Auth::user()->entreprise_id);
        $project = $projectId ? Project::find($projectId) : null;

        // Detect and filter out problematic tasks (e.g., missing project or cost_per_hour)
        $skippedTasks = [];
        $filteredTasks = $tasks_timer->filter(function ($task) use (&$skippedTasks) {
            $projectId = Tasks::where('id', $task->task_id)->value('project_id');
            $employeeName = Employees::where('id', $task->employee_id)->value('name');

            $taskInfo = [
                'task_id'       => $task->task_id,
                'task_name'     => $task->task_name ?? ('Task #' . $task->task_id),
                'project_id'    => $projectId,
                'project_name'  => null,
                'employee_id'   => $task->employee_id,
                'employee_name' => $employeeName,
                'start_date'    => $task->start_date,
                'end_date'      => $task->end_date,
                'duration'      => $task->duration,
            ];

            if (!$projectId) {
                $skippedTasks[] = $taskInfo;
                return false;
            }

            $projectForTask = Project::find($projectId);
            $taskInfo['project_name'] = $projectForTask ? $projectForTask->name : null;

            if (!$projectForTask || !isset($projectForTask->cost_per_hour)) {
                $skippedTasks[] = $taskInfo;
                return false;
            }

            return true;
        })->values();

        $tasks_timer = $filteredTasks;

        $pdf = PDF::loadView('admin.tasks.downloadPointageRepports', compact('tasks_timer', 'entreprise', 'startDate', 'endDate', 'project'));

        $response = $pdf->download($fileName . '.pdf');

        if (!empty($skippedTasks)) {
            // Encode list of skipped tasks (with details) into a response header for the frontend
            $encoded = base64_encode(json_encode($skippedTasks));
            $response->headers->set('X-Skipped-Tasks', $encoded);
        }

        return $response;
    }

    public function CustomerdownloadPointageRepports($startDate, $endDate)
    {
        try {
            $startDate = Carbon::createFromFormat('Y-m-d', $startDate)->startOfDay();
            $endDate = Carbon::createFromFormat('Y-m-d', $endDate)->endOfDay();

            $projectId = request('projectId');
            $employeeId = request('employeeId');
            $clientId = request('clientId');
            $taskIds = request('taskIds');
            $fileName = request('fileName', 'report');

            $query = tasks_timer::where('entreprise_id', Auth::user()->entreprise_id)
                ->whereBetween('start_date', [$startDate, $endDate]);

            if ($projectId) {
                $query->where('project_id', $projectId);
            }

            if ($employeeId) {
                $query->where('employee_id', $employeeId);
            }

            if ($clientId) {
                $projectIds = Project::where('client_id', $clientId)->pluck('id');
                $query->whereIn('project_id', $projectIds);
            }

            if ($taskIds) {
                $taskIds = $this->normalizeIds($taskIds);
                if (!empty($taskIds)) {
                    $query->whereIn('task_id', $taskIds);
                }
            }

            $tasks_timer = $query->orderBy('start_date')->get();
            $entreprise = Entreprise::find(Auth::user()->entreprise_id);
            $project = $projectId ? Project::find($projectId) : null;

            // Detect and filter out problematic tasks for customer PDF
            $skippedTasks = [];
            $filteredTasks = $tasks_timer->filter(function ($task) use (&$skippedTasks) {
                $projectIdForTask = Tasks::where('id', $task->task_id)->value('project_id');
                $employeeName = Employees::where('id', $task->employee_id)->value('name');

                $taskInfo = [
                    'task_id'       => $task->task_id,
                    'task_name'     => $task->task_name ?? ('Task #' . $task->task_id),
                    'project_id'    => $projectIdForTask,
                    'project_name'  => null,
                    'employee_id'   => $task->employee_id,
                    'employee_name' => $employeeName,
                    'start_date'    => $task->start_date,
                    'end_date'      => $task->end_date,
                    'duration'      => $task->duration,
                ];

                if (!$projectIdForTask) {
                    $skippedTasks[] = $taskInfo;
                    return false;
                }

                $projectForTask = Project::find($projectIdForTask);
                $taskInfo['project_name'] = $projectForTask ? $projectForTask->name : null;

                if (!$projectForTask || !isset($projectForTask->cost_per_hour)) {
                    $skippedTasks[] = $taskInfo;
                    return false;
                }

                return true;
            })->values();

            $tasks_timer = $filteredTasks;

            // Ensure the image path is valid
            $imagePath = null;
            if ($entreprise && $entreprise->image) {
                try {
                    $imagePath = Storage::disk('do_spaces')->url($entreprise->image);
                } catch (\Exception $e) {
                    \Log::error('Error getting image path: ' . $e->getMessage());
                }
            }

            $pdf = PDF::loadView('admin.tasks.CustomerdownloadPointageRepports', [
                'tasks_timer' => $tasks_timer,
                'entreprise' => $entreprise,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'project' => $project,
                'imagePath' => $imagePath
            ]);

            // Ensure temp directory exists
            $dir = storage_path('app/public/temp');
            if (!file_exists($dir)) {
                mkdir($dir, 0775, true);
            }

            // Save PDF to temporary storage
            $pdfPath = $dir . '/customer_report_' . time() . '.pdf';
            $pdf->save($pdfPath);

            // Get project contacts if project is selected
            $projectContacts = [];
            if ($project) {
                $projectContacts = $project->contacts()->pluck('email')->toArray();
            }

            return view('admin.tasks.customer-preview', [
                'pdfUrl' => asset('storage/temp/customer_report_' . time() . '.pdf'),
                'startDate' => $startDate->format('Y-m-d'),
                'endDate' => $endDate->format('Y-m-d'),
                'projectId' => $projectId,
                'employeeId' => $employeeId,
                'taskIds' => $taskIds,
                'clientId' => $clientId,
                'projectContacts' => $projectContacts,
                'projectName' => $project ? $project->name : '',
                'skippedTasks' => $skippedTasks,
            ]);
        } catch (\Exception $e) {
            \Log::error('PDF Generation Error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to generate PDF: ' . $e->getMessage()], 500);
        }
    }

    public function downloadOneDayRepports(Request $request, $startDate)
    {
        $startDate = Carbon::createFromFormat('Y-m-d', $startDate)->startOfDay();
        $endDate = $startDate->copy()->endOfDay();

        $projectId = request('projectId');
        $employeeId = request('employeeId');
        $clientId = request('clientId');
        $taskIds = request('taskIds');
        $fileName = request('fileName', 'report');

        $query = tasks_timer::where('entreprise_id', Auth::user()->entreprise_id)
            ->whereBetween('start_date', [$startDate, $endDate]);

        if ($projectId) {
            $query->where('project_id', $projectId);
        }

        if ($employeeId) {
            $query->where('employee_id', $employeeId);
        }

        if ($clientId) {
            $projectIds = Project::where('client_id', $clientId)->pluck('id');
            $query->whereIn('project_id', $projectIds);
        }

        if ($taskIds) {
            $taskIds = $this->normalizeIds($taskIds);
            if (!empty($taskIds)) {
                $query->whereIn('task_id', $taskIds);
            }
        }

        $tasks_timer = $query->orderBy('start_date')->get();
        $entreprise = Entreprise::find(Auth::user()->entreprise_id);
        $project = $projectId ? Project::find($projectId) : null;

        // Detect and filter out problematic tasks
        $skippedTasks = [];
        $filteredTasks = $tasks_timer->filter(function ($task) use (&$skippedTasks) {
            $projectId = Tasks::where('id', $task->task_id)->value('project_id');
            $employeeName = Employees::where('id', $task->employee_id)->value('name');

            $taskInfo = [
                'task_id'       => $task->task_id,
                'task_name'     => $task->task_name ?? ('Task #' . $task->task_id),
                'project_id'    => $projectId,
                'project_name'  => null,
                'employee_id'   => $task->employee_id,
                'employee_name' => $employeeName,
                'start_date'    => $task->start_date,
                'end_date'      => $task->end_date,
                'duration'      => $task->duration,
            ];

            if (!$projectId) {
                $skippedTasks[] = $taskInfo;
                return false;
            }

            $projectForTask = Project::find($projectId);
            $taskInfo['project_name'] = $projectForTask ? $projectForTask->name : null;

            if (!$projectForTask || !isset($projectForTask->cost_per_hour)) {
                $skippedTasks[] = $taskInfo;
                return false;
            }

            return true;
        })->values();

        $tasks_timer = $filteredTasks;

        $pdf = PDF::loadView('admin.tasks.downloadOneDayPointageRepports', compact('tasks_timer', 'entreprise', 'startDate', 'project'));

        $response = $pdf->download($fileName . '.pdf');

        if (!empty($skippedTasks)) {
            $encoded = base64_encode(json_encode($skippedTasks));
            $response->headers->set('X-Skipped-Tasks', $encoded);
        }

        return $response;
    }

    public function CustomerdownloadOneDayRepports(Request $request, $startDate)
    {
        $startDate = Carbon::createFromFormat('Y-m-d', $startDate)->startOfDay();
        $endDate = $startDate->copy()->endOfDay();

        $projectId = request('projectId');
        $employeeId = request('employeeId');
        $clientId = request('clientId');
        $taskIds = request('taskIds');
        $fileName = request('fileName', 'report');

        $query = tasks_timer::where('entreprise_id', Auth::user()->entreprise_id)
            ->whereBetween('start_date', [$startDate, $endDate]);

        if ($projectId) {
            $query->where('project_id', $projectId);
        }

        if ($employeeId) {
            $query->where('employee_id', $employeeId);
        }

        if ($clientId) {
            $projectIds = Project::where('client_id', $clientId)->pluck('id');
            $query->whereIn('project_id', $projectIds);
        }

        if ($taskIds) {
            $taskIds = $this->normalizeIds($taskIds);
            if (!empty($taskIds)) {
                $query->whereIn('task_id', $taskIds);
            }
        }

        $tasks_timer = $query->orderBy('start_date')->get();
        $entreprise = Entreprise::find(Auth::user()->entreprise_id);
        $project = $projectId ? Project::find($projectId) : null;

        // Detect and filter out problematic tasks for customer one-day PDF
        $skippedTasks = [];
        $filteredTasks = $tasks_timer->filter(function ($task) use (&$skippedTasks) {
            $projectIdForTask = Tasks::where('id', $task->task_id)->value('project_id');
            $employeeName = Employees::where('id', $task->employee_id)->value('name');

            $taskInfo = [
                'task_id'       => $task->task_id,
                'task_name'     => $task->task_name ?? ('Task #' . $task->task_id),
                'project_id'    => $projectIdForTask,
                'project_name'  => null,
                'employee_id'   => $task->employee_id,
                'employee_name' => $employeeName,
                'start_date'    => $task->start_date,
                'end_date'      => $task->end_date,
                'duration'      => $task->duration,
            ];

            if (!$projectIdForTask) {
                $skippedTasks[] = $taskInfo;
                return false;
            }

            $projectForTask = Project::find($projectIdForTask);
            $taskInfo['project_name'] = $projectForTask ? $projectForTask->name : null;

            if (!$projectForTask || !isset($projectForTask->cost_per_hour)) {
                $skippedTasks[] = $taskInfo;
                return false;
            }

            return true;
        })->values();

        $tasks_timer = $filteredTasks;

        $pdf = PDF::loadView('admin.tasks.CustomerdownloadOneDayPointageRepports', [
            'tasks_timer' => $tasks_timer,
            'entreprise' => $entreprise,
            'startDate' => $startDate,
            'project' => $project
        ]);

        // Ensure temp directory exists
        $dir = storage_path('app/public/temp');
        if (!file_exists($dir)) {
            mkdir($dir, 0775, true);
        }

        // Save PDF to temporary storage
        $pdfPath = $dir . '/customer_report_' . time() . '.pdf';
        $pdf->save($pdfPath);

        // Get project contacts if project is selected
        $projectContacts = [];
        if ($project) {
            $projectContacts = $project->contacts()->pluck('email')->toArray();
        }

        return view('admin.tasks.customer-preview', [
            'pdfUrl' => asset('storage/temp/customer_report_' . time() . '.pdf'),
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $startDate->format('Y-m-d'),
            'projectId' => $projectId,
            'employeeId' => $employeeId,
            'taskIds' => $taskIds,
            'clientId' => $clientId,
            'projectContacts' => $projectContacts,
            'projectName' => $project ? $project->name : '',
            'skippedTasks' => $skippedTasks,
        ]);
    }

    public function durationInfos($employee_id, $task_id)
    {
        $tasks = tasks_timer::where('employee_id', $employee_id)->where('task_id', $task_id)->whereNotNull('end_date')->get();
        $projects = Project::where('entreprise_id', Auth::user()->entreprise_id)
            ->where(function ($query) {
                $query->whereNull('AcceptedDevisProject')
                    ->orWhere('AcceptedDevisProject', '=', 1);
            })
            ->where('archived', 0)
            ->orderBy('client_id')  // This will group projects by client
            ->orderBy('name')       // This will sort projects by name within each client group
            ->get();
        $employees = Employees::where('entreprise_id', auth()->user()->entreprise_id)->where('archived', 0)->orderBy('name', 'asc')->get();
        return view('admin.tasks.showDurationInfos', compact('tasks', 'projects', 'employees'));
    }

    /**
     * Get tasks for a specific project
     */
    public function getProjectTasks(Project $project)
    {
        $tasks = Tasks::where('project_id', $project->id)
            ->select('id', 'title as name')
            ->get();

        return response()->json($tasks);
    }

    /**
     * Move duration from one task to another
     */
    public function moveDuration(Request $request)
    {
        try {
            DB::beginTransaction();

            // Validate the request
            $validated = $request->validate([
                'original_task_id' => 'required',
                'new_task_id' => 'required|exists:tasks,id',
                'employee_id' => 'required|exists:employees,id',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after:start_date',
                'duration' => 'required|integer|min:1'
            ]);

            // Get the original timer entry
            $originalTimer = tasks_timer::find($validated['original_task_id']);

            if (!$originalTimer) {
                throw new \Exception('Original timer entry not found');
            }

            // Get the new task
            $newTask = Tasks::find($validated['new_task_id']);
            if (!$newTask) {
                throw new \Exception('New task not found');
            }

            // Get the employee
            $employee = Employees::find($validated['employee_id']);
            if (!$employee) {
                throw new \Exception('Employee not found');
            }

            // Calculate cost
            $duration_in_hours = $validated['duration'] / 60;
            $new_cost = $employee->cost_per_hour * $duration_in_hours;

            // Get or create employee_task record
            $employeeTask = employees_tasks::firstOrCreate(
                [
                    'task_id' => $validated['new_task_id'],
                    'employee_id' => $validated['employee_id']
                ],
                [
                    'task_name' => $newTask->title,
                    'employee_name' => $this->getEmployeeDisplayName($employee),
                    'entreprise_id' => auth()->user()->entreprise_id
                ]
            );

            // Create new timer entry with all required fields
            $newTimer = new tasks_timer();
            $newTimer->task_id = $validated['new_task_id'];
            $newTimer->task_name = $newTask->title;
            $newTimer->task_employee_id = $employeeTask->id;
            $newTimer->project_id = $newTask->project_id;
            $newTimer->employee_id = $validated['employee_id'];
            $newTimer->start_date = $validated['start_date'];
            $newTimer->end_date = $validated['end_date'];
            $newTimer->duration = $validated['duration'];
            $newTimer->cost = $new_cost;
            $newTimer->entreprise_id = auth()->user()->entreprise_id;
            $newTimer->paused = 0; // Set default value
            $newTimer->comment = null; // Set explicitly to null if it's nullable
            $newTimer->save();

            // Update original timer
            $remainingDuration = $originalTimer->duration - $validated['duration'];
            if ($remainingDuration > 0) {
                $originalTimer->duration = $remainingDuration;
                $originalTimer->cost = ($remainingDuration / 60) * $employee->cost_per_hour;
                $originalTimer->save();
            } else {
                $originalTimer->delete();
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Duration moved successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Move Duration Error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to move duration: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getEmployeeProjects($employeeId)
    {
        // Get all tasks for this employee
        $taskIds = tasks_timer::where('employee_id', $employeeId)
            ->pluck('task_employee_id');

        // Get all projects associated with these tasks
        $projects = Project::whereHas('tasks', function ($query) use ($taskIds) {
            $query->whereIn('id', $taskIds);
        })
            ->select('id', 'name', 'tva')  // Explicitly select the TVA field
            ->distinct()
            ->get()
            ->map(function ($project) {
                return [
                    'id' => $project->id,
                    'name' => $project->name,
                    'tva' => $project->tva ?? 0  // Ensure TVA is never null
                ];
            });

        return response()->json($projects);
    }

    public function getAllProjects()
    {
        // Return all projects the user has access to
        $projects = Project::where('entreprise_id', auth()->user()->entreprise_id)->where(function ($query) {
            $query->whereNull('AcceptedDevisProject')
                ->orWhere('AcceptedDevisProject', '=', 1);
        })
            ->where('archived', 0)
            ->select('id', 'name', 'tva')  // Explicitly select the TVA field
            ->get();
        return response()->json($projects);
    }

    public function getAllTasks()
    {
        // Return all tasks the user has access to
        $tasks = Tasks::where('entreprise_id', auth()->user()->entreprise_id)
            ->orderBy('title')
            ->get();  // Add any necessary access control logic
        return response()->json($tasks);
    }

    public function moveTask(Request $request)
    {
        try {
            $validated = $request->validate([
                'task_id' => 'required|exists:tasks,id',
                'new_project_id' => 'required|exists:projects,id'
            ]);

            $enterpriseId = auth()->user()->entreprise_id;
            
            // Get task and verify it belongs to user's enterprise
            $task = Tasks::where('id', $validated['task_id'])
                ->where('entreprise_id', $enterpriseId)
                ->first();

            if (!$task) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Task not found or unauthorized access.'
                ], 403);
            }

            // Get new project and verify it belongs to user's enterprise
            $newProject = Project::where('id', $validated['new_project_id'])
                ->where('entreprise_id', $enterpriseId)
                ->where('archived', 0)
                ->first();

            if (!$newProject) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Project not found, archived, or unauthorized access.'
                ], 403);
            }

            // Update task project_id and project_name
            $task->project_id = $validated['new_project_id'];
            $task->project_name = $newProject->name;
            $task->save();

            // Update all related tasks_timer entries
            tasks_timer::where('task_id', $task->id)
                ->update([
                    'project_id' => $validated['new_project_id']
                ]);

            // Determine the redirect route based on user role
            $redirectRoute = '';
            if (Auth::user()->hasRole('Admin')) {
                $redirectRoute = route('admin.projects.show', $validated['new_project_id']);
            } elseif (Auth::user()->hasRole('Responsable de projet')) {
                $redirectRoute = route('Responsable_de_projet.projects.show', $validated['new_project_id']);
            } elseif (Auth::user()->hasRole('Responsable de projet et gestionnaire')) {
                $redirectRoute = route('Responsable_gestionnaire.projects.show', $validated['new_project_id']);
            } elseif (Auth::user()->hasRole('Employee')) {
                $redirectRoute = route('employee.projects.show', $validated['new_project_id']);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Task moved successfully.',
                'redirect_url' => $redirectRoute,
                'new_project_id' => $validated['new_project_id']
            ]);

        } catch (\Exception $e) {
            \Log::error('Move Task Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to move task: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getClientProjects($clientId)
    {
        // Get all projects for this client
        $projects = Project::where('client_id', $clientId)
            ->where('entreprise_id', Auth::user()->entreprise_id)
            ->where(function ($query) {
                $query->whereNull('AcceptedDevisProject')
                    ->orWhere('AcceptedDevisProject', '=', 1);
            })
            ->where('archived', 0)
            ->select('id', 'name', 'tva')  // Explicitly select the TVA field
            ->orderBy('name')
            ->get();

        return response()->json($projects);
    }

    public function downloadMultipleTasksRepports(Request $request, $startDate, $endDate)
    {
        // Convert dates to the correct timestamp format
        $endDate = Carbon::createFromFormat('Y-m-d', $endDate)->endOfDay()->toDateTimeString();
        $startDate = Carbon::createFromFormat('Y-m-d', $startDate)->startOfDay()->toDateTimeString();

        // Get the project ID and task IDs from the request
        $projectId = $request->projectId;
        $taskIds = json_decode($request->taskIds);

        if (!$projectId || empty($taskIds)) {
            return response()->json(['error' => 'Project ID and task IDs are required'], 400);
        }

        // Initialize the query
        $query = tasks_timer::where('entreprise_id', auth()->user()->entreprise_id)
            ->where('start_date', '>=', $startDate)
            ->whereNotNull('end_date')
            ->where('end_date', '<=', $endDate)
            ->whereIn('task_id', $taskIds);

        // Execute the query
        $tasks_timer = $query->orderBy('start_date', 'asc')->get();

        // Fetch the entreprise details
        $entreprise = Entreprise::where('id', auth()->user()->entreprise_id)->first();

        // Get project details
        $project = Project::find($projectId);

        // Generate the PDF using the existing template
        $pdf = PDF::loadView('admin.tasks.downloadPointageRepports', [
            'tasks_timer' => $tasks_timer,
            'entreprise' => $entreprise,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'project' => $project
        ]);

        $fileName = 'multiple_tasks_report_' . $project->name . '_' . $startDate . '_to_' . $endDate . '.pdf';

        return $pdf->download($fileName);
    }

    public function previewCustomerReport($startDate, $endDate)
    {
        // Debug log input
        \Log::info('previewCustomerReport input', [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'projectId' => request('projectId'),
            'employeeId' => request('employeeId'),
            'clientId' => request('clientId'),
            'taskIds' => request('taskIds'),
        ]);

        // Validate date format
        if (!$startDate || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate)) {
            return back()->with('error', 'Invalid or missing start date');
        }
        if (!$endDate || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) {
            return back()->with('error', 'Invalid or missing end date');
        }

        try {
            $startDate = Carbon::createFromFormat('Y-m-d', $startDate)->startOfDay();
            $endDate = Carbon::createFromFormat('Y-m-d', $endDate)->endOfDay();

            $projectId = request('projectId');
            $employeeId = request('employeeId');
            $clientId = request('clientId');
            $taskIds = request('taskIds');

            $query = tasks_timer::where('entreprise_id', Auth::user()->entreprise_id)
                ->whereBetween('start_date', [$startDate, $endDate]);

            if ($projectId) {
                $query->where('project_id', $projectId);
            }

            if ($employeeId) {
                $query->where('employee_id', $employeeId);
            }

            if ($clientId) {
                $projectIds = Project::where('client_id', $clientId)->pluck('id');
                $query->whereIn('project_id', $projectIds);
            }

            if ($taskIds) {
                $taskIds = $this->normalizeIds($taskIds);
                if (!empty($taskIds)) {
                    $query->whereIn('task_id', $taskIds);
                }
            }

            $tasks_timer = $query->orderBy('start_date')->get();
            $entreprise = Entreprise::find(Auth::user()->entreprise_id);
            $project = $projectId ? Project::find($projectId) : null;

            // Detect problematic tasks (same rules as other customer PDFs)
            $skippedTasks = [];
            $tasks_timer->each(function ($task) use (&$skippedTasks) {
                $projectIdForTask = Tasks::where('id', $task->task_id)->value('project_id');
                $employeeName = Employees::where('id', $task->employee_id)->value('name');

                $taskInfo = [
                    'task_id'       => $task->task_id,
                    'task_name'     => $task->task_name ?? ('Task #' . $task->task_id),
                    'project_id'    => $projectIdForTask,
                    'project_name'  => null,
                    'employee_id'   => $task->employee_id,
                    'employee_name' => $employeeName,
                    'start_date'    => $task->start_date,
                    'end_date'      => $task->end_date,
                    'duration'      => $task->duration,
                ];

                if (!$projectIdForTask) {
                    $skippedTasks[] = $taskInfo;
                    return;
                }

                $projectForTask = Project::find($projectIdForTask);
                $taskInfo['project_name'] = $projectForTask ? $projectForTask->name : null;

                if (!$projectForTask || !isset($projectForTask->cost_per_hour)) {
                    $skippedTasks[] = $taskInfo;
                }
            });

            // Get project contacts if project is selected
            $projectContacts = [];
            if ($project) {
                $projectContacts_ids = client_contacts_projects::where('project_id', $projectId)->pluck('contact_id')->toArray();
                $projectContacts = client_contacts::whereIn('id', $projectContacts_ids)->pluck('email')->toArray();
            }

            return view('admin.tasks.customer-preview', [
                'pdfUrl' => route('admin.CustomerPointagerepports.pdf', [
                    'startDate' => $startDate->format('Y-m-d'),
                    'endDate' => $endDate->format('Y-m-d'),
                    'projectId' => $projectId,
                    'employeeId' => $employeeId,
                    'clientId' => $clientId,
                    'taskIds' => $taskIds
                ]),
                'startDate' => $startDate->format('Y-m-d'),
                'endDate' => $endDate->format('Y-m-d'),
                'projectId' => $projectId,
                'employeeId' => $employeeId,
                'taskIds' => $taskIds,
                'clientId' => $clientId,
                'projectContacts' => $projectContacts,
                'projectName' => $project ? $project->name : '',
                'skippedTasks' => $skippedTasks,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in previewCustomerReport: ' . $e->getMessage());
            return back()->with('error', 'Failed to generate preview: ' . $e->getMessage());
        }
    }

    public function previewOneDayCustomerReport($startDate)
    {
        // Debug log input
        \Log::info('previewOneDayCustomerReport input', [
            'startDate' => $startDate,
            'projectId' => request('projectId'),
            'employeeId' => request('employeeId'),
            'clientId' => request('clientId'),
            'taskIds' => request('taskIds'),
        ]);

        // Validate date format
        if (!$startDate || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate)) {
            return back()->with('error', 'Invalid or missing start date');
        }
        try {
            $startDate = Carbon::createFromFormat('Y-m-d', $startDate)->startOfDay();
            $endDate = $startDate->copy()->endOfDay();

            $projectId = request('projectId');
            $employeeId = request('employeeId');
            $clientId = request('clientId');
            $taskIds = request('taskIds');

            $query = tasks_timer::where('entreprise_id', Auth::user()->entreprise_id)
                ->whereBetween('start_date', [$startDate, $endDate]);

            if ($projectId) {
                $query->where('project_id', $projectId);
            }

            if ($employeeId) {
                $query->where('employee_id', $employeeId);
            }

            if ($clientId) {
                $projectIds = Project::where('client_id', $clientId)->pluck('id');
                $query->whereIn('project_id', $projectIds);
            }

            if ($taskIds) {
                $taskIds = $this->normalizeIds($taskIds);
                if (!empty($taskIds)) {
                    $query->whereIn('task_id', $taskIds);
                }
            }

            $tasks_timer = $query->orderBy('start_date')->get();
            $entreprise = Entreprise::find(Auth::user()->entreprise_id);
            $project = $projectId ? Project::find($projectId) : null;

            // Detect problematic tasks (same rules as other customer PDFs)
            $skippedTasks = [];
            $tasks_timer->each(function ($task) use (&$skippedTasks) {
                $projectIdForTask = Tasks::where('id', $task->task_id)->value('project_id');
                $employeeName = Employees::where('id', $task->employee_id)->value('name');

                $taskInfo = [
                    'task_id'       => $task->task_id,
                    'task_name'     => $task->task_name ?? ('Task #' . $task->task_id),
                    'project_id'    => $projectIdForTask,
                    'project_name'  => null,
                    'employee_id'   => $task->employee_id,
                    'employee_name' => $employeeName,
                    'start_date'    => $task->start_date,
                    'end_date'      => $task->end_date,
                    'duration'      => $task->duration,
                ];

                if (!$projectIdForTask) {
                    $skippedTasks[] = $taskInfo;
                    return;
                }

                $projectForTask = Project::find($projectIdForTask);
                $taskInfo['project_name'] = $projectForTask ? $projectForTask->name : null;

                if (!$projectForTask || !isset($projectForTask->cost_per_hour)) {
                    $skippedTasks[] = $taskInfo;
                }
            });

            // Get project contacts if project is selected
            $projectContacts = [];
            if ($project) {
                $projectContacts_ids = client_contacts_projects::where('project_id', $projectId)->pluck('contact_id')->toArray();
                $projectContacts = client_contacts::whereIn('id', $projectContacts_ids)->pluck('email')->toArray();
            }

            return view('admin.tasks.customer-preview', [
                'pdfUrl' => route('admin.CustomerPointagerepports.pdf', [
                    'startDate' => $startDate->format('Y-m-d'),
                    'endDate' => $startDate->format('Y-m-d'),
                    'projectId' => $projectId,
                    'employeeId' => $employeeId,
                    'clientId' => $clientId,
                    'taskIds' => $taskIds
                ]),
                'startDate' => $startDate->format('Y-m-d'),
                'endDate' => $startDate->format('Y-m-d'),
                'projectId' => $projectId,
                'employeeId' => $employeeId,
                'taskIds' => $taskIds,
                'clientId' => $clientId,
                'projectContacts' => $projectContacts,
                'projectName' => $project ? $project->name : '',
                'skippedTasks' => $skippedTasks,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in previewOneDayCustomerReport: ' . $e->getMessage());
            return back()->with('error', 'Failed to generate preview: ' . $e->getMessage());
        }
    }

    public function sendCustomerReport(Request $request)
    {
        try {
            $startDate = Carbon::createFromFormat('Y-m-d', $request->start_date)->startOfDay();
            $endDate = Carbon::createFromFormat('Y-m-d', $request->end_date)->endOfDay();

            $projectId = $request->project_id;
            $employeeId = $request->employee_id;
            $clientId = $request->client_id;
            $taskIds = $this->normalizeIds($request->task_ids);
            $recipients = explode(',', $request->recipients);
            $subject = $request->subject;
            $body = $request->body;

            $query = tasks_timer::where('entreprise_id', Auth::user()->entreprise_id)
                ->whereBetween('start_date', [$startDate, $endDate]);

            if ($projectId) {
                $query->where('project_id', $projectId);
            }

            if ($employeeId) {
                $query->where('employee_id', $employeeId);
            }

            if ($clientId) {
                $projectIds = Project::where('client_id', $clientId)->pluck('id');
                $query->whereIn('project_id', $projectIds);
            }

            if (!empty($taskIds)) {
                $query->whereIn('task_id', $taskIds);
            }

            $tasks_timer = $query->orderBy('start_date')->get();
            $entreprise = Entreprise::find(Auth::user()->entreprise_id);
            $project = $projectId ? Project::find($projectId) : null;

            // Generate PDF
            $pdf = PDF::loadView('admin.tasks.CustomerdownloadPointageRepports', [
                'tasks_timer' => $tasks_timer,
                'entreprise' => $entreprise,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'project' => $project
            ]);

            // Ensure temp directory exists
            $dir = storage_path('app/public/temp');
            if (!file_exists($dir)) {
                mkdir($dir, 0775, true);
            }

            // Save PDF to temporary storage
            $pdfPath = $dir . '/customer_report_' . time() . '.pdf';
            $pdf->save($pdfPath);

            // Send email
            Mail::to($recipients)->send(new CustomerReportMail(
                $subject,
                $body,
                $pdfPath,
                $project ? $project->name : 'All Projects',
                $startDate->format('Y-m-d'),
                $endDate->format('Y-m-d')
            ));

            // Clean up temporary file
            unlink($pdfPath);

            return redirect()->route('admin.index')->with('success', 'Email sent successfully to: ' . implode(', ', $recipients));
        } catch (\Exception $e) {
            \Log::error('Error in sendCustomerReport: ' . $e->getMessage());
            return back()->with('error', 'Failed to send report: ' . $e->getMessage());
        }
    }

    public function streamCustomerReportPdf(Request $request)
    {
        try {
            $startDate = Carbon::createFromFormat('Y-m-d', $request->startDate)->startOfDay();
            $endDate = Carbon::createFromFormat('Y-m-d', $request->endDate)->endOfDay();

            $projectId = $request->projectId;
            $employeeId = $request->employeeId;
            $clientId = $request->clientId;
            $taskIds = $request->taskIds;

            $query = tasks_timer::where('entreprise_id', Auth::user()->entreprise_id)
                ->whereBetween('start_date', [$startDate, $endDate]);

            if ($projectId) {
                $query->where('project_id', $projectId);
            }

            if ($employeeId) {
                $query->where('employee_id', $employeeId);
            }

            if ($clientId) {
                $projectIds = Project::where('client_id', $clientId)->pluck('id');
                $query->whereIn('project_id', $projectIds);
            }

            if ($taskIds) {
                $taskIds = $this->normalizeIds($taskIds);
                if (!empty($taskIds)) {
                    $query->whereIn('task_id', $taskIds);
                }
            }

            $tasks_timer = $query->orderBy('start_date')->get();
            $entreprise = Entreprise::find(Auth::user()->entreprise_id);
            $project = $projectId ? Project::find($projectId) : null;

            // Filter out problematic tasks so the streamed PDF matches preview rules
            $filteredTasks = $tasks_timer->filter(function ($task) {
                $projectIdForTask = Tasks::where('id', $task->task_id)->value('project_id');

                if (!$projectIdForTask) {
                    return false;
                }

                $projectForTask = Project::find($projectIdForTask);

                if (!$projectForTask || !isset($projectForTask->cost_per_hour)) {
                    return false;
                }

                return true;
            })->values();

            $tasks_timer = $filteredTasks;

            // Generate PDF
            $pdf = PDF::loadView('admin.tasks.CustomerdownloadPointageRepports', [
                'tasks_timer' => $tasks_timer,
                'entreprise' => $entreprise,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'project' => $project
            ]);

            return $pdf->stream('customer_report.pdf');
        } catch (\Exception $e) {
            \Log::error('Error in streamCustomerReportPdf: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to generate PDF: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Generate a PDF table of all employees and total worked hours for each day in a period
     */
    public function employeesDailyTotals($startDate, $endDate)
    {
        $entrepriseId = Auth::user()->entreprise_id;
        $start = Carbon::createFromFormat('Y-m-d', $startDate)->startOfDay();
        $end = Carbon::createFromFormat('Y-m-d', $endDate)->endOfDay();

        // Build date list for header
        $dates = [];
        $cursor = $start->copy();
        while ($cursor->lte($end)) {
            $dates[] = $cursor->toDateString();
            $cursor->addDay();
        }

        // Fetch all active employees of this entreprise
        $employees = Employees::where('entreprise_id', $entrepriseId)
            ->where('archived', 0)
            ->orderBy('name')
            ->get(['id', 'name']);

        // Fetch timers in range and aggregate minutes by employee and day
        $timers = tasks_timer::where('entreprise_id', $entrepriseId)
            ->whereBetween('start_date', [$start, $end])
            ->get(['employee_id', 'start_date', 'duration']);

        $totals = [];
        foreach ($timers as $t) {
            $day = Carbon::parse($t->start_date)->toDateString();
            $empId = $t->employee_id;
            if (!isset($totals[$empId])) {
                $totals[$empId] = [];
            }
            if (!isset($totals[$empId][$day])) {
                $totals[$empId][$day] = 0;
            }
            $totals[$empId][$day] += (int) ($t->duration ?? 0);
        }

        $entreprise = Entreprise::find($entrepriseId);

        $pdf = PDF::loadView('admin.tasks.employeesDailyTotals', [
            'entreprise' => $entreprise,
            'employees' => $employees,
            'dates' => $dates,
            'totals' => $totals,
            'start' => $start->copy(),
            'end' => $end->copy(),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('employees_daily_totals_' . $startDate . '_to_' . $endDate . '.pdf');
    }
}