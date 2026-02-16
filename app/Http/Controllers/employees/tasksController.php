<?php

namespace App\Http\Controllers\employees;

use Carbon\Carbon;
use App\Models\Tasks;
use App\Models\Teams;
use App\Models\Client;
use App\Models\Project;
use App\Models\Employees;
use App\Models\team_tasks;
use App\Models\tasks_timer;
use App\Models\Entreprise;
use Illuminate\Http\Request;
use App\Models\employees_tasks;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Illuminate\Support\Facades\File;

class tasksController extends Controller
{
    public function index()
    {
        $employee_id= Employees::where('user_id', Auth::user()->id)->first()->id;


        $projects = Project::where('entreprise_id', auth()->user()->entreprise_id)
            ->where('archived', 0)
            ->get();
        $activeProjectIds = $projects->pluck('id');
        $enterpriseId = Auth::user()->entreprise_id;
        $employees = Employees::where('entreprise_id', $enterpriseId)->get();
        $teams = Teams::where('entreprise_id', Auth::user()->entreprise_id)->get();

        $employee_tasks_ids = employees_tasks::where('employee_id', $employee_id)->pluck('task_id');

        $tasks = Tasks::whereIn('id', $employee_tasks_ids)
            ->whereIn('project_id', $activeProjectIds)
            ->get();
        $tasks_ordered = Tasks::whereIn('id', $employee_tasks_ids)
            ->whereIn('project_id', $activeProjectIds)
            ->orderBy('end', 'asc')
            ->get();

        // Keep employees_tasks in sync with active tasks
        $employee_tasks = employees_tasks::where('employee_id', $employee_id)
            ->whereIn('task_id', $tasks->pluck('id'))
            ->get();

        return view('employees.tasks.index', compact('employees', 'tasks', 'tasks_ordered', 'teams', 'projects'));
    }


    public function edit(Tasks $task)
    {
        return view('employees.tasks.edit', compact('task'));
    }

    public function update(Request $request, Tasks $task)
    {
        $validated = $request->validate([
            "title" => "required",
            "start" => "required",
            "end" => "required",
            "description" => "required",
            "project_name" => "required",
        ]);

        $task->update($validated);

        return redirect()->route('employees.tasks.index')->with('message', 'task Updated Successfully');
    }


    public function destroy(Tasks $task)
    {
        $team_tasks = team_tasks::where('task_id', $task->id)->get();
        foreach ($team_tasks as $team_task) {
            $team_task->delete();
        }
        $employees_tasks= employees_tasks::where('task_id', $task->id)->get();
        foreach ($employees_tasks as $employee_task) {
            $employee_task->delete();
        }

        $task->delete();
        return back()->with('message', 'task Deleted Successfully');
    }

    public function status(Tasks $task, Request $request, $status)
    {

        //Status = 0 --> Inactive
        if ($status==0) {
            $task->update(['status' => 0]);
        }
        //Status = 1 --> Working
        if($status==1){
            $task->update(['status' => 1]);
        } 
        //Status = 2 --> Done
        if($status==2) {
            $task->update(['status' => 2]);
        }
        return back()->with('message', 'task Status Updated Successfully');
    }

    function pointage(){
        $projects = Project::where('entreprise_id', auth()->user()->entreprise_id)
            ->where('archived', 0)
            ->get();
        $activeProjectIds = $projects->pluck('id');
        $tasks = Tasks::where('user_id', Auth::user()->id)
            ->whereIn('project_id', $activeProjectIds)
            ->get();
        $employees = Employees::where('entreprise_id', auth()->user()->entreprise_id)->get();
        $clients = Client::where('entreprise_id', Auth::user()->entreprise_id)->get();
        $teams = Teams::where('entreprise_id', Auth::user()->entreprise_id)->get();
        

        $employee_id= Employees::where('user_id', Auth::user()->id)->first()->id;
        $employees_tasks= employees_tasks::where('employee_id', $employee_id)->get();
        
        return view('employees.tasks.pointage', compact('employees_tasks', 'employee_id', 'projects', 'tasks', 'employees', 'clients', 'teams'));
    }

    public function getTasksInfo($startDate, $employee_id) 
    {
        // Convert the start date to a proper format, if needed 
        $startDate = Carbon::parse($startDate)->toDateString();
    
        // Fetch tasks information for the selected start date
        $tasks_timer = tasks_timer::whereDate('start_date', $startDate)
        ->where('employee_id', $employee_id)
        ->orderBy('start_date', 'asc')
        ->get();
    
        // Return the tasks information as JSON response
        return response()->json($tasks_timer);
    }

    public function getTasksInfoInterval($startDate, $endDate, $employee)
{
    // Convert the start and end dates to a proper format, if needed 
    $startDate = Carbon::parse($startDate)->toDateString();
    $endDate = Carbon::parse($endDate)->toDateString();

    // Fetch tasks information for the selected start and end dates
    $tasks_timer = tasks_timer::where('employee_id', $employee)
                ->where('start_date', '>=', $startDate)
                ->where(function($query) use ($endDate) {
                    // Add one day to the endDate for inclusive comparison
                    $inclusiveEndDate = Carbon::parse($endDate)->addDay()->format('d-m-Y');
                    $query->where('end_date', '<', $inclusiveEndDate)
                          ->orWhereNull('end_date');
                })
                ->orderBy('start_date', 'asc')
               ->get();

    // Return the tasks information as JSON response
    return response()->json($tasks_timer);
}

public function updateTimerComment(Request $request, $id)
    {
        $request->validate([
            'comment' => 'nullable|string|max:1000',
        ]);

        $timer = tasks_timer::where('id', $id)->first();
        if (!$timer) {
            return response()->json(['message' => 'Task timer not found'], 404);
        }

        // Ensure the authenticated user is the owner employee
        $employeeId = Employees::where('user_id', Auth::id())->value('id');
        if ($timer->employee_id !== $employeeId) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $comment = $request->input('comment');
        if (is_string($comment)) {
            $comment = trim($comment);
            if ($comment === '') {
                $comment = null;
            }
        }
        $timer->comment = $comment;
        $timer->save();

        return response()->json([
            'message' => 'Comment updated',
            'comment' => $timer->comment,
            'id' => $timer->id,
        ]);
    }


public function downloadPointageRepports($startDate, $endDate, $employeeId, Request $request)
{
    $tasks_timer = tasks_timer::where('entreprise_id', auth()->user()->entreprise_id)
    ->where('employee_id', $employeeId) 
    ->Where('start_date' , '>=', $startDate)
    ->Where('end_date' , '<=', $endDate)
    ->get();
    $entreprise = Entreprise::where('id', auth()->user()->entreprise_id)->first();
    $employeeName = Employees::where('id', $employeeId)->first()->name;
    $pdf = PDF::loadView('employees.tasks.downloadPointageRepports', [
        'tasks_timer' => $tasks_timer,
        'entreprise' => $entreprise,
        'startDate' => $startDate,
        'endDate' => $endDate,
        'employeeName' => $employeeName,
    ]); 
    return $pdf->stream('pointageRepports.pdf');

}


public function downloadOneDayRepports($startDate, $employeeId ,Request $request)
{
    // Log the received startDate
    \Log::info("Received startDate: " . $startDate);

    $tasks_timer = tasks_timer::where('entreprise_id', auth()->user()->entreprise_id)
    ->where('employee_id', $employeeId) 
    ->whereDate('start_date', $startDate)
    ->get();

    if ($tasks_timer->isEmpty()) {
        \Log::info("No tasks found for the given date.");
    }

    $entreprise = Entreprise::where('id', auth()->user()->entreprise_id)->first();
    $employeeName = Employees::where('id', $employeeId)->first()->name;

    $pdf = PDF::loadView('employees.tasks.downloadOneDayPointageRepports', [
        'tasks_timer' => $tasks_timer,
        'entreprise' => $entreprise,
        'startDate' => $startDate,
        'employeeName' => $employeeName,
    ]);

    return $pdf->stream('pointageRepports.pdf');
}


}
