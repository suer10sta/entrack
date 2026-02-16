<?php

namespace App\Http\Controllers\superadmin;

use App\Models\User;
use App\Models\Tasks;
use App\Models\Teams;
use App\Models\Project;
use App\Models\Employees;
use App\Models\Entreprise;
use App\Models\Client;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function index() {
        $projects = Project::where('entreprise_id', auth()->user()->entreprise_id)->get();
        $entreprises=Entreprise::all();
        $users = User::with('entreprise')
                ->where('is_employee', 0)
                ->get();
        $enterpriseId = Auth::user()->entreprise_id;
        $employees = Employees::where('entreprise_id', $enterpriseId)->get();
        $tasks= Tasks::where('user_id', Auth::user()->id)->get();
        $teams = Teams::where('entreprise_id', Auth::user()->entreprise_id)->get();
        $clients = Client::where('entreprise_id', $enterpriseId)->get();
        $permissions=Permission::all();
        if(Auth::user()->hasRole('super_admin')) {
            return view('superadmin.Permissions.index', compact("permissions","users", "entreprises"));
        } else {
            return view('admin.Permissions.index', compact("permissions", "teams", "employees", "tasks", "projects", "clients"));
        }
    }

    public function create() {
        if(Auth::user()->hasRole('super_admin')) {
            return view('superadmin.Permissions.create');
        } else {
            return view('admin.Permissions.create');
        }
    }

    public function store(Request $request) {
        $validated=$request->validate(["name"=>"required"]);
        Permission::create($validated);
        $permissions=Permission::all();
        
        $enterpriseId = Auth::user()->entreprise_id;
        $employees = Employees::where('entreprise_id', $enterpriseId)->get();
        $tasks= Tasks::where('user_id', Auth::user()->id)->get();
        $teams = Teams::where('entreprise_id', Auth::user()->entreprise_id)->get();
        $projects = Project::where('entreprise_id', auth()->user()->entreprise_id)->get();
        $clients = Client::where('entreprise_id', Auth::user()->entreprise_id)->get();

        if(Auth::user()->hasRole('super_admin')) {
            return view("superadmin.Permissions.index", compact("permissions"))->with('message', 'Permission Added Successfully');
        } else {
            return view("admin.Permissions.index", compact("permissions", "teams", "employees", "tasks", "projects", "clients" ))->with('message', 'Permission Added Successfully');
        }
    }

    public function edit(Permission $permission) {
        if(Auth::user()->hasRole('super_admin')) {
            return view('superadmin.Permissions.edit', compact("permission"));
        } else {
            return view('admin.Permissions.edit', compact("permission"));
        }
    }

    public function update(Request $request, Permission $permission) {
        $validated=$request->validate(["name"=>"required"]);
        $permission->update($validated);
        $permissions=Permission::all();
        $enterpriseId = Auth::user()->entreprise_id;
        $employees = Employees::where('entreprise_id', $enterpriseId)->get();
        $tasks= Tasks::where('user_id', Auth::user()->id)->get();
        $teams = Teams::where('entreprise_id', Auth::user()->entreprise_id)->get();
        $projects = Project::where('entreprise_id', auth()->user()->entreprise_id)->get();
        $clients = Client::where('entreprise_id', Auth::user()->entreprise_id)->get();
        if(Auth::user()->hasRole('super_admin')) {
            return view("superadmin.Permissions.index", compact("permissions"))->with('message', 'Permission Updated Successfully');
        } else {
            return view("admin.Permissions.index", compact("permissions", "teams", "employees", "tasks", "projects"))->with('message', 'Permission Updated Successfully');
        }
    }

    public function destroy(Permission $permission) {
        $permission->delete();
        return back()->with('message', 'Permission Deleted Successfully');
    }
}
