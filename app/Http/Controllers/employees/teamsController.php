<?php

namespace App\Http\Controllers\employees;

use App\Models\User;
use App\Models\Tasks;
use App\Models\Teams;
use App\Models\Project;
use App\Models\Employees;
use App\Models\team_members;
use App\Models\project_teams;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class teamsController extends Controller
{
    public function index()
    {
        $employee_id= Employees::where('user_id', Auth::user()->id)->first()->id;

        $projects = Project::where('entreprise_id', auth()->user()->entreprise_id)
        ->where('archived', 0)
        ->get();
        $employees = Employees::where('entreprise_id', Auth::user()->entreprise_id)->get();
        $tasks = Tasks::where('user_id', Auth::user()->id)->get();

        $team_members= team_members::where('employee_id', $employee_id)->get();
        $teams_ids= collect();
        foreach($team_members as $team_member){
            $teams_ids->push($team_member->team_id);
        }
        $teams = Teams::whereIn('id', $teams_ids)->get();
        $project_teams=project_teams::where('entreprise_id', Auth::user()->entreprise_id)->get();
        return view('employees.teams.index', compact('employees', 'tasks','teams','team_members','projects','project_teams'));
    }
    public function create()
    {
        $employee_users = collect();
        $employees = Employees::where('entreprise_id', Auth::user()->entreprise_id)->get();
        foreach ($employees as $employee){
            $employee_user= User::where('id', $employee->user_id)
            ->where('is_activate', 1)
            ->get();
            $employee_users = $employee_users->concat($employee_user);
        }

        return view('employees.teams.create', compact('employee_users'));
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

        return to_route('admin.teams.index')->with('message', 'team Added Successfully');

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
}
