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

        $projects = Project::where('entreprise_id', auth()->user()->entreprise_id)
            ->where(function($query) {
                $query->whereNull('AcceptedDevisProject')
                      ->orWhere('AcceptedDevisProject', '=', 1);
            })
            ->where('archived', 0)
            ->orderBy('client_id')
            ->orderBy('name')
            ->paginate(6, ['*'], 'page', $projectPage);

        $projectsFilter = Project::where('entreprise_id', auth()->user()->entreprise_id)
            ->where(function($query) {
                $query->whereNull('AcceptedDevisProject')
                      ->orWhere('AcceptedDevisProject', '=', 1);
            })
            ->where('archived', 0)
            ->orderBy('client_id')
            ->get();

        $tasks = Tasks::where('user_id', Auth::user()->id)->get();
        $teams = Teams::where('entreprise_id', Auth::user()->entreprise_id)->get();
        $clients = Client::where('entreprise_id', auth()->user()->entreprise_id)
            ->where(function($query) {
                $query->whereNull('AcceptedDevisClient')
                      ->orWhere('AcceptedDevisClient', '=', 1);
            })
            ->orderBy('client_number')
            ->get();

        $todoliste = todoliste::where('user_id', Auth::user()->id)
            ->orderBy('start', 'asc')
            ->paginate(4, ['*'], 'todoPage', $todoPage);

        $invoices = invoices_history::where('entreprise_id', Auth::user()->entreprise_id)
            ->orderBy('number', 'desc')
            ->paginate(5);

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
            'dayEvents'
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
}
