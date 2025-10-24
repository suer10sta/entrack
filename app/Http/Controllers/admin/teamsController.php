<?php

namespace App\Http\Controllers\admin;

use App\Models\User;
use App\Models\Tasks;
use App\Models\Teams;
use App\Models\Project;
use App\Models\Employees;
use App\Models\team_members;
use App\Models\team_tasks;
use App\Models\employees_tasks;
use App\Models\project_teams;
use App\Models\Entreprise;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class teamsController extends Controller
{
    public function index()
    {
        $projects = Project::where('entreprise_id', Auth::user()->entreprise_id)->where(function($query) {
            $query->whereNull('AcceptedDevisProject')
                  ->orWhere('AcceptedDevisProject', '=', 1);
        })
        ->where('archived', 0)
        ->get();
        $employees = Employees::where('entreprise_id', Auth::user()->entreprise_id)->get();
        $tasks = Tasks::where('user_id', Auth::user()->id)->get();
        $teams = Teams::where('entreprise_id', Auth::user()->entreprise_id)->get();
        $team_members= team_members::where('entreprise_id', Auth::user()->entreprise_id)->get();
        $project_teams=project_teams::where('entreprise_id', Auth::user()->entreprise_id)->get();
        return view('admin.teams.index', compact('employees', 'tasks','teams','team_members','projects','project_teams'));
    }
    public function create()
    {
        $employee_users = collect();
        $employees = Employees::where('entreprise_id', Auth::user()->entreprise_id)
        ->where('user_id', '<>', Auth::user()->id)
        ->get();
        foreach ($employees as $employee){
            $employee_user= User::where('id', $employee->user_id)
            ->where('is_activate', 1)
            ->get();
            $employee_users = $employee_users->concat($employee_user);
        }

        return view('admin.teams.create', compact('employee_users'));
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required',
        ]);

        $members=$request->input('members', []);

        $team = new Teams();
        $team->name = $validated['name'];
        $team->entreprise_id = Auth::user()->entreprise_id;
        $team->save();

        foreach ($members as $member) {
            $employee = Employees::where('name', $member)->first();

            $team_members=new team_members();
            $team_members->employee_id = $employee->id;
            $team_members->employee_name= $employee->name;
            $team_members->entreprise_id = Auth::user()->entreprise_id;
            $team_members->team_id = $team->id;

            $team_members->save();
            
        }
        if(Auth::user()->hasRole('Responsable de projet et gestionnaire')){
            return redirect()->route('Responsable_gestionnaire.teams.index')->with('message', 'team Added Successfully');
        }
        if(Auth::user()->hasRole('Responsable de projet')){
            return redirect()->route('Responsable_de_projet.teams.index')->with('message', 'team Added Successfully');
        }
        return to_route('admin.teams.index')->with('message', 'team Added Successfully');

    }

    public function edit(Teams $team)
    {
        $employee_users = collect();
        $employees = Employees::where('entreprise_id', Auth::user()->entreprise_id)
        ->where('user_id', '<>', Auth::user()->id)
        ->get();
        foreach ($employees as $employee){
            $employee_user= User::where('id', $employee->user_id)
            ->where('is_activate', 1)
            ->get();
            $employee_users = $employee_users->concat($employee_user);
        }
        return view('admin.teams.edit', compact('team', 'employee_users'));
    }

    public function update(Request $request, Teams $team)
    {
        $validated = $request->validate([
            'name' => 'required',
        ]);

        $members=$request->input('members', []);

        $team->name = $validated['name'];
        $team->save(); 

        $team_members= team_members::where('team_id', $team->id)->get();
        foreach ($team_members as $team_member) {
            $team_member->delete();
        }

        foreach ($members as $member) {
            $employee = Employees::where('name', $member)->first();

            $team_members=new team_members();
            $team_members->employee_id = $employee->id;
            $team_members->employee_name= $employee->name;
            $team_members->entreprise_id = Auth::user()->entreprise_id;
            $team_members->team_id = $team->id;

            $team_members->save();

            $team_tasks = team_tasks::where('team_id', $team->id)->get();
            foreach ($team_tasks as $team_task) {
                $old_tasks= employees_tasks::where('employee_id', $employee->id)->where('task_id', $team_task->task_id)->get();
                foreach ($old_tasks as $old_task) {
                    $old_task->delete();
                }
                $employee_task = new employees_tasks;
                $employee_task->employee_id= $employee->id;
                $employee_task->employee_name= $employee->name;
                $employee_task->task_id = $team_task->task_id;
                $employee_task->task_name= $team_task->task_name;
                $employee_task->entreprise_id=Auth::user()->entreprise_id;
                $employee_task->save();
            }
            
            
        }
        if(Auth::user()->hasRole('Responsable de projet et gestionnaire')){
            return redirect()->route('Responsable_gestionnaire.teams.index')->with('message', 'team Updated Successfully');
        }
        if(Auth::user()->hasRole('Responsable de projet')){
            return redirect()->route('Responsable_de_projet.teams.index')->with('message', 'team Updated Successfully');
        }
        return to_route('admin.teams.index')->with('message', 'team Updated Successfully');
    }

    public function destroy(Teams $team)
    {
        $team_members= team_members::where('team_id', $team->id)->get();
        foreach ($team_members as $team_member) {
            $team_member->delete();
        }
        $team->delete();

        return back()->with('message', 'teams Deleted Successfully');
    }

    
    public function downloadTeamsRepports()
    {
        $projects = Project::where('entreprise_id', Auth::user()->entreprise_id)->where(function($query) {
            $query->whereNull('AcceptedDevisProject')
                  ->orWhere('AcceptedDevisProject', '=', 1);
        })
        ->where('archived', 0)
        ->get();
        $employees = Employees::where('entreprise_id', Auth::user()->entreprise_id)->get();
        $tasks = Tasks::where('user_id', Auth::user()->id)->get();
        $team_members= team_members::where('entreprise_id', Auth::user()->entreprise_id)->get();
        $project_teams=project_teams::where('entreprise_id', Auth::user()->entreprise_id)->get();

        $teams = Teams::where('entreprise_id', auth()->user()->entreprise_id)
        ->get();
        $entreprise = Entreprise::where('id', auth()->user()->entreprise_id)->first();
        $pdf = PDF::loadView('admin.teams.downloadRepports', [
            'teams' => $teams,
            'entreprise' => $entreprise,
            'project_teams' => $project_teams,
        ]); 

        return $pdf->stream('teamsRepports.pdf');
    }
}
