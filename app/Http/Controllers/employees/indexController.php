<?php

namespace App\Http\Controllers\employees;


use App\Models\User;
use App\Models\Tasks;
use App\Models\Teams;
use App\Models\Client;
use App\Models\Project;
use App\Models\Employees;
use App\Models\team_tasks;
use App\Models\employees_tasks;
use App\Models\todoliste;
use App\Models\team_members;
use App\Models\project_teams;
use App\Models\tasks_timer;
use App\Models\work_days_repports;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use DateTime;
use DateInterval;
use Carbon\Carbon;

class indexController extends Controller 
{
    function index(){
        $employee_id= Employees::where('user_id', Auth::user()->id)->first()->id;


        $enterpriseId = Auth::user()->entreprise_id;
        $employees = Employees::where('entreprise_id', $enterpriseId)->get();
        $teams = Teams::where('entreprise_id', Auth::user()->entreprise_id)->get();
        $employees_teams = team_members::where('employee_id', $employee_id)
        ->where('entreprise_id', $enterpriseId)
        ->get();

        $employees_tasks = employees_tasks::where('employee_id',$employee_id )->get();
        $employee_tasks_ids= collect();
        foreach($employees_tasks as $employee_task){
            $employee_tasks_ids->push($employee_task->task_id);
        }
        $tasks= Tasks::whereIn('id', $employee_tasks_ids)->get();
        $tasks_ordered = Tasks::whereIn('id', $employee_tasks_ids)->orderBy('end', 'asc')->get();

        $employee_projects_ids= collect();
        foreach($tasks as $t){
            $employee_projects_ids->push($t->project_id);
        }
        $projects = Project::where('entreprise_id', auth()->user()->entreprise_id)
        ->whereIn('id', $employee_projects_ids)
        ->where('archived', 0)
        ->get();

        $clients = Client::where('entreprise_id', Auth::user()->entreprise_id)->get();


        $currentYear = strval(Carbon::now()->year);
        $currentMonth = str_pad(Carbon::now()->month, 2, '0', STR_PAD_LEFT);

        $startDate = Carbon::createFromDate($currentYear, $currentMonth, 1)->startOfMonth();
        $endDate = Carbon::createFromDate($currentYear, $currentMonth, 1)->endOfMonth();
        
        // Get work days reports for the current employee
        $work_days_repports = work_days_repports::where('employee_id', $employee_id)
            ->where('year', $currentYear)
            ->where('month', $currentMonth)
            ->first();

        if ($work_days_repports) {
            $totalMinutesForEmployee = tasks_timer::where('employee_id', $employee_id)
                ->whereBetween('start_date', [$startDate, $endDate])
                ->whereNotNull('end_date')
                ->sum('duration');

            //convert minutes to hours
            $totalHours = $totalMinutesForEmployee / 60;

            // First calculate days
            $totalDays = $totalHours / 8.5;
            
            // Round up any decimal value to the next whole number
            $daysWorked = ceil($totalDays);

            // Calculate the difference to add to hours_accumulate
            $diff = $totalDays - $daysWorked;

            // Convert the difference from days to hours (assuming 1 day = 8.5 hours)
            $hoursToAdd = $diff * 8.5;

            // Format hoursToAdd to two decimal places
            $hoursToAdd = number_format($hoursToAdd, 2, '.', '');

            // Add the hours to hours_accumulate only if daysWorked is not equal to totalDays
            if ($daysWorked !== $totalDays && $daysWorked < $totalDays) {
                $previous_month_report = work_days_repports::where('employee_id', $employee_id)
                    ->where('month', date('m', strtotime('-1 month')))
                    ->where('year', date('Y', strtotime('-1 month')))
                    ->first();
                
                if ($previous_month_report) {
                    $work_days_repports->hours_accumulate = $previous_month_report->hours_accumulate + $hoursToAdd;
                } else {
                    $work_days_repports->hours_accumulate = $hoursToAdd;
                }
            }
            
            $work_days_repports->days_worked = $daysWorked;
            $work_days_repports->save();
        }

        return view('employees.tasks.pointage', compact('employees', 'tasks', 'tasks_ordered', 'teams', 'projects', 'employees_teams', 'employees_tasks', 'employee_id', 'clients', 'work_days_repports'));
    }

}
