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
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission;
use App\Models\EnterpriseRolePermission;

class RoleController extends Controller
{
    public function index() {
        $projects = Project::where('entreprise_id', auth()->user()->entreprise_id)->get();
        $entreprises=Entreprise::all();
        $users = User::with('entreprise')
                ->where('is_employee', 0)
                ->get();
        $teams = Teams::where('entreprise_id', Auth::user()->entreprise_id)->get();
        $roles=Role::whereNotIn('name', ['super_admin'])->get();
        $tasks= Tasks::where('user_id', Auth::user()->id)->get();
        $enterpriseId = Auth::user()->entreprise_id;
        $employees = Employees::where('entreprise_id', $enterpriseId)->get();
        $clients = Client::where('entreprise_id', $enterpriseId)->get();


        if(Auth::user()->hasRole('super_admin')) {
            return view('superadmin.roles.index', compact("roles","users", "entreprises"));
        } else {
            return view('admin.roles.index', compact("roles", "teams", "employees", "tasks", "projects", "clients"));
        }
    }

    public function edit(Role $role) {
        $permissions = Permission::orderBy('name')->get();
        if(Auth::user()->hasRole('super_admin')) {
            return view('superadmin.roles.edit', compact("role", "permissions"));
        } else {
            return view('admin.roles.edit', compact("role", "permissions"));
        }
    }

    public function update(Request $request, Role $role) {
        $validated=$request->validate(["name"=>"required"]);
        $role->update($validated);
        if(Auth::user()->hasRole('super_admin')) {
            return to_route('superadmin.roles.index')->with('message', __('messages.Role Updated Successfully'));
        } else {
            return to_route('admin.roles.index')->with('message', __('messages.Role Updated Successfully'));
        }
    }

    public function destroy(Role $role) {
        $role->delete();
        return back()->with('message', __('messages.Role Deleted Successfully'));
    }

    public function assign_permission(Request $request, Role $role)
    {
        $entreprise_id = Auth::user()->entreprise_id;

        // Clear existing enterprise-specific permissions for this role
        EnterpriseRolePermission::where('entreprise_id', $entreprise_id)
            ->where('role_id', $role->id)
            ->delete();

        // Add new permissions for this enterprise
        if ($request->has('permissions') && is_array($request->permissions)) {
            foreach ($request->permissions as $permission_id) {
                EnterpriseRolePermission::create([
                    'entreprise_id' => $entreprise_id,
                    'role_id' => $role->id,
                    'permission_id' => $permission_id
                ]);
            }
        }

        // Redirect to roles index based on user role
        if(Auth::user()->hasRole('super_admin')) {
            return to_route('superadmin.roles.index')->with('message', __('messages.Permissions updated successfully'));
        } elseif(Auth::user()->hasRole('Admin')) {
            return to_route('admin.roles.index')->with('message', __('messages.Permissions updated successfully'));
        } elseif(Auth::user()->hasRole('Responsable de projet')) {
            return to_route('Responsable_de_projet.roles.index')->with('message', __('messages.Permissions updated successfully'));
        } elseif(Auth::user()->hasRole('Employee')) {
            return to_route('employee.roles.index')->with('message', __('messages.Permissions updated successfully'));
        } elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire')) {
            return to_route('Responsable_gestionnaire.roles.index')->with('message', __('messages.Permissions updated successfully'));
        }

        return redirect()->back()->with('message', __('messages.Permissions updated successfully'));
    }
}
