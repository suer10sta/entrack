<?php

namespace App\Http\Controllers\admin;

use Carbon\Carbon;
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

class todolisteController extends Controller
{
    public function index(){
        $enterpriseId = Auth::user()->entreprise_id;
        $employees = Employees::where('user_id', $enterpriseId)->get();
        return view('admin.tasks.index', compact('employees'));
    }

    public function create(Request $request){
        return view('admin.todoliste.create');
    }

    public function store(Request $request)
    {
        $projects = Project::where('entreprise_id', Auth::user()->entreprise_id)->where(function($query) {
            $query->whereNull('AcceptedDevisProject')
                  ->orWhere('AcceptedDevisProject', '=', 1);
        })->get();
        $tasks= Tasks::where('user_id', Auth::user()->id)->get();
        $teams = Teams::where('entreprise_id', Auth::user()->entreprise_id)->get();
        $clients = Client::where('entreprise_id', Auth::user()->entreprise_id)->where(function($query) {
            $query->whereNull('AcceptedDevisClient')
                  ->orWhere('AcceptedDevisClient', '=', 1);
        })->get();
        $todoliste = todoliste::where('user_id', Auth::user()->id)->get();
        $invoices = invoices_history::where('entreprise_id', Auth::user()->entreprise_id)->paginate(5); 
        // Get the authenticated user's enterprise_id
        $enterpriseId = Auth::user()->entreprise_id;
    
        // Retrieve only employees with the same enterprise_id
        $employees = Employees::where('entreprise_id', $enterpriseId)->get();
        $entreprises = Entreprise::all();

        $item = new todoliste();
        $item->title = $request->title;
        $item->start = $request->start;
        $item->end = $request->end;
        $item->description = $request->description;
        $item->entreprise_id = Auth::user()->entreprise_id;
        $item->user_id = Auth::user()->id;
        $item->status = 0;
        $item->save();

        return redirect()->route('admin.index')->with('message', __('messages.task Added Successfully'));
    }

    public function edit($id)
    {
        $todo = todoliste::find($id);
        return view('admin.todoliste.edit', compact('todo'));
    }

    public function update(Request $request, $id)
    {

        $projects = Project::where('entreprise_id', Auth::user()->entreprise_id)->where(function($query) {
            $query->whereNull('AcceptedDevisProject')
                  ->orWhere('AcceptedDevisProject', '=', 1);
        })->get();
        $tasks= Tasks::where('user_id', Auth::user()->id)->get();
        $teams = Teams::where('entreprise_id', Auth::user()->entreprise_id)->get();
        $clients = Client::where('entreprise_id', Auth::user()->entreprise_id)->where(function($query) {
            $query->whereNull('AcceptedDevisClient')
                  ->orWhere('AcceptedDevisClient', '=', 1);
        })->get();
        $todoliste = todoliste::where('user_id', Auth::user()->id)->get();
        $invoices = invoices_history::where('entreprise_id', Auth::user()->entreprise_id)->paginate(5); 
        // Get the authenticated user's enterprise_id
        $enterpriseId = Auth::user()->entreprise_id;
    
        // Retrieve only employees with the same enterprise_id
        $employees = Employees::where('entreprise_id', $enterpriseId)->get();
        $entreprises = Entreprise::all();



        // Validate the incoming request data
        $validated = $request->validate([
            'title' => 'required',
            'start' => 'required|date',
            'end' => 'required|date',
            'description' => 'nullable|string',
        ]);
    
        // Find the todoliste item or fail with a 404 error
        $todo = todoliste::findOrFail($id);
    
        // Update the model with validated data
        $todo->title = $validated['title'];
        $todo->start = $validated['start'];
        $todo->end = $validated['end'];
        $todo->description = $validated['description'];
        $todo->save();
    
        // Return back with a success message
        return redirect()->route('admin.index')->with('message', __('messages.task Updated Successfully'));
    }

    public function destroy()
    {
        todoliste::where('user_id', Auth::user()->id)->delete();

        return back()->with('message', __('messages.todolistes Deleted Successfully'));
    }

    public function deleteItem($id)
    {
        todoliste::where('id', $id)->delete();

        return back()->with('message', __('messages.Item Deleted Successfully'));
    }

    public function updateStatus(Request $request, $todoId)
    {
        $todo = todoliste::findOrFail($todoId); // Assuming you have a Todo model
    
        // Check if 'status' is set to 1 (checked), otherwise set to 0 (unchecked)
        $todo->status = $request->input('status') == 1 ? 1 : 0;
    
        $todo->save();
    
        return back()->with('success', __('messages.Todo status updated successfully.'));
    }

    
}
