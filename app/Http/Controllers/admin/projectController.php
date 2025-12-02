<?php

namespace App\Http\Controllers\admin;

use Mail;
use DateTime;
use DateInterval;
use Carbon\Carbon;
use App\Models\Tasks;
use App\Models\Teams;
use App\Models\Client;
use App\Models\Project;
use App\Mail\DevisEmail;
use App\Mail\InvoiceMail;
use App\Models\Employees;
use App\Models\Entreprise;
use App\Mail\InvoicesEmail;
use App\Models\tasks_timer;
use Illuminate\Http\Request;
use App\Models\devis_history;
use App\Models\employees_tasks;
use App\Models\team_tasks;
use App\Models\project_teams;
use App\Models\client_contacts;
use App\Models\invoices_history;
use App\Models\client_contacts_projects;
use App\Models\client_contacts_devis;
use App\Models\credit_note;
use App\Models\note_de_frais;
use App\Models\devis_details;
use App\Models\ProjectFile;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Illuminate\Support\Facades\File;
use setasign\Fpdi\Fpdi;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Mail\CreditNoteEmail;
use App\Models\User;



class projectController extends Controller
{
    public function index()
    {
        $projects = Project::where('entreprise_id', auth()->user()->entreprise_id)->where(function($query) {
            $query->whereNull('AcceptedDevisProject')
                  ->orWhere('AcceptedDevisProject', '=', 1);
        })
        ->where('archived', 0)
        ->orderBy('name')
        ->get();
        $employees = Employees::where('entreprise_id', Auth::user()->entreprise_id)->where('archived', 0)->orderBy('name', 'asc')->get();
        $tasks = Tasks::where('user_id', Auth::user()->id)->get();
        $teams = Teams::where('entreprise_id', Auth::user()->entreprise_id)->get();
        return view('admin.projects.index', compact('projects', 'employees', 'tasks', 'teams'));
    }
   
    public function create($client_id)
    {
        $teams = Teams::where('entreprise_id', Auth::user()->entreprise_id)->get();
        return view('admin.projects.create', compact('teams', 'client_id'));
    }
    
    public function store(Request $request, $client_id)
    {
        $project = new Project();
        $project->name = $request->name;
        $project->description = $request->description;
        $project->budget = $request->budget;
        $project->cost_per_hour = $request->cost_per_hour;
        $project->status = 0;
        $project->regie = $request->has('regie') ? 1 : 0;
         
        $project->client_id = $client_id;
        $project->estimated_hours = $request->estimated_hours;
        $project->entreprise_id = auth()->user()->entreprise_id;

        $teams = $request->input('teams', []);
        $contacts = $request->input('contacts', []); 

        $project->save();

        foreach ($teams as $t) {
            $team = Teams::where('id', $t)->first();

            $project_teams = new project_teams();
            $project_teams->team_id = $team->id;
            $project_teams->team_name = $team->name;
            $project_teams->entreprise_id = Auth::user()->entreprise_id;
            $project_teams->project_id = $project->id;

            $project_teams->save();
        }


        foreach ($contacts as $contact_id) { 
            $contact = client_contacts::where('id', $contact_id)->first(); 

            $contact_projects = new client_contacts_projects();

            $contact_projects->project_id = $project->id;
            $contact_projects->project_name = $project->name;
            $contact_projects->contact_id = $contact->id;
            $contact_projects->contact_name = $contact->name;
            $contact_projects->client_id = $client_id;

            $contact_projects->save();
        }

        if(Auth::user()->hasRole('Admin')){
            return redirect()->route('admin.clients.show', $client_id);
        }
        if(Auth::user()->hasRole('Responsable de projet')){
            return redirect()->route('Responsable_de_projet.clients.show', $client_id);
        }
        if(Auth::user()->hasRole('Responsable de projet et gestionnaire')){
            return redirect()->route('Responsable_gestionnaire.clients.show', $client_id);
        }
    }

    public function updateProject(Request $request)
    {
        $Project = Project::find($request->id);
        $Project->{$request->column} = $request->content;
        $Project->save();

        if($request->column == 'name'){
            $tasks= Tasks::where('project_id', $request->id)->get();

            foreach ($tasks as $task) {
                $task->project_name = $Project->name;
                $task->save();
            }
            $client_contacts = client_contacts::where('project_id', $request->id)->get();
            
            foreach ($client_contacts as $contact) {
                $contact->project_name = $request->content;
                $contact->save();
            }
        }
         

        return response()->json(['success'=>'Project updated successfully.']);
    }
   
    public function show(Project $project)
    {
        $projects = Project::where('entreprise_id', auth()->user()->entreprise_id)->where(function($query) {
            $query->whereNull('AcceptedDevisProject')
                  ->orWhere('AcceptedDevisProject', '=', 1);
        })
        ->where('archived', 0)
        ->get();
        $employees = Employees::where('entreprise_id', Auth::user()->entreprise_id)->where('archived', 0)->orderBy('name', 'asc')->get();
        $tasks = Tasks::where('user_id', Auth::user()->id)->get();
        $teams = Teams::where('entreprise_id', Auth::user()->entreprise_id)->get();
        $client=Client::where('id',$project->client_id)->first();
        $invoices = invoices_history::where('project_id', $project->id)->get();
        $devis = devis_history::where('project_id', $project->id)->where('archived', 0)->orderBy('created_at', 'desc')->get();
        $entreprise_infos = Entreprise::where('id', Auth::user()->entreprise_id)->first();
        $Project_files = ProjectFile::where('project_id', $project->id)->get();
        return view('admin.projects.show', compact('projects', 'employees', 'tasks', 'teams', 'project','client', 'invoices', 'devis', 'entreprise_infos', 'Project_files'));
    }

    public function edit($id)
    {
        $project = Project::find($id);
        $teams = Teams::where('entreprise_id', Auth::user()->entreprise_id)->get();
        $client_contacts = client_contacts::where('client_id', $project->client_id)->get();
        return view('admin.projects.edit', compact('project', 'teams', 'client_contacts'));
    }
    public function update(Request $request, $id, $client_id)
    {
        $project = Project::find($id);
        $project->name = $request->name;
        $project->budget = $request->budget;
        $project->cost_per_hour = $request->cost_per_hour;
        $project->estimated_hours = $request->estimated_hours;
        $project->tva = $request->tva;
        $project->description = $request->description;
        $project->client_id = $client_id;
        $project->entreprise_id = auth()->user()->entreprise_id;
        $project->regie = $request->has('regie') ? 1 : 0;
        $project->save();

        $teams = $request->input('teams', []);
        $contacts = $request->input('contacts', []); 

        project_teams::where('project_id', $id)->delete();
        client_contacts_projects::where('project_id', $id)->delete();

        foreach ($teams as $t) {
            $team = Teams::where('id', $t)->first();

            $project_teams = new project_teams();
            $project_teams->team_id = $team->id;
            $project_teams->team_name = $team->name;
            $project_teams->entreprise_id = Auth::user()->entreprise_id;
            $project_teams->project_id = $project->id;

            $project_teams->save();
        }

        foreach ($contacts as $contact_id) { 
            $contact = client_contacts::where('id', $contact_id)->first(); 

            $contact_projects = new client_contacts_projects();

            $contact_projects->project_id = $project->id;
            $contact_projects->project_name = $project->name;
            $contact_projects->contact_id = $contact->id;
            $contact_projects->contact_name = $contact->name;
            $contact_projects->client_id = $client_id;

            $contact_projects->save();
        }

        $tasks = Tasks::where('project_id', $id)->get();
        foreach($tasks as $task){
            $task->project_name = $project->name;
            $task->save();
        }

        if(Auth::user()->hasRole('Admin')){
            return redirect()->route('admin.projects.show', $id);
        }
        if(Auth::user()->hasRole('Responsable de projet')){
            return redirect()->route('Responsable_de_projet.projects.show', $id);
        }
        if(Auth::user()->hasRole('Responsable de projet et gestionnaire')){
            return redirect()->route('Responsable_gestionnaire.projects.show', $id);
        }
    }

    public function getContacts($clientId)
    {
        $client = Client::where('id',$clientId)->first();
        $client_contacts = client_contacts::where('client_id', $clientId)
            ->whereNotNull('name')
            ->get();


        return response()->json($client_contacts);
    }


    public function destroy($id)
    {
        $project = Project::find($id);
        $client_id = $project->client_id;
        $tasks = Tasks::where('project_id', $id)->get();
        foreach ($tasks as $task) {
            $employees_tasks = employees_tasks::where('task_id', $task->id)->get();
            foreach ($employees_tasks as $employees_task) {
                $employees_task->delete();
            }
            $teams_tasks = team_tasks::where('task_id', $task->id)->get();
            foreach ($teams_tasks as $team_task) {
                $team_task->delete();
            }
            
            $task->delete();
        }
        $teams = project_teams::where('project_id', $id)->get();
        foreach ($teams as $team) {
            $team->delete();
        }
        $project->delete();
        if(Auth::user()->hasRole('Admin')){
            return redirect()->route('admin.clients.show', $client_id);
        }
        if(Auth::user()->hasRole('Responsable de projet')){
            return redirect()->route('Responsable_de_projet.clients.show', $client_id);
        }
        if(Auth::user()->hasRole('Responsable de projet et gestionnaire')){
            return redirect()->route('Responsable_gestionnaire.clients.show', $client_id);
        }
    }

    public function status(Project $project, Request $request, $status)
    {

        //Status = 0 --> Inactive
        if ($status==0) {
            $project->update(['status' => 0]);
        }
        //Status = 1 --> Working
        if($status==1){
            $project->update(['status' => 1]);
        } 
        //Status = 2 --> Done
        if($status==2) {
            $project->update(['status' => 2]);
        }
        return back()->with('message', 'project Status Updated Successfully');
    }

    public function invoice($id, Request $request)
    {
        $projectinfos = Project::where('id', $id)->first();
        $client = Client::where('id', $projectinfos->client_id)->first();
        $user_enterprise = Auth::user()->entreprise_id;
        $entreprise = Entreprise::where('id', $user_enterprise)->first();

        $invoice_link = '/admin/projects/invoice/' . $id;

        // Check if an invoice with the same details already exists and have a credit note
        $existingInvoice = invoices_history::where('link', $invoice_link)
            ->where('client_id', $client->id)
            ->where('type', 'Full')
            ->where('entreprise_id', $user_enterprise)
            ->where('Payment_status', '!=', -1)
            ->first();

        $latestInvoice = invoices_history::latest()
            ->where('entreprise_id', $user_enterprise)
            ->first();
        
        $currentYear = date('Y');
        $lastInvoiceYear = $latestInvoice ? date('Y', strtotime($latestInvoice->created_at)) : null;
        
        if ($lastInvoiceYear !== $currentYear) {
            $number = 0;
        } else {
            $number = $latestInvoice ? $latestInvoice->number : 0;
        }

        $invoice_id = sprintf('%03d', $number + 1);

        if ($request->has('hours')) {
            $budget = $projectinfos->cost_per_hour * $request->hours;
        } else if ($request->has('manualBudget')) {
            $budget = $request->manualBudget;
        }
        
        $comment = $request->has('comment') ? urldecode($request->comment) : null;

            if(!$budget){
                return back()->with('message', 'The Budget is not set');
            //}elseif($budget > ($projectinfos->budget - $projectinfos->amount_billed)){
                // Changed this condition to check if the new budget exceeds remaining budget
            //    return back()->with('message', 'The amount exceeds the remaining project budget');
            }else{
                return view('admin.projects.invoice', compact('projectinfos', 'client', 'entreprise', 'number', 'invoice_id', 'comment', 'budget'));
            }    
       
    }

    


    public function invoicebyduration($name, $duration, $cost)
    {
        $projectinfos = Project::where('name', $name)->first();
        $client = Client::where('id', $projectinfos->client_id)->first();
        $user_enterprise=Auth::user()->entreprise_id;
        $entreprise= Entreprise::where('id', $user_enterprise)->first();
        return view('admin.projects.invoicebyduration', compact('projectinfos', 'client', 'entreprise', 'duration', 'cost'));
    }

    public function invoicebyduration2(Request $request, $id, $startdate, $enddate)
{
    $projectinfos = Project::findOrFail($id); // Use findOrFail to handle case when project doesn't exist
    $client = Client::where('id', $projectinfos->client_id)->first();
    $user_enterprise = Auth::user()->entreprise_id;
    $entreprise = Entreprise::where('id', $user_enterprise)->first();

    $enddate = Carbon::parse($enddate)->endOfDay();
    $startdate = Carbon::parse($startdate)->startOfDay();

    // Parse selected task IDs from query string if provided
    $selectedTaskIds = [];
    if ($request->has('taskIds')) {
        $decoded = json_decode($request->query('taskIds'), true);
        if (is_array($decoded)) {
            $selectedTaskIds = array_values(array_filter(array_map('intval', $decoded)));
        }
    }

    $tasks_timer = tasks_timer::where('entreprise_id', auth()->user()->entreprise_id)
        ->where('start_date', '>=', $startdate)
        ->whereNotNull('end_date')
        ->where('end_date', '<=', $enddate)
        ->where('duration', '!=', 0)
        ->orderBy('start_date', 'asc')
        ->when(!empty($selectedTaskIds), function($q) use ($selectedTaskIds) {
            $q->whereIn('task_id', $selectedTaskIds);
        })
        ->get();

    $collection = collect();

    foreach ($tasks_timer as $task) {
        $task_project_id = Tasks::where('id', $task->task_id)->first();
        if ($task_project_id && $task_project_id->project_id == $id) {
            $collection->push($task); // Use push() to add the task to the collection
        }
    }

    $cost = 0;
    $duration = 0;

    foreach ($collection as $task) {
        $cost = $cost + $task->cost;
        $duration = $duration +  $task->duration;
    }

    $invoice_link = '/admin/projects/invoicebyduration2/' . $id . '/' . $startdate . '/' . $enddate;
    // Check if an invoice with the same details already exists and have a credit note
    $existingInvoice = invoices_history::where('link', $invoice_link)
        ->where('client_id', $client->id)
        ->where('type', 'Period')
        ->where('entreprise_id', $user_enterprise)
        ->where('Payment_status', '!=', -1)
        ->first();


        $latestInvoice = invoices_history::latest()
        ->where('entreprise_id', $user_enterprise)
        ->first(); // Get the latest invoice
        
        $currentYear = date('Y'); // Get the current year
        $lastInvoiceYear = $latestInvoice ? date('Y', strtotime($latestInvoice->created_at)) : null; // Get the year of the last invoice
        // Check if the last invoice year is different from the current year
        if ($lastInvoiceYear !== $currentYear) {
            $number = 0; // Initialize to 0 if the years are different
        } else {
            $number = $latestInvoice ? $latestInvoice->number : 0; // Set number to the latest invoice number or 0 if no invoice exists
        }

        $invoice_id = sprintf('%03d', $number + 1); // Initialize $invoice_id outside the if block
        $invoicerealID = 0;

        $comment = $request->has('comment') ? urldecode($request->comment) : null;

        if (!$existingInvoice) {
            return view('admin.projects.invoicebyduration', compact('projectinfos', 'client', 'entreprise', 'duration', 'cost', 'collection','invoice_id', 'invoicerealID', 'startdate', 'enddate', 'comment'));
        }else{
            return back()->with('message', 'Invoice Already Exists: ' . $entreprise->invoice_prefix . '-' . date('ym', strtotime($existingInvoice->created_at)) . $existingInvoice->number);
        }

}

    
    public function fullinvoice()
    {
        $projects = Project::where('entreprise_id', Auth::user()->entreprise_id)
        ->where(function($query) {
            $query->whereNull('AcceptedDevisProject')
                ->orWhere('AcceptedDevisProject', '=', 1);
        })
        ->where('archived', 0)
        ->orderBy('client_id')  // This will group projects by client
        ->orderBy('name')       // This will sort projects by name within each client group
        ->get();
        return view('admin.invoices.fullinvoice', compact('projects'));
    }

    public function invoicehistory()
    {
        $invoices = invoices_history::where('entreprise_id', Auth::user()->entreprise_id)
        ->where('archived', 0)
        ->orderBy('created_at', 'desc')
        ->get();
        $clients = Client::where('entreprise_id', Auth::user()->entreprise_id)->where(function($query) {
            $query->whereNull('AcceptedDevisClient')
                  ->orWhere('AcceptedDevisClient', '=', 1);
        })->orderBy('client_number')->get();
        $projects = Project::where('entreprise_id', Auth::user()->entreprise_id)->where(function($query) {
            $query->whereNull('AcceptedDevisProject')
                  ->orWhere('AcceptedDevisProject', '=', 1);
        })
        ->where('archived', 0)
        ->orderBy('client_id')
        ->get();
        $entreprise_infos = Entreprise::where('id', Auth::user()->entreprise_id)->first();

        return view('admin.invoices.invoiceHistory', compact('invoices','clients','projects', 'entreprise_infos'));
    }

    public function desactivate(invoices_history $invoice)
    {
        $invoice->Payment_status = false;
        $invoice->archived = 0;
        $invoice->save();

        return redirect()->back()->with('message', 'Invoice Marked Unpaid successfully');
    }

    public function activate(invoices_history $invoice)
    {
        $invoice->Payment_status = true;
        $invoice->archived = 1;
        $invoice->payment_date= now();
        $invoice->save();

        return redirect()->back()->with('message', 'invoice Marked Paid successfully');
    }


    public function destroyInvoice($id)
    {
        $invoice = invoices_history::find($id);
        $entreprise = Entreprise::where('id', Auth::user()->entreprise_id)->first();

        if (!$invoice) {
            if(Auth::user()->hasRole('Admin')){
                return redirect()->route('admin.invoices.invoicehistory')->with('message', 'Invoice not found.');
            }
            if(Auth::user()->hasRole('Responsable de projet')){
                return redirect()->route('Responsable_de_projet.invoices.invoicehistory')->with('message', 'Invoice not found.');
            }
            if(Auth::user()->hasRole('Responsable de projet et gestionnaire')){
                return redirect()->route('Responsable_gestionnaire.invoices.invoicehistory')->with('message', 'Invoice not found.');
            }
        }

        // Adjust 'file_path' to the actual column name if different.
        $baseDir = public_path('invoices/');
        $fileName = $entreprise->invoice_prefix.'-' . $invoice->number .'.pdf';
        $filePath = $baseDir . $fileName;

        try {
            // Attempt to delete the file if it exists
            if (file_exists($filePath)) {
                unlink($filePath); // Note: unlink() returns true on success and false on failure.
            }
            $project = Project::where('id', $invoice->project_id)->first();
            $project->amount_billed = $project->amount_billed - $invoice->total_htva;
            $project->save();
        
            // Delete the invoice from the database regardless of file existence or deletion success
            $invoice->delete();
        
            // Return success message
            if(Auth::user()->hasRole('Admin')){
                return redirect()->route('admin.invoices.invoicehistory')->with('message', 'Invoice deleted successfully.');
            }
            if(Auth::user()->hasRole('Responsable de projet')){
                return redirect()->route('Responsable_de_projet.invoices.invoicehistory')->with('message', 'Invoice deleted successfully.');
            }
            if(Auth::user()->hasRole('Responsable de projet et gestionnaire')){
                return redirect()->route('Responsable_gestionnaire.invoices.invoicehistory')->with('message', 'Invoice deleted successfully.');
            }
        } catch (Exception $e) {
            // Return error message in case of exception
            if(Auth::user()->hasRole('Admin')){
                return redirect()->route('admin.invoices.invoicehistory')->with('message', 'An error occurred: ' . $e->getMessage());
            }
            if(Auth::user()->hasRole('Responsable de projet')){
                return redirect()->route('Responsable_de_projet.invoices.invoicehistory')->with('message', 'An error occurred: ' . $e->getMessage());
            }
            if(Auth::user()->hasRole('Responsable de projet et gestionnaire')){
                return redirect()->route('Responsable_gestionnaire.invoices.invoicehistory')->with('message', 'An error occurred: ' . $e->getMessage());
            }
        }
    }


    public function downloadPeriodInvoice($invoiceNumber)
    {
        set_time_limit(300);
        $invoice = invoices_history::where('id', $invoiceNumber)->first();
        $projectinfos = Project::findOrFail($invoice->project_id);  
        $client = Client::where('id', $invoice->client_id)->first();
        $user_enterprise = Auth::user()->entreprise_id;
        $entreprise = Entreprise::where('id', $user_enterprise)->first();
        $duration= $invoice->duration;
        $invoice_id= $invoice->number;

        $startdate = $invoice->start_date;
        $enddate = $invoice->end_date;
        $enddate = Carbon::parse($enddate)->endOfDay();

        $tasks_timer = tasks_timer::where('entreprise_id', auth()->user()->entreprise_id)
        ->Where('start_date' , '>=', $startdate)
        ->whereNotNull('end_date')
        ->Where('end_date' , '<=', $enddate)
        ->orderBy('start_date', 'asc') // Order by start_date
        ->get();

       
        $collection = collect();

        foreach ($tasks_timer as $task) {
            $task_project_id = Tasks::where('id', $task->task_id)->first();
            if ($task_project_id && $task_project_id->project_id == $projectinfos->id) {
                $collection->push($task); // Use push() to add the task to the collection
            }
        }
    
        $cost = 0;
        $duration = 0;
    
        foreach ($collection as $task) {
            $cost = $cost + $task->cost;
            $duration = $duration +  $task->duration;
        }

        $pdf = PDF::loadView('admin.invoices.invoicebydurationPDF', [
            'invoice' => $invoice,
            'projectinfos' => $projectinfos,
            'client' => $client,
            'entreprise' => $entreprise,
            'duration' => $duration,
            'invoice_id' => $invoice_id,
            'collection' => $collection,
        ]); 

        // Generate filename
        $fileName = $entreprise->invoice_prefix.'-'.$invoice->created_at->format('ym').$invoice->number.'.pdf';
        $filePath = 'invoices/'.$entreprise->id.'/' . date('Y') . '/' . $fileName;

        // Save to DO Spaces
        Storage::disk('do_spaces')->put($filePath, $pdf->output(), 'public');

        // Download the PDF
        return $pdf->download($fileName);
            
    }

    public function downloadFullInvoice($invoiceNumber)
    {
        set_time_limit(300);
        $invoice = invoices_history::where('id', $invoiceNumber)->first();
        $projectinfos = Project::findOrFail($invoice->project_id); 
        $client = Client::where('id', $invoice->client_id)->first();
        $user_enterprise = Auth::user()->entreprise_id;
        $entreprise = Entreprise::where('id', $user_enterprise)->first();
        $duration= $invoice->duration;
        $invoice_id= $invoice->number;

        $pdf = PDF::loadView('admin.invoices.fullinvoicePDF', [ 
            'projectinfos' => $projectinfos,
            'client' => $client,
            'entreprise' => $entreprise,
            'number' => $invoice->number,
            'invoice_id' => $invoice_id
        ]); 

         // Generate filename
        $fileName = $entreprise->invoice_prefix.'-'.$invoice->created_at->format('ym').$invoice->number.'.pdf';
        $filePath = 'invoices/'.$entreprise->id.'/' . date('Y') . '/' . $fileName;

        // Save to DO Spaces
        Storage::disk('do_spaces')->put($filePath, $pdf->output(), 'public');

        // Download the PDF
        return $pdf->download($fileName);
    }

    
    
    public function sendManualReminder($id, $reminder)
    {
        $invoice = invoices_history::where('id', $id)->first();
        $comment = $invoice->comment;
        $projectinfos = Project::findOrFail($invoice->project_id); 
        $client = Client::where('id', $invoice->client_id)->first();
        $user_enterprise = Auth::user()->entreprise_id;
        $entreprise = Entreprise::where('id', $user_enterprise)->first();
        $duration = $invoice->duration;
        $invoice_id = $invoice->number; 
        
        if ($reminder == 1) {
            if ($invoice->first_reminder_date == null) {
                $last_reminder_date = Carbon::now()->format('d-m-Y');
            } else {
                $last_reminder_date = Carbon::parse($invoice->first_reminder_date)->format('d-m-Y');
            }
        } elseif ($reminder == 2) {
            if ($invoice->second_reminder_date == null) {
                $last_reminder_date = Carbon::now()->format('d-m-Y');
            } else {
                $last_reminder_date = Carbon::parse($invoice->second_reminder_date)->format('d-m-Y');
            }
        } elseif ($reminder == 3) {
            if ($invoice->third_reminder_date == null) {
                $last_reminder_date = Carbon::now()->format('d-m-Y');
            } else {
                $last_reminder_date = Carbon::parse($invoice->third_reminder_date)->format('d-m-Y');
            }
        } else {
            $last_reminder_date = Carbon::now()->format('d-m-Y');
        }
    
        // Determine the reminder number
        if ($invoice->sentReminder >= $reminder) {
            $reminder_number = $reminder;
        } else {
            if ($reminder == 1) {
                $reminder_number = 1;
                $invoice->sentReminder = 1;
            } elseif ($reminder == 2) {
                $reminder_number = 2;
                $invoice->sentReminder = 2;
            } elseif ($reminder == 3) {
                $reminder_number = 3;
                $invoice->sentReminder = 3;
            }
        }
    
        $client_contacts = client_contacts::where('client_id', $client->id)
            ->where('project_id', $projectinfos->id)
            ->get();
    
        if ($invoice->type == 'Period') { 
            $startdate = $invoice->start_date;
            $enddate = $invoice->end_date;
    
            $tasks_timer = tasks_timer::where('entreprise_id', auth()->user()->entreprise_id)
                ->Where('start_date', '>=', $startdate)
                ->whereNotNull('end_date')
                ->Where('end_date', '<=', $enddate)
                ->orderBy('start_date', 'asc')
                ->get();
    
            $collection = collect();
    
            foreach ($tasks_timer as $task) {
                $task_project_id = Tasks::where('id', $task->task_id)->first();
                if ($task_project_id && $task_project_id->project_id == $projectinfos->id) {
                    $collection->push($task);
                }
            }
    
            $cost = 0;
            $duration = 0;
    
            foreach ($collection as $task) {
                $cost += $task->cost;
                $duration += $task->duration;
            }

            // Ensure due_date is a Carbon instance
            $due_date = \Carbon\Carbon::parse($invoice->due_date);
            $today = \Carbon\Carbon::now();
            $isOverdue = $due_date->isPast() && !$invoice->Payment_status;
            $days_overdue = $isOverdue ? $today->diffInDays($due_date) : 0;
            
    
            $pdf = PDF::loadView('admin.invoices.invoicebydurationPDFReminder', [
                'invoice' => $invoice, 
                'projectinfos' => $projectinfos,
                'client' => $client,
                'entreprise' => $entreprise,
                'duration' => $duration,
                'invoice_id' => $invoice_id,
                'collection' => $collection,
                'comment' => $comment,
                'reminder_number' => $reminder_number,
                'last_reminder_date' => $last_reminder_date,
                'due_date' => $invoice->due_date,
                'days_overdue' => $days_overdue,
                'invoice' => $invoice,
                'isOverdue' => $isOverdue
            ]);
    
            $fileNameReminder = 'Reminder-'.$entreprise->invoice_prefix.'-'.$invoice->number.'.pdf';
            $filePath = 'invoices/'.$entreprise->id.'/' . date('Y') . '/' . $fileNameReminder;
            // Save to DO Spaces
            Storage::disk('do_spaces')->put($filePath, $pdf->output(), 'public');
    
            $invoice->save();
            return view('admin.projects.invoicebydurationReminder', compact('projectinfos', 'client', 'entreprise', 'duration', 'cost', 'collection', 'invoice_id', 'invoice', 'comment', 'reminder_number', 'last_reminder_date', 'days_overdue', 'isOverdue'));
    
        } elseif ($invoice->type == 'Partial') {
            $amount = $invoice->total_htva;
            
            $due_date = \Carbon\Carbon::parse($invoice->due_date);
            $today = \Carbon\Carbon::now();
            $isOverdue = $due_date->isPast() && !$invoice->Payment_status;
            $days_overdue = $isOverdue ? $today->diffInDays($due_date) : 0;
            
            $pdf = PDF::loadView('admin.invoices.partialInvoicePDFReminder', [
                'projectinfos' => $projectinfos,
                'client' => $client,
                'entreprise' => $entreprise,
                'number' => $invoice->number,
                'invoice_id' => $invoice_id,
                'amount' => $amount,
                'comment' => $comment,
                'reminder_number' => $reminder_number,
                'last_reminder_date' => $last_reminder_date,
                'due_date' => $invoice->due_date,
                'days_overdue' => $days_overdue,
                'invoice' => $invoice,
                'isOverdue' => $isOverdue
            ]);
    
            $fileNameReminder = 'Reminder-'.$entreprise->invoice_prefix.'-'.$invoice->number.'.pdf';
            $filePath = 'invoices/'.$entreprise->id.'/' . date('Y') . '/' . $fileNameReminder;
            // Save to DO Spaces
            Storage::disk('do_spaces')->put($filePath, $pdf->output(), 'public');
    
            $invoice->save();
            return view('admin.projects.partialInvoiceReminder', compact('projectinfos', 'client', 'entreprise', 'invoice_id', 'invoice', 'amount', 'comment', 'reminder_number', 'last_reminder_date', 'days_overdue', 'isOverdue'));
    
        } elseif ($invoice->type == 'Percentage') {
            $amount = $invoice->total_htva;
            
            $due_date = \Carbon\Carbon::parse($invoice->due_date);
            $today = \Carbon\Carbon::now();
            $isOverdue = $due_date->isPast() && !$invoice->Payment_status;
            $days_overdue = $isOverdue ? $today->diffInDays($due_date) : 0;
            
            $pdf = PDF::loadView('admin.invoices.percentageInvoicePDFReminder', [
                'projectinfos' => $projectinfos,
                'client' => $client,
                'entreprise' => $entreprise,
                'number' => $invoice->number,
                'invoice_id' => $invoice_id,
                'amount' => $amount,
                'comment' => $comment,
                'reminder_number' => $reminder_number,
                'last_reminder_date' => $last_reminder_date,
                'percentage' => $invoice->percentage,
                'due_date' => $invoice->due_date,
                'days_overdue' => $days_overdue,
                'invoice' => $invoice,
                'isOverdue' => $isOverdue
            ]);
    
            $fileNameReminder = 'Reminder-'.$entreprise->invoice_prefix.'-'.$invoice->number.'.pdf';
            $filePath = 'invoices/'.$entreprise->id.'/' . date('Y') . '/' . $fileNameReminder;
            // Save to DO Spaces
            Storage::disk('do_spaces')->put($filePath, $pdf->output(), 'public');
    
            $invoice->save();
            return view('admin.projects.percentageInvoiceReminder', compact('projectinfos', 'client', 'entreprise', 'invoice_id', 'invoice', 'amount', 'comment', 'reminder_number', 'last_reminder_date', 'days_overdue', 'isOverdue'));
    
        } 
        elseif ($invoice->type == 'Detailed') {
            $details = json_decode($invoice->details, true);
            
            $due_date = \Carbon\Carbon::parse($invoice->due_date);
            $today = \Carbon\Carbon::now();
            $isOverdue = $due_date->isPast() && !$invoice->Payment_status;
            $days_overdue = $isOverdue ? $today->diffInDays($due_date) : 0;
            
            $pdf = PDF::loadView('admin.invoices.detailedinvoicePDFReminder', [
                'projectinfos' => $projectinfos,
                'client' => $client,
                'entreprise' => $entreprise,
                'number' => $invoice->number,
                'details' => $details,
                'total_htva' => $invoice->total_htva,
                'tva_amount' => $invoice->total_tva,
                'total_ttc' => $invoice->total_ttc,
                'comment' => $comment,
                'invoice_date' => $invoice->created_at->format('d-m-Y'),
                'due_date' => Carbon::parse($invoice->due_date)->format('d-m-Y'),
                'invoice_id' => $invoice->number,
                'reminder_number' => $reminder_number,
                'last_reminder_date' => $last_reminder_date,
                'due_date' => $invoice->due_date,
                'days_overdue' => $days_overdue,
                'invoice' => $invoice,
                'isOverdue' => $isOverdue
            ]);

            $fileNameReminder = 'Reminder-'.$entreprise->invoice_prefix.'-'.$invoice->number.'.pdf';
            $filePath = 'invoices/'.$entreprise->id.'/' . date('Y') . '/' . $fileNameReminder;
            // Save to DO Spaces
            Storage::disk('do_spaces')->put($filePath, $pdf->output(), 'public');

            $invoice->save();
            return view('admin.invoices.detailedinvoiceReminder', compact('projectinfos', 'client', 'entreprise', 'invoice_id', 'invoice', 'comment', 'reminder_number', 'last_reminder_date', 'invoice', 'details', 'days_overdue', 'isOverdue'));
        }else {
            $latestInvoice = invoices_history::latest()
                ->where('entreprise_id', $user_enterprise)
                ->first();
            $number = $latestInvoice ? $latestInvoice->number : 0;
            
            $due_date = \Carbon\Carbon::parse($invoice->due_date);
            $today = \Carbon\Carbon::now();
            $isOverdue = $due_date->isPast() && !$invoice->Payment_status;
            $days_overdue = $isOverdue ? $today->diffInDays($due_date) : 0;
            
            $pdf = PDF::loadView('admin.invoices.fullinvoicePDFReminder', [
                'projectinfos' => $projectinfos,
                'client' => $client,
                'entreprise' => $entreprise,
                'number' => $invoice->number,
                'invoice_id' => $invoice_id,
                'comment' => $comment,
                'reminder_number' => $reminder_number,
                'last_reminder_date' => $last_reminder_date,
                'invoice' => $invoice,
                'due_date' => $invoice->due_date,
                'days_overdue' => $days_overdue,
                'invoice' => $invoice,
                'isOverdue' => $isOverdue
            ]);
    
            $fileNameReminder = 'Reminder-'.$entreprise->invoice_prefix.'-'.$invoice->number.'.pdf';
            $filePath = 'invoices/'.$entreprise->id.'/' . date('Y') . '/' . $fileNameReminder;
            // Save to DO Spaces
            Storage::disk('do_spaces')->put($filePath, $pdf->output(), 'public');
    
            $invoice->save();
            return view('admin.projects.invoiceReminder', compact('projectinfos', 'client', 'entreprise', 'number', 'invoice_id', 'invoice', 'comment', 'reminder_number', 'last_reminder_date', 'invoice', 'days_overdue', 'isOverdue'));
        }
    }

public function SendInvReminder(Request $request, $invoice_id, $reminder_number)
{
    try {
        $invoice = invoices_history::where('id', $invoice_id)->first();
        $projectinfos = Project::findOrFail($invoice->project_id); 
        $client = Client::where('id', $invoice->client_id)->first();
        $user_enterprise = Auth::user()->entreprise_id;
        $entreprise = Entreprise::where('id', $user_enterprise)->first();

        $invoice->sentReminder = $reminder_number;

        if ($reminder_number == 1) {
            if ($invoice->first_reminder_date == null) {
                $last_reminder_date = Carbon::now()->format('d-m-Y');
                $invoice->first_reminder_date = Carbon::now();
            } else {
                $last_reminder_date = Carbon::parse($invoice->first_reminder_date)->format('d-m-Y');
            }
        } elseif ($reminder_number == 2) {
            if ($invoice->second_reminder_date == null) {
                $last_reminder_date = Carbon::now()->format('d-m-Y');
                $invoice->second_reminder_date = Carbon::now();
            } else {
                $last_reminder_date = Carbon::parse($invoice->second_reminder_date)->format('d-m-Y');
            }
        } elseif ($reminder_number == 3) {
            if ($invoice->third_reminder_date == null) {
                $last_reminder_date = Carbon::now()->format('d-m-Y');
                $invoice->third_reminder_date = Carbon::now();
            } else {
                $last_reminder_date = Carbon::parse($invoice->third_reminder_date)->format('d-m-Y');
            }
        } else {
            $last_reminder_date = Carbon::now()->format('d-m-Y');
        }

        // Apply nl2br to convert newlines to <br> tags for HTML emails
        if($reminder_number == 1){
            $message = "<p>" . nl2br($entreprise->first_reminder) . "</p>";
        } elseif($reminder_number == 2){
            $message = "<p>" . nl2br($entreprise->second_reminder) . "</p>";
        } else {
            $message = "<p>" . nl2br($entreprise->third_reminder) . "</p>";
        }

        $message .= "<p>Facture : " . $entreprise->invoice_prefix . "-" . date('ym') . $invoice->number . "<br>
        Date d'échéance : " . Carbon::parse($invoice->due_date)->format('d-m-Y') . "<br>
        Montant : " . number_format($invoice->total_ttc, 2, ',', '') . "€" . "</p>";

        $invyear = $invoice->created_at->format('y');
        $invmonth = $invoice->created_at->format('m');
        $subject = "Invoice Reminder : " . $entreprise->invoice_prefix . "-" . $invyear . $invmonth . $invoice->number . " for Project : " . $projectinfos->name;
        $body = $message;

        // Get selected recipients from the request
        $selectedRecipients = json_decode($request->input('selected_recipients'), true);
        
        if (empty($selectedRecipients)) {
            return response()->json([
                'success' => false,
                'message' => 'Please select at least one recipient'
            ]);
        }

        // Handle file attachments
        $attachments = [];
        if ($request->hasFile('file')) {
            foreach ($request->file('file') as $file) {
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('attachments', $filename, 'do_spaces');
                $attachments[] = $path;
            }
        }

        // Send emails to all selected recipients
        foreach ($selectedRecipients as $recipient) {
            Mail::to($recipient)->send(new InvoicesEmail($subject, $body, $invoice_id, $attachments, 1));
        }

        $invoice->save();

        return response()->json([
            'success' => true,
            'message' => 'Reminder sent successfully!'
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error sending reminder: ' . $e->getMessage()
        ]);
    }
}

public function sendemailreminder()
{
    $invoices = invoices_history::where('Payment_status', NULL, 0)->get();
    $current_date = new DateTime();

    foreach ($invoices as $invoice) {
        $due_date = Carbon::createFromFormat('Y-m-d', $invoice->due_date);
        $interval = $current_date->diff($due_date);
        $days = $interval->format('%a');//now do whatever you like with $days

        // Check if the due_date is in the future and more than 7 days away
        if ($current_date > $due_date && $days > 7) { 
            $subject='Invoice Reminder';
            $body='This is a reminder to pay your invoice Number INV-'. $invoice->number.' for the project '.$invoice->project_name.'.';
            $client_email = Client::where('id', $invoice->client_id)->first();
            $recipient=$client_email->email;

            $invoice->increment('sentReminder');

            Mail::to($recipient)->send(new InvoicesEmail($subject, $body));
        }
    }

    if(Auth::user()->hasRole('Admin')){
        return redirect()->route('admin.index');
    }
    if(Auth::user()->hasRole('Responsable de projet')){
        return redirect()->route('Responsable_de_projet.index');
    }
    if(Auth::user()->hasRole('Responsable de projet et gestionnaire')){
        return redirect()->route('Responsable_gestionnaire.index');
    }
}




public function sendInvoice(Request $request, $invoiceId)
{
    try {
        $invoice = invoices_history::findOrFail($invoiceId);
        $year = $invoice->created_at->format('Y'); 
        $projectinfos = Project::findOrFail($invoice->project_id); 
        $client = Client::findOrFail($invoice->client_id);
        $entreprise = Entreprise::findOrFail(Auth::user()->entreprise_id);

        // Get basic info and prepare email content
        $email = Entreprise::where('id', Auth::user()->entreprise_id)->first()->email;
        $subject = 'Invoice for your project : ' . $projectinfos->name;
        $message = "<p>" . nl2br($entreprise->invoice_body) . "</p>";
        $message .= "<p>Facture : " . $entreprise->invoice_prefix . "-" . date('ym') . $invoice->number . "<br>
                        Date d'échéance : " . $invoice->due_date . "<br>
                        Montant : " . number_format($invoice->total_ttc, 2, '.', '') . "€" . "</p>";
        $body = $message;

        // Verify main invoice PDF exists
        $invoicePath = 'invoices/' . $entreprise->id . '/' . $year . '/' . $entreprise->invoice_prefix . '-' . $invoice->number . '.pdf';
        if (!Storage::disk('do_spaces')->exists($invoicePath)) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice PDF not found'
            ], 404);
        }

        // Create temporary directory for PDF processing
        $tempDir = storage_path('app/temp/');
        if (!File::exists($tempDir)) {
            File::makeDirectory($tempDir, 0755, true);
        }

        // Download main invoice to temp directory
        $mainInvoicePath = $tempDir . 'main_invoice.pdf';
        file_put_contents($mainInvoicePath, Storage::disk('do_spaces')->get($invoicePath));

        // Initialize PDF merger
        $pdf = new Fpdi();
        
        // Add main invoice to merged PDF
        $pageCount = $pdf->setSourceFile($mainInvoicePath);
        for ($i = 1; $i <= $pageCount; $i++) {
            $template = $pdf->importPage($i);
            $pdf->AddPage();
            $pdf->useTemplate($template);
        }

        // Handle additional files
        if ($request->hasFile('file')) {
            foreach ($request->file('file') as $file) {
                if ($file->getClientMimeType() === 'application/pdf') {
                    $tempPath = $tempDir . $file->getClientOriginalName();
                    move_uploaded_file($file->getPathname(), $tempPath);
                    
                    // Add pages from additional PDF
                    $pageCount = $pdf->setSourceFile($tempPath);
                    for ($i = 1; $i <= $pageCount; $i++) {
                        $template = $pdf->importPage($i);
                        $pdf->AddPage();
                        $pdf->useTemplate($template);
                    }
                    
                    // Clean up temp file
                    unlink($tempPath);
                }
            }
        }

        // Save merged PDF
        $mergedPdfPath = 'invoices/' . $entreprise->id . '/' . $year . '/merged_' . $invoice->number . '.pdf';
        Storage::disk('do_spaces')->put($mergedPdfPath, $pdf->Output('S'));

        // Clean up main invoice temp file
        unlink($mainInvoicePath);

        // Get selected recipients from the form
        $selectedRecipients = json_decode($request->selected_recipients, true);

        if (empty($selectedRecipients)) {
            return response()->json([
                'success' => false,
                'message' => 'No recipients selected'
            ], 400);
        }

        // Send emails with merged PDF
        $failedRecipients = [];
        foreach ($selectedRecipients as $recipient) {
            try {
                Mail::to($recipient)->send(new InvoicesEmail(
                    $subject,
                    $body,
                    $invoiceId,
                    [$mergedPdfPath] // Send only the merged PDF
                ));
            } catch (\Exception $e) {
                $failedRecipients[] = $recipient;
                \Log::error('Failed to send email to ' . $recipient, ['error' => $e->getMessage()]);
                continue;
            }
        }

        // Clean up merged PDF
        Storage::disk('do_spaces')->delete($mergedPdfPath);

        // Update invoice status and return response
        if (count($failedRecipients) < count($selectedRecipients)) {
            $invoice->sentInvoice = 1;
            $invoice->save();

            if (empty($failedRecipients)) {
                return response()->json([
                    'success' => true,
                    'message' => 'Invoice sent successfully to all recipients!'
                ]);
            } else {
                return response()->json([
                    'success' => true,
                    'message' => 'Invoice sent successfully but failed for some recipients: ' . implode(', ', $failedRecipients)
                ]);
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send invoice to any recipient'
            ], 500);
        }

    } catch (\Exception $e) {
        \Log::error('Invoice sending failed', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'invoice_id' => $invoiceId
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Failed to send invoice: ' . $e->getMessage()
        ], 500);
    }
}

public function generateDevis()
    {
        $clients = Client::where('entreprise_id', Auth::user()->entreprise_id)->where(function($query) {
            $query->whereNull('AcceptedDevisClient')
                  ->orWhere('AcceptedDevisClient', '=', 1);
        })
        ->orderby('name', 'asc')
        ->get();
        $projects = Project::where('entreprise_id', Auth::user()->entreprise_id)->where(function($query) {
            $query->whereNull('AcceptedDevisProject')
                  ->orWhere('AcceptedDevisProject', '=', 1);
        })
        ->where('archived', 0)
        ->orderby('name', 'asc')
        ->get();
        return view('admin.devis.generate', compact('clients', 'projects'));
    }

    public function saveDevisPdf(Request $request)
    {
    
            $email = Entreprise::where('id', Auth::user()->entreprise_id )->first()->email;
            $entreprise = Entreprise::where('id', Auth::user()->entreprise_id)->first();    
    
            $billingType = $request->input('billing_type', 'forfait');
            $hoursInput = $request->input('hours');
            $amountInput = $request->input('amount_per_hour');
    
            $hoursValue = is_numeric($hoursInput) ? (float) $hoursInput : 0;
            $amountValue = is_numeric($amountInput) ? (float) $amountInput : 0;
    
            $calculatedBudget = (float) $request->input('budget', 0);
            $costPerHour = (float) $request->input('cost_per_hour', 0);
    
            if ($billingType === 'horaire') {
                $calculatedBudget = $hoursValue * $amountValue;
                $costPerHour = $amountValue;
            }

            $storedHours = $billingType === 'horaire' ? $hoursValue : null;
            $storedAmountPerHour = $billingType === 'horaire' ? $amountValue : null;
    
       
        // Save the data into the devis_history table
        $devis = new devis_history();
        $devis->Project_name = $request->Project_name;
        $devis->cost_per_hour = $costPerHour;
        $devis->budget = $calculatedBudget;
        $devis->tva = $request->project_tva;
        $devis->billing_type = $billingType;
        $devis->hours = $storedHours;
        $devis->amount_per_hour = $storedAmountPerHour;
    
            if ($request->input('newclient_name')) {
                $newClient = new Client();
                $devis->emails = $request->emails; 
                $newClient->name = $request->newclient_name;
                $newClient->client_number = $request->newclient_number;
                $newClient->phone = $request->newclient_phone;
                $newClient->address = $request->newclient_street;
                $newClient->city = $request->newclient_city;
                $newClient->zip = $request->newclient_zip;
                $newClient->due_date = 0;
                $newClient->country = $request->newclient_country;
                $newClient->entreprise_id = Auth::user()->entreprise_id;
                $newClient->AcceptedDevisClient = 0;
                $newClient->save();
                $devis->client_id = $newClient->id;
                $devis->client_name = $request->newclient_name;
    
                if($request->Project_name){
                    $newProject = new Project();
                    $newProject->name = $request->Project_name; 
                    $newProject->cost_per_hour = $costPerHour;
                    $newProject->budget = $calculatedBudget;
                    $newProject->start_date = now();
                    $newProject->tva = $request->project_tva;
                    $newProject->AcceptedDevisProject = 0;
                    $newProject->client_id = $newClient->id;
                    $newProject->entreprise_id = Auth::user()->entreprise_id;
                    $newProject->save(); 
                    $devis->project_id = $newProject->id;
                }else{
                    $devis->project_id = $request->Existing_project_id;
                    $newProject = Project::where('id', $request->Existing_project_id)->first();
                    $devis->Existing_project_name = $newProject->name;
                }   
    
            }else{
                $devis->client_id = $request->client;
                $devis->client_name = Client::where('id', $request->client)->first()->name; 
                
    
                if($request->Project_name){
                    $newProject = new Project();
                    $newProject->name = $request->Project_name; 
                    $newProject->cost_per_hour = $costPerHour;
                    $newProject->budget = $calculatedBudget;
                    $newProject->start_date = now();
                    $newProject->tva = $request->project_tva;
                    $newProject->AcceptedDevisProject = 0;
                    $newProject->client_id = $request->client;
                    $newProject->entreprise_id = Auth::user()->entreprise_id;
                    $newProject->save();
                    $devis->project_id = $newProject->id;
                }else{
                    $devis->project_id = $request->Existing_project_id;
                    $newProject = Project::where('id', $request->Existing_project_id)->first();
                    $devis->Existing_project_name = $newProject->name;
                }
            }
    
        $devis->description = $request->description;
        $devis->entreprise_id = Auth::user()->entreprise_id;
    
        if ($entreprise->devis_start_number == null) {
            $lastDevisNumber = sprintf('%03d', 0);
        } else {
            $latestDevis = devis_history::where('entreprise_id', Auth::user()->entreprise_id)
                ->orderBy('devis_number', 'desc')  // Order by devis_number instead of created_at
                ->first();
            
            if ($latestDevis == null) {
                // No existing devis, use the start number from entreprise
                $lastDevisNumber = $entreprise->devis_start_number;
                $devis->hasStartNumber = 1;
            } else {
                // Get the highest devis number currently in use
                $formatLength = strlen($latestDevis->devis_number);
                $lastDevisNumber = $latestDevis->devis_number;
            }
        }
    
        // Always increment from the last number
        $nextNumber = intval($lastDevisNumber) + 1;
        $formatLength = strlen($lastDevisNumber); // Use the same format length as the last number
        $devis->devis_number = sprintf("%0{$formatLength}d", $nextNumber);
    
        $devis->save();
    
        if( $request->input('contacts') ){
            $selectedContacts = $request->input('contacts');
            $contacts =client_contacts::whereIn('id', $selectedContacts)->get();
            foreach ($contacts as $c) {
                $client_contacts_devis = new client_contacts_devis();
                $client_contacts_devis->devis_id = $devis->id;
                $client_contacts_devis->contact_id = $c->id;
                $client_contacts_devis->client_id = $request->client;
                $client_contacts_devis->devis_number = $devis->devis_number;
                $client_contacts_devis->entreprise_id = Auth::user()->entreprise_id;
                $client_contacts_devis->save(); 
            }
        }
    
        $entreprise = Entreprise::where('id', Auth::user()->entreprise_id)->first();
    
        $tasks = $request->input('task');
        $taskBudget = $request->input('taskBudget');
    
        // Add this new code to save task details
        if ($tasks && $taskBudget) {
            foreach ($tasks as $index => $task) {
                if (!empty($task) && isset($taskBudget[$index])) {
                    $devis_detail = new devis_details();
                    $devis_detail->devis_id = $devis->id;
                    $devis_detail->task_details = $task;
                    $devis_detail->task_budget = $taskBudget[$index];
                    $devis_detail->entreprise_id = Auth::user()->entreprise_id;
                    $devis_detail->save();
                }
            }
        }
        
    
        if($request->Project_name){
            $project_name = $request->Project_name;
        }else{
            $project_name = $devis->Existing_project_name;
        } 
    
    
        // Generate a PDF file of the devis
        $pdf = PDF::loadView('admin.devis.view', [
            'project_name' => $project_name,
            'prix_unitaire' => $costPerHour,
            'budget' => $calculatedBudget,
            'tva' => $devis->tva,
            'description' => $request->description,
            'entreprise' => $entreprise,
            'devisNum' => $devis->devis_number,
            'cost_per_hour' => $costPerHour,
            'tasks' => $tasks,
            'taskBudget' => $taskBudget,
            'devis_id' => $devis->id,
            'billing_type' => $billingType,
            'hours' => $hoursValue,
            'amount_per_hour' => $amountValue
        ]);
    
        // Save the PDF to a do_spaces file
        $filePath = 'devis/' . $entreprise->id . '/' . $devis->devis_number . '.pdf';
        Storage::disk('do_spaces')->put($filePath, $pdf->output(), 'public');
    
        //get the full url directly
        $fullUrl = Storage::disk('do_spaces')->url($filePath);
    
        return redirect($fullUrl);
    
        
    }

public function duplicateDevis($devis_id){
    $devis = devis_history::where('id', $devis_id)->first();
    $devis_details = devis_details::where('devis_id', $devis_id)->get();
    $clients = Client::where('entreprise_id', Auth::user()->entreprise_id)->where(function($query) {
        $query->whereNull('AcceptedDevisClient')
              ->orWhere('AcceptedDevisClient', '=', 1);
    })
    ->orderby('name', 'asc')
    ->get();
    $projects = Project::where('entreprise_id', Auth::user()->entreprise_id)->where(function($query) {
        $query->whereNull('AcceptedDevisProject')
              ->orWhere('AcceptedDevisProject', '=', 1);
    })
    ->where('archived', 0)
    ->orderby('name', 'asc')
    ->get();

    // Get the client
    $client = Client::where('id', $devis->client_id)->first();

    // Get selected contacts
    $selectedContacts = client_contacts_devis::where('devis_id', $devis->id)
        ->pluck('contact_id')
        ->toArray();

    return view('admin.devis.duplicate', compact('devis', 'clients', 'projects', 'devis_details', 'client', 'selectedContacts'));
}

public function saveDuplicateDevis(Request $request)
{

        $email = Entreprise::where('id', Auth::user()->entreprise_id )->first()->email;
        $entreprise = Entreprise::where('id', Auth::user()->entreprise_id)->first();    

        // Handle billing type (forfait / horaire) similarly to saveDevisPdf
        $billingType = $request->input('billing_type', 'forfait');
        $hoursInput = $request->input('hours');
        $amountInput = $request->input('amount_per_hour');

        $hoursValue = is_numeric($hoursInput) ? (float) $hoursInput : 0;
        $amountValue = is_numeric($amountInput) ? (float) $amountInput : 0;

        $calculatedBudget = (float) $request->input('budget', 0);
        $costPerHour = (float) $request->input('cost_per_hour', 0);

        if ($billingType === 'horaire') {
            $calculatedBudget = $hoursValue * $amountValue;
            $costPerHour = $amountValue;
        }

        $storedHours = $billingType === 'horaire' ? $hoursValue : null;
        $storedAmountPerHour = $billingType === 'horaire' ? $amountValue : null;
   
    // Save the data into the devis_history table
    $devis = new devis_history();
    $devis->Project_name = $request->Project_name;
    $devis->cost_per_hour = $costPerHour;
    $devis->budget = $calculatedBudget;
    $devis->tva = $request->project_tva;
    $devis->billing_type = $billingType;
    $devis->hours = $storedHours;
    $devis->amount_per_hour = $storedAmountPerHour;

        if ($request->input('newclient_name')) {
            $newClient = new Client();
            $devis->emails = $request->emails; 
            $newClient->name = $request->newclient_name;
            $newClient->client_number = $request->newclient_number;
            $newClient->phone = $request->newclient_phone;
            $newClient->address = $request->newclient_street;
            $newClient->city = $request->newclient_city;
            $newClient->zip = $request->newclient_zip;
            $newClient->due_date = 0;
            $newClient->country = $request->newclient_country;
            $newClient->entreprise_id = Auth::user()->entreprise_id;
            $newClient->AcceptedDevisClient = 0;
            $newClient->save();
            $devis->client_id = $newClient->id;
            $devis->client_name = $request->newclient_name;

            if($request->Project_name){
                $newProject = new Project();
                $newProject->name = $request->Project_name; 
                $newProject->cost_per_hour = $request->cost_per_hour;
                $newProject->budget = $request->budget;
                $newProject->start_date = now();
                $newProject->tva = $request->project_tva;
                $newProject->AcceptedDevisProject = 0;
                $newProject->client_id = $newClient->id;
                $newProject->entreprise_id = Auth::user()->entreprise_id;
                $newProject->save(); 
                $devis->project_id = $newProject->id;
            }else{
                $devis->project_id = $request->Existing_project_id;
                $newProject = Project::where('id', $request->Existing_project_id)->first();
                $devis->Existing_project_name = $newProject->name;
            }   

        }else{
            $devis->client_id = $request->client;
            $devis->client_name = Client::where('id', $request->client)->first()->name;

            if($request->Project_name){
                $newProject = new Project();
                $newProject->name = $request->Project_name; 
                $newProject->cost_per_hour = $request->cost_per_hour;
                $newProject->budget = $request->budget;
                $newProject->start_date = now();
                $newProject->tva = $request->project_tva;
                $newProject->AcceptedDevisProject = 0;
                $newProject->client_id = $request->client;
                $newProject->entreprise_id = Auth::user()->entreprise_id;
                $newProject->save();
                $devis->project_id = $newProject->id;
            }else{
                $devis->project_id = $request->Existing_project_id;
                $newProject = Project::where('id', $request->Existing_project_id)->first();
                $devis->Existing_project_name = $newProject->name;
            }
        }

    $devis->description = $request->description;
    $devis->entreprise_id = Auth::user()->entreprise_id;

    if ($entreprise->devis_start_number == null) {
        $lastDevisNumber = sprintf('%03d', 0);
    } else {
        $latestDevis = devis_history::where('entreprise_id', Auth::user()->entreprise_id)
            ->orderBy('devis_number', 'desc')  // Order by devis_number instead of created_at
            ->first();
        
        if ($latestDevis == null) {
            // No existing devis, use the start number from entreprise
            $lastDevisNumber = $entreprise->devis_start_number;
            $devis->hasStartNumber = 1;
        } else {
            // Get the highest devis number currently in use
            $formatLength = strlen($latestDevis->devis_number);
            $lastDevisNumber = $latestDevis->devis_number;
        }
    }

    // Always increment from the last number
    $nextNumber = intval($lastDevisNumber) + 1;
    $formatLength = strlen($lastDevisNumber); // Use the same format length as the last number
    $devis->devis_number = sprintf("%0{$formatLength}d", $nextNumber);

    $devis->save();

    if( $request->input('contacts') ){
        $selectedContacts = $request->input('contacts');
        $contacts =client_contacts::whereIn('id', $selectedContacts)->get();
        foreach ($contacts as $c) {
            $client_contacts_devis = new client_contacts_devis();
            $client_contacts_devis->devis_id = $devis->id;
            $client_contacts_devis->contact_id = $c->id;
            $client_contacts_devis->client_id = $request->client;
            $client_contacts_devis->devis_number = $devis->devis_number;
            $client_contacts_devis->entreprise_id = Auth::user()->entreprise_id;
            $client_contacts_devis->save(); 
        }
    }

    $entreprise = Entreprise::where('id', Auth::user()->entreprise_id)->first();

    $tasks = $request->input('task');
    $taskBudget = $request->input('taskBudget');


     // Add this new code to save task details
     if ($tasks && $taskBudget) {
        foreach ($tasks as $index => $task) {
            if (!empty($task) && isset($taskBudget[$index])) {
                $devis_detail = new devis_details();
                $devis_detail->devis_id = $devis->id;
                $devis_detail->task_details = $task;
                $devis_detail->task_budget = $taskBudget[$index];
                $devis_detail->entreprise_id = Auth::user()->entreprise_id;
                $devis_detail->save();
            }
        }
    }

    if($request->Project_name){
        $project_name = $request->Project_name;
    }else{
        $project_name = $devis->Existing_project_name;
    } 


    // Generate a PDF file of the devis
    $pdf = PDF::loadView('admin.devis.view', [
        'project_name' => $project_name,
        'prix_unitaire' => $request->cost_per_hour,
        'budget' => $request->budget,
        'tva' => $devis->tva,
        'description' => $request->description,
        'entreprise' => $entreprise,
        'devisNum' => $devis->devis_number,
        'cost_per_hour' => $request->cost_per_hour,
        'tasks' => $tasks,
        'taskBudget' => $taskBudget,
        'devis_id' => $devis->id
    ]);

    // Save the PDF to a do_spaces file
    $filePath = 'devis/' . $entreprise->id . '/' . $devis->devis_number . '.pdf';
    Storage::disk('do_spaces')->put($filePath, $pdf->output(), 'public');

    //get the full url directly
    $fullUrl = Storage::disk('do_spaces')->url($filePath);

    return redirect($fullUrl);

    
}

public function sendDevis(Request $request)
{
    try {
        $entreprise = Entreprise::where('id', Auth::user()->entreprise_id)->first();
        $devis = devis_history::where('id', $request->devis_id)->where('entreprise_id', Auth::user()->entreprise_id)->first();
        
        if (!$devis) {
            return response()->json([
                'success' => false,
                'message' => 'Devis not found'
            ]);
        }

        $total_ttc = $devis->budget + ($devis->budget * ($devis->tva / 100));
        $subject = 'Devis for the project : ' . ($devis->Project_name ? $devis->Project_name : $devis->Existing_project_name);
        $message = "<p>" . nl2br($entreprise->devis_body) . "</p>";
        $message .= "<p>Devis : " . $entreprise->devis_prefix . "-" . $devis->devis_number . "<br>
                        Montant : " . $total_ttc."€" . "</p>"; 
        $body = $message;

        // Get selected recipients
        $selectedRecipients = json_decode($request->recipients, true);
        if (empty($selectedRecipients)) {
            return response()->json([
                'success' => false,
                'message' => 'No recipients selected'
            ]);
        }
        \Log::info('Processing devis attachments for merging using Fpdi.', ['devis_id' => $devis->id, 'has_attachments' => $request->hasFile('attachments')]);
        // Handle attachments and merge with devis PDF
        $filesToMerge = [];
        $tempFiles = [];
        $mergedPdfPath = null; // Initialize to null

        try {
            \Log::info('Processing devis attachments for merging using Fpdi.', ['devis_id' => $devis->id, 'has_attachments' => $request->hasFile('attachments')]);

            // Get the main devis PDF path and content from storage
            $devisPath = 'devis/' . $entreprise->id . '/' . $devis->devis_number . '.pdf';
            $devisContent = Storage::disk('do_spaces')->get($devisPath);

            if ($devisContent === null) {
                 throw new \Exception('Main devis PDF not found in storage.');
            }

            \Log::info('Main devis PDF retrieved from storage.', ['path' => $devisPath, 'content_length' => strlen($devisContent)]);

            // Create a temporary directory for PDF operations
            $tempDir = storage_path('app/temp_devis_merge_' . uniqid()); // Use unique temp dir
            if (!file_exists($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            // Save the main devis PDF to a temporary file
            $tempDevisPath = $tempDir . '/devis_' . uniqid() . '.pdf';
            file_put_contents($tempDevisPath, $devisContent);
            $filesToMerge[] = $tempDevisPath;
            $tempFiles[] = $tempDevisPath;

            // Process uploaded attachments
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    if ($file->getClientOriginalExtension() === 'pdf') {
                        $tempAttachmentPath = $tempDir . '/attachment_' . uniqid() . '.pdf';
                        // Move uploaded file to temporary location
                        $file->move($tempDir, basename($tempAttachmentPath));
                        \Log::info('Uploaded attachment saved to temporary file.', ['temp_path' => $tempAttachmentPath, 'original_name' => $file->getClientOriginalName()]);
                        $filesToMerge[] = $tempAttachmentPath;
                        $tempFiles[] = $tempAttachmentPath;
                    } else {
                         \Log::warning('Skipping non-PDF attachment for devis merge:', ['filename' => $file->getClientOriginalName()]);
                    }
                }
            }

            // Merge PDFs using Fpdi if there are files to merge (at least the main devis)
            if (!empty($filesToMerge)) {
                $pdf = new Fpdi();
                
                foreach ($filesToMerge as $fileToMerge) {
                    try {
                         $pageCount = $pdf->setSourceFile($fileToMerge);
                         for ($i = 1; $i <= $pageCount; $i++) {
                             $template = $pdf->importPage($i);
                             $pdf->AddPage();
                             $pdf->useTemplate($template);
                         }
                         \Log::info('Added pages from file to merger.', ['file' => $fileToMerge, 'page_count' => $pageCount]);
                    } catch (\Exception $e) {
                         \Log::error('Error adding file to Fpdi merger:' . $e->getMessage(), ['file' => $fileToMerge, 'trace' => $e->getTraceAsString()]);
                         // Decide how to handle this error: skip the file, throw an exception, etc.
                         // For now, we'll log and continue with other files.
                    }
                }

                // Save merged PDF to a temporary file
                $mergedPdfPath = $tempDir . '/merged_devis_' . uniqid() . '.pdf';
                $pdfOutput = $pdf->Output('S'); // Get PDF output as a string
                file_put_contents($mergedPdfPath, $pdfOutput);

                \Log::info('PDFs merged successfully to temporary file:', ['path' => $mergedPdfPath]);

                // The merged PDF is now at $mergedPdfPath. We will pass this path to the Mailable.
                // The Mailable will attach it using fromPath.

            } else {
                 \Log::warning('No PDFs found to merge for devis:', ['devis_id' => $devis->id]);
                 // If no files to merge, there's no merged PDF. We might not send an attachment or handle differently.
                 // Based on the previous logic, we'll proceed without an attachment if merging didn't happen.
            }

        } catch (\Exception $e) {
            \Log::error('Error during PDF merging process in controller:' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            // If an exception occurs during merging, ensure mergedPdfPath is null so no attachment is attempted.
            $mergedPdfPath = null;
        } finally {
            // Clean up temporary files and the temporary directory
            foreach ($tempFiles as $tempFile) {
                if (file_exists($tempFile)) {
                    unlink($tempFile);
                     \Log::debug('Cleaned up temporary file.', ['path' => $tempFile]);
                }
            }
             // Clean up the temporary merged file if it was created
            if ($mergedPdfPath && file_exists($mergedPdfPath)) {
                // We will leave the merged file here for the Mailable to pick up.
                // It will be cleaned up by the system or a scheduled job later, or we can add cleanup after sending the email.
                 $tempFiles[] = $mergedPdfPath; // Add merged file to tempFiles for eventual cleanup
            }
             // Clean up the temporary directory if it exists and is empty (or contains only the merged PDF initially)
            if (is_dir($tempDir)) {
                $items = scandir($tempDir);
                 // If the directory contains only . and .., or . , .. and the merged file, we can clean it up now
                if (count($items) <= 2 || (count($items) == 3 && $mergedPdfPath && in_array(basename($mergedPdfPath), $items))) {
                     // We will clean up the directory and the merged file after sending the email.
                }
            }
        }

        // Send emails to each selected recipient
        $failedRecipients = [];
        $successCount = 0;

        foreach ($selectedRecipients as $recipient) {
            try {
                // Pass the path of the merged PDF (or null if merging failed) to the Mailable.
                // The Mailable will handle attaching the file from this path.
                Mail::to($recipient)->send(new DevisEmail($subject, $body, $devis->id, $mergedPdfPath)); // Pass mergedPdfPath
                $successCount++;
                $devis->sent = 1;
                $devis->save();

                // Log success
                \Log::info('Successfully sent devis email to:', ['email' => $recipient, 'devis_id' => $devis->id]);

            } catch (\Exception $e) {
                $failedRecipients[] = $recipient;
                \Log::error('Failed to send devis email to ' . $recipient, [
                    'error' => $e->getMessage(),
                    'devis_id' => $devis->id,
                    'recipient' => $recipient,
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        // After sending all emails, upload the merged PDF to DigitalOcean Spaces and then clean up temporary files
        if ($mergedPdfPath && file_exists($mergedPdfPath)) {
            try {
                // Define the target path in DigitalOcean Spaces (same as original devis)
                $devisPath = 'devis/' . $entreprise->id . '/' . $devis->devis_number . '.pdf';

                // Read the content of the temporary merged PDF file
                $mergedContent = file_get_contents($mergedPdfPath);

                // Upload the merged PDF content back to DigitalOcean Spaces, overwriting the original devis
                Storage::disk('do_spaces')->put($devisPath, $mergedContent);
                \Log::info('Merged PDF successfully uploaded to DigitalOcean Spaces.', ['path' => $devisPath]);
            } catch (\Exception $e) {
                 \Log::error('Error uploading merged PDF to DigitalOcean Spaces: ' . $e->getMessage(), ['path' => $devisPath, 'trace' => $e->getTraceAsString()]);
                 // Optionally, handle this error, e.g., notify admin
            } finally {
                // Clean up the temporary merged file
                unlink($mergedPdfPath);
                 \Log::debug('Cleaned up temporary merged PDF file after upload.', ['path' => $mergedPdfPath]);
            }
        }

        // Clean up the temporary directory if it exists and is empty
        if (is_dir($tempDir)) {
             $items = scandir($tempDir);
            // Check if directory contains only . and ..
            if (count($items) <= 2) {
                 rmdir($tempDir);
                 \Log::debug('Cleaned up temporary devis merge directory.', ['path' => $tempDir]);
            }
        }

        // Prepare response message
        if ($successCount > 0) {
            $message = 'Devis sent successfully to ' . $successCount . ' recipient(s)';
            if (!empty($failedRecipients)) {
                $message .= '. Failed to send to: ' . implode(', ', $failedRecipients);
            }
            return response()->json([
                'success' => true,
                'message' => $message
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to send devis to any recipient'
        ]);

    } catch (\Exception $e) {
        \Log::error('Error sending devis:', [
            'devis_id' => $request->devis_id,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        return response()->json([
            'success' => false,
            'message' => 'Error sending devis: ' . $e->getMessage()
        ]);
    }
}

public function devishistory()
{
    $entreprise = Entreprise::where('id', Auth::user()->entreprise_id)->first();
    $devis = devis_history::where('entreprise_id', Auth::user()->entreprise_id)
    ->where('archived', 0)
    ->orderBy('created_at', 'desc')
    ->get();
    $clients = Client::where('entreprise_id', Auth::user()->entreprise_id)->where(function($query) {
        $query->whereNull('AcceptedDevisClient')
              ->orWhere('AcceptedDevisClient', '=', 1);
    })->get();
    $projects = Project::where('entreprise_id', Auth::user()->entreprise_id)->where(function($query) {
            $query->whereNull('AcceptedDevisProject')
                  ->orWhere('AcceptedDevisProject', '=', 1);
        })
        ->where('archived', 0)
        ->get();
    return view('admin.devis.devisHistory', compact('devis','clients','projects', 'entreprise'));
}


public function downloadDevis($id)
{
    $entreprise = Entreprise::where('id', Auth::user()->entreprise_id)->first();
    // Define the file path from do_spaces
    $filePath = 'devis/' . $entreprise->id . '/' . $id . '.pdf';
    $fullUrl = Storage::disk('do_spaces')->get($filePath);

    // Get file from DO Spaces and return it
    return response($fullUrl, 200)
        ->header('Content-Type', 'application/pdf')
        ->header('Content-Disposition', 'inline; filename="'.$entreprise->devis_prefix.'-'.$id.'.pdf"');
    
}



public function acceptDevis(devis_history $devis) 
    {
        $devis->acceptanceStatus = 1;
        $devis->save();

        if($devis->Project_name){
            $project = Project::where('id', $devis->project_id)->first();
            $project->AcceptedDevisProject = 1;
            $project->save();
        }

        $prj = Project::where('id', $devis->project_id)->first();
        $prj_devis_budgets = devis_history::where('project_id', $prj->id)->where('acceptanceStatus', 1)->sum('budget'); // Corrected here
        $prj->budget = $prj_devis_budgets;
        $prj->save(); 
       
        $client = Client::where('id', $devis->client_id)->first();
        $client->AcceptedDevisClient = 1;

        $client->save();

        return redirect()->back()->with('message', 'devis deactivated successfully');
    }

    public function declineDevis(devis_history $devis)
    {
        $devis->acceptanceStatus = 2;
        $devis->save();

        if($devis->Project_name){
            $project = Project::where('id', $devis->project_id)->first();
            $project->AcceptedDevisProject = 0;
            $project->save();
        }

        $client = Client::where('id', $devis->client_id)->first();
        
        // Check if client has any active projects
        $hasActiveProjects = Project::where('client_id', $client->id)
            ->where(function($query) {
                $query->whereNull('AcceptedDevisProject')
                      ->orWhere('AcceptedDevisProject', '=', 1);
            })
            ->where('archived', 0)
            ->exists();

        // Only update AcceptedDevisClient if client has no active projects
        if($client->AcceptedDevisClient != null && !$hasActiveProjects){
            $client->AcceptedDevisClient = 0;
            $client->save();
        }
        
        return redirect()->back()->with('message', 'devis activated successfully');
    }


public function destroyDevis($id)
    {
        $devis = devis_history::find($id);

        if($devis->acceptanceStatus == 0){
            $project = Project::where('id', $devis->project_id)->where('AcceptedDevisProject', 0)->first();
            if($project){
                $project->delete();
            }

            if( $client = Client::where('id', $devis->client_id)->where('AcceptedDevisClient', 0)->first()){
                if($client){
                    $client->delete();
                }
                
            }
            
        }
        $devis->delete();
        
        $devis = devis_history::where('entreprise_id', Auth::user()->entreprise_id)->get();
        $clients = Client::where('entreprise_id', Auth::user()->entreprise_id)->where(function($query) {
            $query->whereNull('AcceptedDevisClient')
                  ->orWhere('AcceptedDevisClient', '=', 1);
        })->get();
        $projects = Project::where('entreprise_id', Auth::user()->entreprise_id)->where(function($query) {
            $query->whereNull('AcceptedDevisProject')
                  ->orWhere('AcceptedDevisProject', '=', 1);
        })
        ->where('archived', 0)
        ->get();

        $entreprise = Entreprise::where('id', Auth::user()->entreprise_id)->first();

        return view('admin.devis.devisHistory', compact('devis', 'clients', 'projects', 'entreprise'))->with('message', 'Devis deleted successfully.');
}


public function servePdf($invoiceName)
{
    $entreprise = Entreprise::where('id', Auth::user()->entreprise_id)->first();

    $invoice = invoices_history::where('id', $invoiceName)->where('entreprise_id', Auth::user()->entreprise_id)->first();
    $invoiceCreatedAt = $invoice->created_at;
    $invoiceYear = $invoiceCreatedAt->format('Y');
    $invoiceMonth = $invoiceCreatedAt->format('m');
    // Create the filename
    $fileName = $entreprise->invoice_prefix . '-' .date('y').$invoiceMonth.$invoice->number.'.pdf';
    $filePath = 'invoices/' . $entreprise->id . '/' . $invoiceYear . '/' . $entreprise->invoice_prefix . '-'.$invoice->number.'.pdf';

    // Get file from DO Spaces and return it
    $file = Storage::disk('do_spaces')->get($filePath);
    
    return response($file, 200)
        ->header('Content-Type', 'application/pdf')
        ->header('Content-Disposition', 'inline; filename="' . $fileName . '"')
        ->header('Content-Length', strlen($file));
}

public function parameters(){
    $entreprise = Entreprise::where('id', Auth::user()->entreprise_id)->first();
    return view('admin.invoices.parameters', compact('entreprise'));
}

public function devisparameters(){
    $entreprise = Entreprise::where('id', Auth::user()->entreprise_id)->first();
    return view('admin.devis.parameters', compact('entreprise'));
}

public function downloadProjectsRepports()
{
    $projects = Project::where('entreprise_id', Auth::user()->entreprise_id)->where(function($query) {
            $query->whereNull('AcceptedDevisProject')
                  ->orWhere('AcceptedDevisProject', '=', 1);
        })
        ->orderBy('client_id', 'asc')
        ->where('archived', 0)
        ->get();
    $entreprise = Entreprise::where('id', auth()->user()->entreprise_id)->first();
    $pdf = PDF::loadView('admin.projects.downloadRepports', [
        'projects' => $projects,
        'entreprise' => $entreprise,
    ]); 
    return $pdf->stream('projectsRepports.pdf');
}



public function downloadInvoicesRepports(Request $request)
{
    $query = invoices_history::where('entreprise_id', auth()->user()->entreprise_id)
    ->orderBy('created_at', 'desc');

    // Apply date range if provided
    $startDate = $request->get('startDate');
    $endDate = $request->get('endDate');
    if ($startDate && $endDate) {
        $query->whereDate('created_at', '>=', $startDate)
              ->whereDate('created_at', '<=', $endDate);
    }

    // Apply optional filters (paid/unpaid)
    $filtersParam = $request->get('filters'); // comma-separated: paid,unpaid
    $parts = collect($filtersParam ? explode(',', $filtersParam) : [])
        ->map(fn($s) => strtolower(trim($s)))
        ->filter(fn($s) => in_array($s, ['paid','unpaid']))
        ->unique()
        ->values();

    // Type filtering
    $typesParam = $request->get('types'); // comma-separated: partial,percentage,full,period,detailed
    if ($typesParam) {
        $validTypes = ['partial','percentage','full','period','detailed'];
        $typeParts = collect(explode(',', $typesParam))
            ->map(fn($s) => strtolower(trim($s)))
            ->filter(fn($s) => in_array($s, $validTypes))
            ->unique()
            ->values();
        if ($typeParts->count() > 0) {
            // Stored types in DB seem capitalized based on blade usage: Partial, Percentage, Period, Detailed, Full
            $mapToDb = [
                'partial' => 'Partial',
                'percentage' => 'Percentage',
                'full' => 'Full',
                'period' => 'Period',
                'detailed' => 'Detailed',
            ];
            $query->whereIn('type', $typeParts->map(fn($t) => $mapToDb[$t])->all());
        }
    }

    // Credit note applied filtering (use invoices_history.Payment_status column)
    $creditNoteFilter = $request->boolean('credit_note');

    // Combine selected categories with OR logic
    if ($parts->count() > 0 || $creditNoteFilter) {
        $query->where(function($outer) use ($parts, $creditNoteFilter) {
            if ($parts->contains('paid')) {
                $outer->orWhere('Payment_status', 1);
            }
            if ($parts->contains('unpaid')) {
                $outer->orWhere(function($q) {
                    $q->where(function($qq){ $qq->where('Payment_status', 0)->orWhereNull('Payment_status'); })
                      ->where(function($qq){ $qq->whereNull('archived')->orWhere('archived', 0); });
                });
            }
            if ($creditNoteFilter) {
                $outer->orWhere('Payment_status', -1);
            }
        });
    }

    // If no category/type filters specified, exclude archived by default
    if ($parts->count() === 0 && !$typesParam && !$creditNoteFilter) {
        $query->where(function($q){ $q->whereNull('archived')->orWhere('archived', 0); });
    }

    $invoices = $query->get();
    $entreprise = Entreprise::where('id', auth()->user()->entreprise_id)->first();

    $totalPaid = $invoices->where('Payment_status', 1)->sum('total_ttc');
    $totalUnpaid = $invoices->where('Payment_status', 0)->sum('total_ttc');
    $totalCreditNote = $invoices->where('Payment_status', -1)->sum('total_ttc');
    $totaltva = $invoices->sum('total_tva');
    $total = $totalPaid + $totalUnpaid + $totalCreditNote + $totaltva;
    
    $pdf = PDF::loadView('admin.invoices.downloadRepports', [
        'invoices' => $invoices,
        'entreprise' => $entreprise,
        'totalPaid' => $totalPaid,
        'totalUnpaid' => $totalUnpaid,
        'totaltva' => $totaltva,
        'total' => $total,
        'totalCreditNote' => $totalCreditNote,
    ]);

    return $pdf->stream('invoicesRepports.pdf');
}


public function downloadDevisRepports(Request $request)
{
    $query = devis_history::where('entreprise_id', auth()->user()->entreprise_id)
    ->orderBy('created_at', 'desc');

    // Apply optional filters and archived flag
    $archivedFlag = $request->boolean('archived');
    $filtersParam = $request->get('filters'); // comma-separated values: accepted,declined,pending

    // Acceptance status map
    $statusMap = [
        'accepted' => 1,
        'declined' => 2,
        'pending' => 0,
    ];

    if ($filtersParam) {
        $parts = collect(explode(',', $filtersParam))
            ->map(fn($s) => strtolower(trim($s)))
            ->filter(fn($s) => isset($statusMap[$s]))
            ->unique()
            ->values();

        if ($parts->count() > 0) {
            $query->whereIn('acceptanceStatus', $parts->map(fn($k) => $statusMap[$k])->all());
        }
    }

    // Archived handling: by default exclude archived unless user explicitly includes them
    if (!$archivedFlag) {
        $query->where(function($q){ $q->whereNull('archived')->orWhere('archived', 0); });
    }

    $devis = $query->orderBy('created_at', 'asc')->get();

    $entreprise = Entreprise::where('id', auth()->user()->entreprise_id)->first();

    $pdf = PDF::loadView('admin.devis.downloadRepports', [
        'devis' => $devis,
        'entreprise' => $entreprise,
    ]);

    return $pdf->stream('devisRepports.pdf');
}



public function generateCreditNote($id, Request $request)
{
  
    $type = $request->query('type', 'full');
    $amount = $request->query('amount');
    $budget = 0;
    $partial = false;
    $credit_note ; 

    $invoice = invoices_history::where('id', $id)->first();
    $projectinfos = Project::findOrFail($invoice->project_id); 
    $client = Client::where('id', $invoice->client_id)->first();
    $user_enterprise = Auth::user()->entreprise_id;
    $entreprise = Entreprise::where('id', $user_enterprise)->first();
    $duration= $invoice->duration;
    $invoice_id= $invoice->number; 

    $client_contacts = client_contacts::where('client_id', $client->id)
    ->where('project_id', $projectinfos->id)
    ->get();

    
    if($invoice->type=='Period'){ 

        $startdate = $invoice->start_date;
        $enddate = $invoice->end_date;

        $enddate = Carbon::parse($enddate)->endOfDay();

        $tasks_timer = tasks_timer::where('entreprise_id', auth()->user()->entreprise_id)
        ->Where('start_date' , '>=', $startdate)
        ->whereNotNull('end_date')
        ->Where('end_date' , '<=', $enddate)
        ->orderBy('start_date', 'asc') // Order by start_date
        ->get();

        $collection = collect();

        foreach ($tasks_timer as $task) {
            $task_project_id = Tasks::where('id', $task->task_id)->first();
            if ($task_project_id && $task_project_id->project_id == $projectinfos->id) {
                $collection->push($task); // Use push() to add the task to the collection
            }
        }
    
        $cost = 0;
        $duration = 0;
    
        foreach ($collection as $task) {
            $cost = $cost + $task->cost;
            $duration = $duration +  $task->duration;
        }

        if ($type === 'partial' && $amount) {
            $prixtotal = $amount; 
            $partial = true;
            $last_credit_note_number = credit_note::where('entreprise_id', $invoice->entreprise_id)->max('credit_note_number');
        }
        else {
            $prixtotal = $invoice->total_htva;

            $last_credit_note_number = credit_note::where('entreprise_id', $invoice->entreprise_id)->max('credit_note_number');

            // Check if a similar credit note already exists
            $existing_credit_note = credit_note::where('number', $invoice->number)
            ->where('project_id', $invoice->project_id)
            ->where('client_id', $invoice->client_id)
            ->where('credit_note_type', 'Full')
            ->where('total_htva', $prixtotal)
            ->first();
    
        }

        return view('admin.creditNote.invoicebydurationcreditNote', compact('projectinfos', 'client', 'entreprise', 'duration', 'cost', 'collection','invoice_id', 'prixtotal', 'partial', 'last_credit_note_number', 'type', 'invoice'));

    }elseif($invoice->type=='Full'){
        if ($type === 'partial' && $amount) {
            $budget = $amount;
            $partial = true;
            $last_credit_note_number = credit_note::where('entreprise_id', $invoice->entreprise_id)->max('credit_note_number');
            

        } else {
            $budget = $invoice->total_htva;

            $last_credit_note_number = credit_note::where('entreprise_id', $invoice->entreprise_id)->max('credit_note_number');

            // Check if a similar credit note already exists
            $existing_credit_note = credit_note::where('number', $invoice->number)
                ->where('project_id', $invoice->project_id)
                ->where('client_id', $invoice->client_id)
                ->where('credit_note_type', 'Full')
                ->where('total_htva', $invoice->total_htva)
                ->first();
        }

        
        $latestInvoice = invoices_history::latest()
            ->where('entreprise_id', $user_enterprise)
            ->first(); // Get the latest invoice
        $number = $latestInvoice ? $latestInvoice->number : 0; // Set number to the latest invoice number or 0 if no invoice exists


        return view('admin.creditNote.invoicecreditNote', compact('projectinfos', 'client', 'entreprise', 'number', 'invoice_id', 'budget', 'partial', 'last_credit_note_number', 'type', 'invoice'));

    }elseif($invoice->type=='Partial'){
        if ($type === 'partial' && $amount) {
            $budget = $amount;
            $partial = true;
            $last_credit_note_number = credit_note::where('entreprise_id', $invoice->entreprise_id)->max('credit_note_number');

        } else {
            if($projectinfos->budget != null){
                $budget = $invoice->total_htva;
            }else{
                $budget = 0;
            }

            $last_credit_note_number = credit_note::where('entreprise_id', $invoice->entreprise_id)->max('credit_note_number');

            // Check if a similar credit note already exists
            $existing_credit_note = credit_note::where('number', $invoice->number)
                ->where('project_id', $invoice->project_id)
                ->where('client_id', $invoice->client_id)
                ->where('credit_note_type', 'Full')
                ->where('total_htva', $invoice->total_htva)
                ->first();

        }

        
        $latestInvoice = invoices_history::latest()
            ->where('entreprise_id', $user_enterprise)
            ->first(); // Get the latest invoice
        $number = $latestInvoice ? $latestInvoice->number : 0; // Set number to the latest invoice number or 0 if no invoice exists

        

        return view('admin.creditNote.invoicecreditNote', compact('projectinfos', 'client', 'entreprise', 'number', 'invoice_id', 'budget', 'partial', 'last_credit_note_number', 'type', 'invoice'));

    }elseif($invoice->type ="Percentage"){
        if ($type === 'partial' && $amount) {
            $budget = $amount;
            $partial = true;
            $last_credit_note_number = credit_note::where('entreprise_id', $invoice->entreprise_id)->max('credit_note_number');
            

        } else {
            if($projectinfos->budget != null){
                $budget = $invoice->total_htva;
            }else{
                $budget = 0;
            }

            $last_credit_note_number = credit_note::where('entreprise_id', $invoice->entreprise_id)->max('credit_note_number');

            // Check if a similar credit note already exists
            $existing_credit_note = credit_note::where('number', $invoice->number)
                ->where('project_id', $invoice->project_id)
                ->where('client_id', $invoice->client_id)
                ->where('credit_note_type', 'Full')
                ->where('total_htva', $invoice->total_htva)
                ->first();

            

        }

        
        $latestInvoice = invoices_history::latest()
            ->where('entreprise_id', $user_enterprise)
            ->first(); // Get the latest invoice
        $number = $latestInvoice ? $latestInvoice->number : 0; // Set number to the latest invoice number or 0 if no invoice exists

       

        return view('admin.creditNote.invoicecreditNote', compact('projectinfos', 'client', 'entreprise', 'number', 'invoice_id', 'budget', 'partial', 'last_credit_note_number', 'type', 'invoice'));

    }else{
        return redirect()->back()->with('message', 'Error');
    }

}

public function ConfirmCreditNote($id, $noteType, $noteAmount)
{
  
    $type = $noteType;
    $amount = $noteAmount;
    $budget = 0;
    $partial = false;
    $credit_note ; 



    $invoice = invoices_history::where('id', $id)->first();
    $projectinfos = Project::findOrFail($invoice->project_id); 
    $client = Client::where('id', $invoice->client_id)->first();
    $user_enterprise = Auth::user()->entreprise_id;
    $entreprise = Entreprise::where('id', $user_enterprise)->first();
    $duration= $invoice->duration;
    $invoice_id= $invoice->number; 

    
    $client_contacts = client_contacts::where('client_id', $client->id)
    ->where('project_id', $projectinfos->id)
    ->get();

    
    if($invoice->type=='Period'){ 

        $startdate = $invoice->start_date;
        $enddate = $invoice->end_date;

        $enddate = Carbon::parse($enddate)->endOfDay();

        $tasks_timer = tasks_timer::where('entreprise_id', auth()->user()->entreprise_id)
        ->Where('start_date' , '>=', $startdate)
        ->whereNotNull('end_date')
        ->Where('end_date' , '<=', $enddate)
        ->orderBy('start_date', 'asc') // Order by start_date
        ->get();

        $collection = collect();

        foreach ($tasks_timer as $task) {
            $task_project_id = Tasks::where('id', $task->task_id)->first();
            if ($task_project_id && $task_project_id->project_id == $projectinfos->id) {
                $collection->push($task); // Use push() to add the task to the collection
            }
        }
    
        $cost = 0;
        $duration = 0;
    
        foreach ($collection as $task) {
            $cost = $cost + $task->cost;
            $duration = $duration +  $task->duration;
        }

        if ($type === 'partial' && $amount) {
            $prixtotal = $amount; 
            $partial = true;
            $last_credit_note_number = credit_note::where('entreprise_id', $invoice->entreprise_id)->max('credit_note_number');

            $credit_note = new credit_note();
            $credit_note->project_name = $invoice->project_name;
            $credit_note->number = $invoice->number;
            $credit_note->link = $invoice->link;
            $credit_note->client_id = $invoice->client_id;
            $credit_note->project_id = $invoice->project_id;
            $credit_note->due_date = $invoice->due_date;
            $credit_note->end_date = $invoice->end_date;
            $credit_note->start_date = $invoice->start_date;
            $credit_note->type = $invoice->type;
            $credit_note->Payment_status = $invoice->Payment_status;
            $credit_note->sentReminder = 0;
            $credit_note->duration = $invoice->duration;
            $credit_note->entreprise_id = $invoice->entreprise_id;
            $credit_note->total_htva = $prixtotal;
            $credit_note->total_tva = $prixtotal* ($projectinfos->tva / 100);
            $credit_note->total_ttc = $credit_note->total_htva + $credit_note->total_tva;
            $credit_note->credit_note_number = str_pad($last_credit_note_number + 1, 3, '0', STR_PAD_LEFT);
            if ($partial) {
                $credit_note->credit_note_type = 'Partial';
            } else {
                $credit_note->credit_note_type = 'Full';
            }
            $credit_note->invoice_id = $invoice->id;
            $credit_note->save();

            $pdf = PDF::loadView('admin.creditNote.invoicebydurationPDFcreditNote', [
                'invoice' => $invoice, 
                'projectinfos' => $projectinfos,
                'client' => $client,
                'entreprise' => $entreprise,
                'duration' => $duration,
                'invoice_id' => $invoice_id,
                'collection' => $collection,
                'prixtotal' => $prixtotal,
                'partial' => $partial,
                'credit_note' => $credit_note
    
            ]);
            $invoice->Payment_status = -1;
            $invoice->archived = 1;
            $invoice->save();
    
            // Save the PDF to a file from do_spaces
            $creditnote_y = $credit_note->created_at->format('y');
            $creditnote_m = $credit_note->created_at->format('m');
            $creditnote_Y = $credit_note->created_at->format('Y');
            $filePath = 'creditNote/' . $entreprise->id .'/'.$creditnote_Y . '/CN-' . $creditnote_y . $creditnote_m . $credit_note->credit_note_number. '.pdf';
            Storage::disk('do_spaces')->put($filePath, $pdf->output(), 'public');
        }
        else {
            $prixtotal = $invoice->total_htva;

            $last_credit_note_number = credit_note::where('entreprise_id', $invoice->entreprise_id)->max('credit_note_number');

            // Check if a similar credit note already exists
            $existing_credit_note = credit_note::where('number', $invoice->number)
            ->where('project_id', $invoice->project_id)
            ->where('client_id', $invoice->client_id)
            ->where('credit_note_type', 'Full')
            ->where('total_htva', $prixtotal)
            ->first();
     
            
                 $credit_note = new credit_note();
                 $credit_note->project_name = $invoice->project_name;
                 $credit_note->number = $invoice->number;
                 $credit_note->link = $invoice->link;
                 $credit_note->client_id = $invoice->client_id;
                 $credit_note->project_id = $invoice->project_id;
                 $credit_note->due_date = $invoice->due_date;
                 $credit_note->end_date = $invoice->end_date;
                 $credit_note->start_date = $invoice->start_date;
                 $credit_note->type = $invoice->type;
                 $credit_note->Payment_status = $invoice->Payment_status;
                 $credit_note->sentReminder = 0;
                 $credit_note->duration = $invoice->duration;
                 $credit_note->entreprise_id = $invoice->entreprise_id;
                 $credit_note->total_htva = $prixtotal;
                 $credit_note->total_tva = $prixtotal* ($projectinfos->tva / 100);
                 $credit_note->total_ttc = $credit_note->total_htva + $credit_note->total_tva;
                 $credit_note->credit_note_number = str_pad($last_credit_note_number + 1, 3, '0', STR_PAD_LEFT);
                 if ($partial) {
                     $credit_note->credit_note_type = 'Partial';
                 } else {
                     $credit_note->credit_note_type = 'Full';
                 }
                 $credit_note->invoice_id = $invoice->id;
                 $credit_note->save();
             

             $pdf = PDF::loadView('admin.creditNote.invoicebydurationPDFcreditNote', [
                'invoice' => $invoice, 
                'projectinfos' => $projectinfos,
                'client' => $client,
                'entreprise' => $entreprise,
                'duration' => $duration,
                'invoice_id' => $invoice_id,
                'collection' => $collection,
                'prixtotal' => $prixtotal,
                'partial' => $partial,
                'credit_note' => $credit_note
    
            ]);
            $invoice->Payment_status = -1;
            $invoice->archived = 1;
            $invoice->save();
    
            // Save the PDF to a file from do_spaces
            $creditnote_y = $credit_note->created_at->format('y');
            $creditnote_m = $credit_note->created_at->format('m');
            $creditnote_Y = $credit_note->created_at->format('Y');
            $filePath = 'creditNote/' . $entreprise->id . '/'.$creditnote_Y . '/CN-' . $creditnote_y . $creditnote_m . $credit_note->credit_note_number. '.pdf';
            Storage::disk('do_spaces')->put($filePath, $pdf->output(), 'public');
        }

        if(Auth::user()->hasRole('Admin')){
            return redirect()->route('admin.creditNote.creditNoteHistory')->with('message', 'Credit Note Created successfully!');
        }
        if(Auth::user()->hasRole('Responsable de projet')){
            return redirect()->route('Responsable_de_projet.creditNote.creditNoteHistory')->with('message', 'Credit Note Created successfully!');
        }
        if(Auth::user()->hasRole('Responsable de projet et gestionnaire')){
            return redirect()->route('Responsable_gestionnaire.creditNote.creditNoteHistory')->with('message', 'Credit Note Created successfully!');
        }

    }elseif($invoice->type=='Full'){
        if ($type === 'partial' && $amount) {
            $budget = $amount;
            $partial = true;
            $last_credit_note_number = credit_note::where('entreprise_id', $invoice->entreprise_id)->max('credit_note_number');
            $credit_note = new credit_note();
            $credit_note->project_name = $invoice->project_name;
            $credit_note->number = $invoice->number;
            $credit_note->link = $invoice->link;
            $credit_note->client_id = $invoice->client_id;
            $credit_note->project_id = $invoice->project_id;
            $credit_note->due_date = $invoice->due_date;
            $credit_note->end_date = $invoice->end_date;
            $credit_note->start_date = $invoice->start_date;
            $credit_note->type = $invoice->type;
            $credit_note->Payment_status = $invoice->Payment_status;
            $credit_note->sentReminder = 0;
            $credit_note->duration = $invoice->duration;
            $credit_note->entreprise_id = $invoice->entreprise_id;
            $credit_note->total_htva = $budget;
            $credit_note->total_tva = $budget * ($projectinfos->tva / 100);
            $credit_note->total_ttc = $credit_note->total_htva + $credit_note->total_tva;
            $credit_note->credit_note_number = str_pad($last_credit_note_number + 1, 3, '0', STR_PAD_LEFT);
            if ($partial) {
                $credit_note->credit_note_type = 'Partial';
            } else {
                $credit_note->credit_note_type = 'Full';
            }
            $credit_note->invoice_id = $invoice->id;
            $credit_note->save();

        } else {
            $budget = $invoice->total_htva;

            $last_credit_note_number = credit_note::where('entreprise_id', $invoice->entreprise_id)->max('credit_note_number');

            // Check if a similar credit note already exists
            $existing_credit_note = credit_note::where('number', $invoice->number)
                ->where('project_id', $invoice->project_id)
                ->where('client_id', $invoice->client_id)
                ->where('credit_note_type', 'Full')
                ->where('total_htva', $invoice->total_htva)
                ->first();

            
                $credit_note = new credit_note();
                $credit_note->project_name = $invoice->project_name;
                $credit_note->number = $invoice->number;
                $credit_note->link = $invoice->link;
                $credit_note->client_id = $invoice->client_id;
                $credit_note->project_id = $invoice->project_id;
                $credit_note->due_date = $invoice->due_date;
                $credit_note->end_date = $invoice->end_date;
                $credit_note->start_date = $invoice->start_date;
                $credit_note->type = $invoice->type;
                $credit_note->Payment_status = $invoice->Payment_status;
                $credit_note->sentReminder = 0;
                $credit_note->duration = $invoice->duration;
                $credit_note->entreprise_id = $invoice->entreprise_id;
                $credit_note->total_htva = $budget;
                $credit_note->total_tva = $budget * ($projectinfos->tva / 100);
                $credit_note->total_ttc = $credit_note->total_htva + $credit_note->total_tva;
                $credit_note->credit_note_number = str_pad($last_credit_note_number + 1, 3, '0', STR_PAD_LEFT);
                if ($partial) {
                    $credit_note->credit_note_type = 'Partial';
                } else {
                    $credit_note->credit_note_type = 'Full';
                }
                $credit_note->invoice_id = $invoice->id;
                $credit_note->save();
            

        }

        
        $latestInvoice = invoices_history::latest()
            ->where('entreprise_id', $user_enterprise)
            ->first(); // Get the latest invoice
        $number = $latestInvoice ? $latestInvoice->number : 0; // Set number to the latest invoice number or 0 if no invoice exists

        $pdf = PDF::loadView('admin.creditNote.fullinvoicePDFcreditNote', [
            'projectinfos' => $projectinfos,
            'client' => $client,
            'entreprise' => $entreprise,
            'number' => $invoice->number,
            'invoice_id' => $invoice_id,
            'budget' => $budget,
            'partial' => $partial, 
            'credit_note' => $credit_note,
            'invoice' => $invoice
        ]);
        $invoice->Payment_status = -1;
        $invoice->archived = 1;
        $invoice->save();

        // Save the PDF to a file from do_spaces
        $creditnote_y = $credit_note->created_at->format('y');
        $creditnote_m = $credit_note->created_at->format('m');
        $creditnote_Y = $credit_note->created_at->format('Y');
        $filePath = 'creditNote/' . $entreprise->id . '/'.$creditnote_Y . '/CN-' . $creditnote_y . $creditnote_m . $credit_note->credit_note_number. '.pdf';
        Storage::disk('do_spaces')->put($filePath, $pdf->output(), 'public');

        if(Auth::user()->hasRole('Admin')){
            return redirect()->route('admin.creditNote.creditNoteHistory')->with('message', 'Credit Note Created successfully!');
        }
        if(Auth::user()->hasRole('Responsable de projet')){
            return redirect()->route('Responsable_de_projet.creditNote.creditNoteHistory')->with('message', 'Credit Note Created successfully!');
        }
        if(Auth::user()->hasRole('Responsable de projet et gestionnaire')){
            return redirect()->route('Responsable_gestionnaire.creditNote.creditNoteHistory')->with('message', 'Credit Note Created successfully!');
        }

    }elseif($invoice->type=='Partial'){
        if ($type === 'partial' && $amount) {
            $budget = $amount;
            $partial = true;
            $last_credit_note_number = credit_note::where('entreprise_id', $invoice->entreprise_id)->max('credit_note_number');
            $credit_note = new credit_note();
            $credit_note->project_name = $invoice->project_name;
            $credit_note->number = $invoice->number;
            $credit_note->link = $invoice->link;
            $credit_note->client_id = $invoice->client_id;
            $credit_note->project_id = $invoice->project_id;
            $credit_note->due_date = $invoice->due_date;
            $credit_note->end_date = $invoice->end_date;
            $credit_note->start_date = $invoice->start_date;
            $credit_note->type = $invoice->type;
            $credit_note->Payment_status = $invoice->Payment_status;
            $credit_note->sentReminder = 0;
            $credit_note->duration = $invoice->duration;
            $credit_note->entreprise_id = $invoice->entreprise_id;
            $credit_note->total_htva = $budget;
            $credit_note->total_tva = $budget * ($projectinfos->tva / 100);
            $credit_note->total_ttc = $credit_note->total_htva + $credit_note->total_tva;
            $credit_note->credit_note_number = str_pad($last_credit_note_number + 1, 3, '0', STR_PAD_LEFT);
            if ($partial) {
                $credit_note->credit_note_type = 'Partial';
            } else {
                $credit_note->credit_note_type = 'Full';
            }
            $credit_note->invoice_id = $invoice->id;
            $credit_note->save();

        } else {
            $budget = $invoice->total_htva;

            $last_credit_note_number = credit_note::where('entreprise_id', $invoice->entreprise_id)->max('credit_note_number');

            // Check if a similar credit note already exists
            $existing_credit_note = credit_note::where('number', $invoice->number)
                ->where('project_id', $invoice->project_id)
                ->where('client_id', $invoice->client_id)
                ->where('credit_note_type', 'Full')
                ->where('total_htva', $invoice->total_htva)
                ->first();

            
                $credit_note = new credit_note();
                $credit_note->project_name = $invoice->project_name;
                $credit_note->number = $invoice->number;
                $credit_note->link = $invoice->link;
                $credit_note->client_id = $invoice->client_id;
                $credit_note->project_id = $invoice->project_id;
                $credit_note->due_date = $invoice->due_date;
                $credit_note->end_date = $invoice->end_date;
                $credit_note->start_date = $invoice->start_date;
                $credit_note->type = $invoice->type;
                $credit_note->Payment_status = $invoice->Payment_status;
                $credit_note->sentReminder = 0;
                $credit_note->duration = $invoice->duration;
                $credit_note->entreprise_id = $invoice->entreprise_id;
                $credit_note->total_htva = $budget;
                $credit_note->total_tva = $budget * ($projectinfos->tva / 100);
                $credit_note->total_ttc = $credit_note->total_htva + $credit_note->total_tva;
                $credit_note->credit_note_number = str_pad($last_credit_note_number + 1, 3, '0', STR_PAD_LEFT);
                if ($partial) {
                    $credit_note->credit_note_type = 'Partial';
                } else {
                    $credit_note->credit_note_type = 'Full';
                }
                $credit_note->invoice_id = $invoice->id;
                $credit_note->save();
            

        }

        
        $latestInvoice = invoices_history::latest()
            ->where('entreprise_id', $user_enterprise)
            ->first(); // Get the latest invoice
        $number = $latestInvoice ? $latestInvoice->number : 0; // Set number to the latest invoice number or 0 if no invoice exists

        $pdf = PDF::loadView('admin.creditNote.fullinvoicePDFcreditNote', [
            'projectinfos' => $projectinfos,
            'client' => $client,
            'entreprise' => $entreprise,
            'number' => $invoice->number,
            'invoice_id' => $invoice_id,
            'budget' => $budget,
            'partial' => $partial, 
            'credit_note' => $credit_note
        ]);
        $invoice->Payment_status = -1;
        $invoice->archived = 1;
        $invoice->save();

        // Save the PDF to a file from do_spaces
        $creditnote_y = $credit_note->created_at->format('y');
        $creditnote_m = $credit_note->created_at->format('m');
        $creditnote_Y = $credit_note->created_at->format('Y');
        $filePath = 'creditNote/' . $entreprise->id . '/'.$creditnote_Y . '/CN-' . $creditnote_y . $creditnote_m . $credit_note->credit_note_number. '.pdf';
        Storage::disk('do_spaces')->put($filePath, $pdf->output(), 'public');

        if(Auth::user()->hasRole('Admin')){
            return redirect()->route('admin.creditNote.creditNoteHistory')->with('message', 'Credit Note Created successfully!');
        }
        if(Auth::user()->hasRole('Responsable de projet')){
            return redirect()->route('Responsable_de_projet.creditNote.creditNoteHistory')->with('message', 'Credit Note Created successfully!');
        }
        if(Auth::user()->hasRole('Responsable de projet et gestionnaire')){
            return redirect()->route('Responsable_gestionnaire.creditNote.creditNoteHistory')->with('message', 'Credit Note Created successfully!');
        }

    }elseif($invoice->type ="Percentage"){
        if ($type === 'partial' && $amount) {
            $budget = $amount;
            $partial = true;
            $last_credit_note_number = credit_note::where('entreprise_id', $invoice->entreprise_id)->max('credit_note_number');
            $credit_note = new credit_note();
            $credit_note->project_name = $invoice->project_name;
            $credit_note->number = $invoice->number;
            $credit_note->link = $invoice->link;
            $credit_note->client_id = $invoice->client_id;
            $credit_note->project_id = $invoice->project_id;
            $credit_note->due_date = $invoice->due_date;
            $credit_note->end_date = $invoice->end_date;
            $credit_note->start_date = $invoice->start_date;
            $credit_note->type = $invoice->type;
            $credit_note->Payment_status = $invoice->Payment_status;
            $credit_note->sentReminder = 0;
            $credit_note->duration = $invoice->duration;
            $credit_note->entreprise_id = $invoice->entreprise_id;
            $credit_note->total_htva = $budget;
            $credit_note->total_tva = $budget * ($projectinfos->tva / 100);
            $credit_note->total_ttc = $credit_note->total_htva + $credit_note->total_tva;
            $credit_note->credit_note_number = str_pad($last_credit_note_number + 1, 3, '0', STR_PAD_LEFT);
            if ($partial) {
                $credit_note->credit_note_type = 'Partial';
            } else {
                $credit_note->credit_note_type = 'Full';
            }
            $credit_note->invoice_id = $invoice->id;
            $credit_note->save();

        } else {
            $budget = $invoice->total_htva;

            $last_credit_note_number = credit_note::where('entreprise_id', $invoice->entreprise_id)->max('credit_note_number');

            // Check if a similar credit note already exists
            $existing_credit_note = credit_note::where('number', $invoice->number)
                ->where('project_id', $invoice->project_id)
                ->where('client_id', $invoice->client_id)
                ->where('credit_note_type', 'Full')
                ->where('total_htva', $invoice->total_htva)
                ->first();

            
                $credit_note = new credit_note();
                $credit_note->project_name = $invoice->project_name;
                $credit_note->number = $invoice->number;
                $credit_note->link = $invoice->link;
                $credit_note->client_id = $invoice->client_id;
                $credit_note->project_id = $invoice->project_id;
                $credit_note->due_date = $invoice->due_date;
                $credit_note->end_date = $invoice->end_date;
                $credit_note->start_date = $invoice->start_date;
                $credit_note->type = $invoice->type;
                $credit_note->Payment_status = $invoice->Payment_status;
                $credit_note->sentReminder = 0;
                $credit_note->duration = $invoice->duration;
                $credit_note->entreprise_id = $invoice->entreprise_id;
                $credit_note->total_htva = $budget;
                $credit_note->total_tva = $budget * ($projectinfos->tva / 100);
                $credit_note->total_ttc = $credit_note->total_htva + $credit_note->total_tva;
                $credit_note->credit_note_number = str_pad($last_credit_note_number + 1, 3, '0', STR_PAD_LEFT);
                if ($partial) {
                    $credit_note->credit_note_type = 'Partial';
                } else {
                    $credit_note->credit_note_type = 'Full';
                }
                $credit_note->invoice_id = $invoice->id;
                $credit_note->save();
            

        }

        
        $latestInvoice = invoices_history::latest()
            ->where('entreprise_id', $user_enterprise)
            ->first(); // Get the latest invoice
        $number = $latestInvoice ? $latestInvoice->number : 0; // Set number to the latest invoice number or 0 if no invoice exists

        $pdf = PDF::loadView('admin.creditNote.fullinvoicePDFcreditNote', [
            'projectinfos' => $projectinfos,
            'client' => $client,
            'entreprise' => $entreprise,
            'number' => $invoice->number,
            'invoice_id' => $invoice_id,
            'budget' => $budget,
            'partial' => $partial, 
            'credit_note' => $credit_note
        ]);
        $invoice->Payment_status = -1;
        $invoice->archived = 1;
        $invoice->save();

        // Save the PDF to a file from do_spaces
        $creditnote_y = $credit_note->created_at->format('y');
        $creditnote_m = $credit_note->created_at->format('m');
        $creditnote_Y = $credit_note->created_at->format('Y');
        $filePath = 'creditNote/' . $entreprise->id . '/'.$creditnote_Y . '/CN-' . $creditnote_y . $creditnote_m . $credit_note->credit_note_number. '.pdf';
        Storage::disk('do_spaces')->put($filePath, $pdf->output(), 'public');

        if(Auth::user()->hasRole('Admin')){
            return redirect()->route('admin.creditNote.creditNoteHistory')->with('message', 'Credit Note Created successfully!');
        }
        if(Auth::user()->hasRole('Responsable de projet')){
            return redirect()->route('Responsable_de_projet.creditNote.creditNoteHistory')->with('message', 'Credit Note Created successfully!');
        }
        if(Auth::user()->hasRole('Responsable de projet et gestionnaire')){
            return redirect()->route('Responsable_gestionnaire.creditNote.creditNoteHistory')->with('message', 'Credit Note Created successfully!');
        }

    }else{
        return redirect()->back()->with('message', 'Error');
    }

}

public function creditNoteHistory()
    {
        $credit_notes = credit_note::where('entreprise_id', Auth::user()->entreprise_id)
        ->get(); 
        $clients = Client::where('entreprise_id', Auth::user()->entreprise_id)->where(function($query) {
            $query->whereNull('AcceptedDevisClient')
                  ->orWhere('AcceptedDevisClient', '=', 1);
        })->get();
        $projects = Project::where('entreprise_id', Auth::user()->entreprise_id)->where(function($query) {
            $query->whereNull('AcceptedDevisProject')
                  ->orWhere('AcceptedDevisProject', '=', 1);
        })
        ->where('archived', 0)
        ->get();
        $entreprise_infos = Entreprise::where('id', Auth::user()->entreprise_id)->first();

        return view('admin.creditNote.creditNoteHistory', compact('credit_notes','clients','projects', 'entreprise_infos'));
    }

    public function serveCreditNotePdf($credit_note_id)
{
    try {
        $entreprise = Entreprise::where('id', Auth::user()->entreprise_id)->first();
        $credit_note = credit_note::find($credit_note_id);
        
        if (!$credit_note) {
            return response('Credit note not found', 404);
        }
        
        $creditnoteyear = $credit_note->created_at->format('Y');
        $creditnotemonth = $credit_note->created_at->format('m');
        $creditnotey = $credit_note->created_at->format('y');
        
        // Try different possible file paths
        $possiblePaths = [
            // Current format in your system
            'creditNote/' . $entreprise->id . '/' . $creditnoteyear . '/CN-' . $creditnotey . $creditnotemonth . $credit_note->credit_note_number . '.pdf',
            
            // Format for detailed credit notes (NC- prefix)
            'creditNote/' . $entreprise->id . '/' . $creditnoteyear . '/NC-' . $creditnotey . $creditnotemonth . $credit_note->credit_note_number . '.pdf',
            
            // Alternative format without year/month in filename
            'creditNote/' . $entreprise->id . '/' . $creditnoteyear . '/CN-' . $credit_note->credit_note_number . '.pdf',
            
            // Alternative format without year/month in filename with NC prefix
            'creditNote/' . $entreprise->id . '/' . $creditnoteyear . '/NC-' . $credit_note->credit_note_number . '.pdf',
            
            // Path without year folder
            'creditNote/' . $entreprise->id . '/CN-' . $creditnotey . $creditnotemonth . $credit_note->credit_note_number . '.pdf',
            
            // Path without year folder with NC prefix
            'creditNote/' . $entreprise->id . '/NC-' . $creditnotey . $creditnotemonth . $credit_note->credit_note_number . '.pdf'
        ];
        
        $file = null;
        $filePath = null;
        
        // Try each path until we find the file
        foreach ($possiblePaths as $path) {
            if (Storage::disk('do_spaces')->exists($path)) {
                $file = Storage::disk('do_spaces')->get($path);
                $filePath = $path;
                \Log::info('Credit note PDF found at: ' . $path);
                break;
            }
        }
        
        if (!$file) {
            \Log::error('Credit note PDF not found. Credit note ID: ' . $credit_note_id);
            \Log::error('Attempted paths: ' . implode(', ', $possiblePaths));
            return response('Credit note PDF not found', 404);
        }
        
        $fileName = basename($filePath);
        
        return response($file, 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="' . $fileName . '"')
            ->header('Content-Length', strlen($file));
    } catch (\Exception $e) {
        \Log::error('Error serving credit note PDF: ' . $e->getMessage());
        return response('Error serving credit note PDF: ' . $e->getMessage(), 500);
    }
}


public function downloadcreditNoteRepports(Request $request)
{
    $query = credit_note::where('entreprise_id', auth()->user()->entreprise_id);

    // Check if start and end dates are provided
    if ($request->has(['startDate', 'endDate'])) {
        $startDate = $request->startDate;
        $endDate = $request->endDate;
        $query->whereDate('created_at', '>=', $startDate)
              ->whereDate('created_at', '<=', $endDate);
    }

    $creditNote = $query->get();
    $entreprise = Entreprise::where('id', auth()->user()->entreprise_id)->first();

    $pdf = PDF::loadView('admin.creditNote.downloadRepports', [
        'creditNote' => $creditNote,
        'entreprise' => $entreprise,
    ]);

    return $pdf->stream('creditNoteRepports.pdf');
}

public function destroyCreditNote($id)
    {
        $creditnote = credit_note::find($id);

        if (!$creditnote) {
            if(Auth::user()->hasRole('Admin')){
                return redirect()->route('admin.creditNote.creditNoteHistory')->with('message', 'creditnote not found.');
            }
            if(Auth::user()->hasRole('Responsable de projet')){
                return redirect()->route('Responsable_de_projet.creditNote.creditNoteHistory')->with('message', 'creditnote not found.');
            }
            if(Auth::user()->hasRole('Responsable de projet et gestionnaire')){
                return redirect()->route('Responsable_gestionnaire.creditNote.creditNoteHistory')->with('message', 'creditnote not found.');
            }
        }

        // Adjust 'file_path' to the actual column name if different.
        $baseDir = public_path('creditNote/');
        $fileName = 'CN-' . $creditnote->credit_note_number .'.pdf';
        $filePath = $baseDir . $fileName;

        try {
            // Attempt to delete the file if it exists
            if (file_exists($filePath)) {
                unlink($filePath); // Note: unlink() returns true on success and false on failure.
            }
        
            // Delete the creditnote from the database regardless of file existence or deletion success
            $creditnote->delete();
        
            // Return success message
            if(Auth::user()->hasRole('Admin')){
                return redirect()->route('admin.creditNote.creditNoteHistory')->with('message', 'creditnote deleted successfully.');
            }
            if(Auth::user()->hasRole('Responsable de projet')){
                return redirect()->route('Responsable_de_projet.creditNote.creditNoteHistory')->with('message', 'creditnote deleted successfully.');
            }
            if(Auth::user()->hasRole('Responsable de projet et gestionnaire')){
                return redirect()->route('Responsable_gestionnaire.creditNote.creditNoteHistory')->with('message', 'creditnote deleted successfully.');
            }
        } catch (Exception $e) {
            // Return error message in case of exception
            if(Auth::user()->hasRole('Admin')){
                return redirect()->route('admin.creditNote.creditNoteHistory')->with('message', 'An error occurred: ' . $e->getMessage());
            }
            if(Auth::user()->hasRole('Responsable de projet')){
                return redirect()->route('Responsable_de_projet.creditNote.creditNoteHistory')->with('message', 'An error occurred: ' . $e->getMessage());
            }
            if(Auth::user()->hasRole('Responsable de projet et gestionnaire')){
                return redirect()->route('Responsable_gestionnaire.creditNote.creditNoteHistory')->with('message', 'An error occurred: ' . $e->getMessage());
            }
        }
    }


    public function generateNoteDeFrais()
    {
        return view('admin.noteDeFrais.generate');
    }

public function saveNoteDeFraisPdf(Request $request)
{
    $latestnotedefraisnumber = note_de_frais::where('entreprise_id', Auth::user()->entreprise_id)->max('note_number');
    $note_number = sprintf('%03d', $latestnotedefraisnumber + 1);
    

    $entreprise = Entreprise::where('id', Auth::user()->entreprise_id)->first();

    $tasks = $request->input('task');
    $quantity = $request->input('quantity');
    $taskBudget = $request->input('taskBudget');
  
    $employee_name = $request->employee_name;
    $budget = $request->budget;
    $description = $request->description;
    $entreprise = $entreprise;
    $tasks = $tasks;
    $quantity = $quantity;
    $taskBudget = $taskBudget;
   


     return view('admin.noteDeFrais.notedefraisTemplate', compact('employee_name', 'budget', 'description', 'entreprise', 'note_number', 'tasks', 'quantity', 'taskBudget'));

    
    }

    public function confirmFraisNote(Request $request)
    {
        $employee_name = $request->input('employee_name');
        $budget = $request->input('budget');
        $description = $request->input('description');
        $entreprise = $request->input('entreprise');
        $note_number = $request->input('note_number');
        $tasks = json_decode($request->input('tasks'), true);
        $quantity = json_decode($request->input('quantity'), true);
        $taskBudget = json_decode($request->input('taskBudget'), true);

        $entreprise = Entreprise::where('id', Auth::user()->entreprise_id)->first();
        $latestnotedefraisnumber = note_de_frais::where('entreprise_id', Auth::user()->entreprise_id)->max('note_number');

        // Save the data into the devis_history table
        $notedefrais = new note_de_frais();
        $notedefrais->note_number = sprintf('%03d', $latestnotedefraisnumber + 1);
        $notedefrais->employee_name = $employee_name;
        $notedefrais->budget = $budget;
        $notedefrais->description = $description;
        $notedefrais->entreprise_id = Auth::user()->entreprise_id;
        $notedefrais->save();

        $pdf = PDF::loadView('admin.noteDeFrais.view', [
            'employee_name' => $employee_name,
            'budget' => $budget,
            'description' => $description,
            'entreprise' => $entreprise,
            'note_number' => $note_number,
            'tasks' => $tasks,  // Already decoded
            'quantity' => $quantity,  // Already decoded
            'taskBudget' => $taskBudget,  // Already decoded
            'notedefrais_id' => $notedefrais->id,
        ]);

        // Save the PDF to a file from do_spaces 
        $filePath = 'notedefrais/' . $entreprise->id . '/' . $entreprise->notedefraisPrefix . '-' . $note_number . '.pdf';
        Storage::disk('do_spaces')->put($filePath, $pdf->output(), 'public');

        if(Auth::user()->hasRole('Admin')){
            return redirect()->route('admin.notedefrais.notedefraishistory')->with('message', 'Note de frais created successfully!');
        }
        if(Auth::user()->hasRole('Responsable de projet')){
            return redirect()->route('Responsable_de_projet.notedefrais.notedefraishistory')->with('message', 'Note de frais created successfully!');
        }
        if(Auth::user()->hasRole('Responsable de projet et gestionnaire')){
            return redirect()->route('Responsable_gestionnaire.notedefrais.notedefraishistory')->with('message', 'Note de frais created successfully!');
        }
    }



    public function notedefraisparameters(){
        $entreprise = Entreprise::where('id', Auth::user()->entreprise_id)->first();
        return view('admin.noteDeFrais.parameters', compact('entreprise'));
    }

    public function notedefraishistory()
{
    $entreprise = Entreprise::where('id', Auth::user()->entreprise_id)->first();
    $notedefrais = note_de_frais::where('entreprise_id', Auth::user()->entreprise_id)->get();

    return view('admin.noteDeFrais.noteHistory', compact('notedefrais', 'entreprise'));
}


public function downloadNoteDeFrais($id)
{
    $entreprise = Entreprise::where('id', Auth::user()->entreprise_id)->first();
    
    // Get the full URL directly
    $fileUrl = Storage::disk('do_spaces')->url('notedefrais/' . $entreprise->id . '/' . $entreprise->notedefraisPrefix . '-' . $id . '.pdf');

    // For remote files, we should return a redirect response instead of trying to read the file locally
    return redirect($fileUrl);
}

public function destroyNotedeFrais($id)
    {
        $notedefrais = note_de_frais::find($id);
        $entreprise = Entreprise::where('id', Auth::user()->entreprise_id)->first();

        if (!$notedefrais) {
            if(Auth::user()->hasRole('Admin')){
                return redirect()->route('admin.notedefrais.notedefraishistory')->with('message', 'Expense Note not found.');
            }
            if(Auth::user()->hasRole('Responsable de projet')){
                return redirect()->route('Responsable_de_projet.notedefrais.notedefraishistory')->with('message', 'Expense Note not found.');
            }
            if(Auth::user()->hasRole('Responsable de projet et gestionnaire')){
                return redirect()->route('Responsable_gestionnaire.notedefrais.notedefraishistory')->with('message', 'Expense Note not found.');
            }
        }

        // Adjust 'file_path' to the actual column name if different.
        $baseDir = public_path('notedefrais/');
        $fileName = $entreprise->notedefraisPrefix.'-' . $notedefrais->note_number .'.pdf';
        $filePath = $baseDir . $fileName;

        try {
            // Attempt to delete the file if it exists
            if (file_exists($filePath)) {
                unlink($filePath); // Note: unlink() returns true on success and false on failure.
            }
        
            // Delete the invoice from the database regardless of file existence or deletion success
            $notedefrais->delete();
        
            // Return success message
            if(Auth::user()->hasRole('Admin')){
                return redirect()->route('admin.notedefrais.notedefraishistory')->with('message', 'Expense Note deleted successfully.');
            }
            if(Auth::user()->hasRole('Responsable de projet')){
                return redirect()->route('Responsable_de_projet.notedefrais.notedefraishistory')->with('message', 'Expense Note deleted successfully.');
            }
            if(Auth::user()->hasRole('Responsable de projet et gestionnaire')){
                return redirect()->route('Responsable_gestionnaire.notedefrais.notedefraishistory')->with('message', 'Expense Note deleted successfully.');
            }
        } catch (Exception $e) {
            // Return error message in case of exception
            if(Auth::user()->hasRole('Admin')){
                return redirect()->route('admin.notedefrais.notedefraishistory')->with('message', 'An error occurred: ' . $e->getMessage());
            }
            if(Auth::user()->hasRole('Responsable de projet')){
                return redirect()->route('Responsable_de_projet.notedefrais.notedefraishistory')->with('message', 'An error occurred: ' . $e->getMessage());
            }
            if(Auth::user()->hasRole('Responsable de projet et gestionnaire')){
                return redirect()->route('Responsable_gestionnaire.notedefrais.notedefraishistory')->with('message', 'An error occurred: ' . $e->getMessage());
            }
        }
    }

    public function downloadNoteDeFraisRepports(Request $request)
{
    $query = note_de_frais::where('entreprise_id', auth()->user()->entreprise_id);

    // Check if start and end dates are provided
    if ($request->has(['startDate', 'endDate'])) {
        $startDate = $request->startDate;
        $endDate = $request->endDate;
        $query->whereDate('created_at', '>=', $startDate)
              ->whereDate('created_at', '<=', $endDate);
    }

    $notedefrais = $query->get();
    $entreprise = Entreprise::where('id', auth()->user()->entreprise_id)->first();

    $pdf = PDF::loadView('admin.noteDeFrais.downloadRepports', [
        'notedefrais' => $notedefrais,
        'entreprise' => $entreprise,
    ]);

    return $pdf->stream('ExpenseNoteRepports.pdf');
}


    public function getEmployeeProjects($employeeId)
    {
        $tasks_ids = employees_tasks::where('employee_id', $employeeId)->pluck('task_id');
        $projects_id = Tasks::whereIn('id', $tasks_ids)->pluck('project_id');
        $projects = Project::whereIn('id', $projects_id)->get();
        return response()->json($projects);
    }

    public function getEmployeeTasks($employeeId)
    {
        $tasks = employees_tasks::where('employee_id', $employeeId)->get();
        return response()->json($tasks);
    }

    public function getProjectTasks($projectId)
    {
        $tasks = Tasks::where('project_id', $projectId)->get();
        return response()->json($tasks);
    }

    public function getTaskInfo($taskId)
    {
        $task = Tasks::find($taskId);
        return response()->json($task);
    }

    public function getAllProjects()
    {
        $projects = Project::where('id', auth()->user()->entreprise_id)->get();
        return response()->json($projects);
    }

    public function getAllTasks()
    {
        $tasks = Tasks::where('id', auth()->user()->entreprise_id)->get();
        return response()->json($tasks);
    }

    public function PartialInvoice(Request $request, $projectid, $amount)
{
    // Resolve project via ID or selected devis
    $projectinfos = null;
    if (!empty($projectid)) {
        $projectinfos = Project::where('id', $projectid)->first();
    }
    if (!$projectinfos && $request->has('devis_id')) {
        $devis = \App\Models\devis_history::find($request->query('devis_id'));
        if ($devis) {
            $projectinfos = Project::where('id', $devis->project_id)->first();
        }
    }
    if (!$projectinfos) {
        return back()->with('message', 'Unable to resolve project from selection');
    }
    $client = Client::where('id', $projectinfos->client_id)->first();
    $user_enterprise = Auth::user()->entreprise_id;
    $entreprise = Entreprise::where('id', $user_enterprise)->first();

    $invoice_link = '/admin/projects/invoice/' . $projectinfos->id;

    // Check if an invoice with the same details already exists and have a credit note
    $existingInvoice = invoices_history::where('link', $invoice_link)
        ->where('client_id', $client->id)
        ->where('type', 'Partial')
        ->where('entreprise_id', $user_enterprise)
        ->where('Payment_status', '!=', -1)
        ->first();

    $latestInvoice = invoices_history::latest()
    ->where('entreprise_id', $user_enterprise)
    ->first(); // Get the latest invoice
    $currentYear = date('Y'); // Get the current year
    $lastInvoiceYear = $latestInvoice ? date('Y', strtotime($latestInvoice->created_at)) : null; // Get the year of the last invoice
    // Check if the last invoice year is different from the current year
    if ($lastInvoiceYear !== $currentYear) {
        $number = 0; // Initialize to 0 if the years are different
    } else {
        $number = $latestInvoice ? $latestInvoice->number : 0; // Set number to the latest invoice number or 0 if no invoice exists
    }

    $invoice_id =  sprintf('%03d', $number + 1); // Initialize $invoice_id outside the if block
    $invoicerealID = 0;

    $comment = $request->has('comment') ? urldecode($request->comment) : null;
    $principalBudget = (float)($projectinfos->budget ?? 0);

    // If a devis is selected, validate against devis initial budget instead of project
    if ($request->has('devis_id') && !empty($request->query('devis_id'))) {
        $selectedDevis = \App\Models\devis_history::where('id', $request->query('devis_id'))
            ->where('entreprise_id', $user_enterprise)
            ->first();
        if (!$selectedDevis || !$selectedDevis->budget) {
            return back()->with('message', 'The selected devis has no budget set');
        }
        $principalBudget = (float)$selectedDevis->budget;
        if ($amount <= 0) {
            return back()->with('message', 'Amount must be greater than zero');
        }
        if ($amount > (float)$selectedDevis->budget) {
            return back()->with('message', 'The amount is greater than the selected devis budget');
        }
        return view('admin.projects.partialInvoice', compact('projectinfos', 'client', 'entreprise', 'number', 'comment' , 'invoice_id', 'invoicerealID', 'amount', 'comment', 'principalBudget'));
    } else {
        if(!$projectinfos->budget){
            return back()->with('message', 'The project budget is not set');
        }elseif($projectinfos->budget - $projectinfos->amount_billed == 0){
            return back()->with('message', 'The project is already fully billed');
        }elseif($projectinfos->budget - $projectinfos->amount_billed < $amount){
            return back()->with('message', 'The amount is greater than the project unbilled budget');
        }else{
            return view('admin.projects.partialInvoice', compact('projectinfos', 'client', 'entreprise', 'number', 'comment' , 'invoice_id', 'invoicerealID', 'amount', 'comment', 'principalBudget'));   
        }
    }

    
   
}

public function PercentageInvoice(Request $request, $projectid, $percentage)
{
    // Resolve project via ID or selected devis
    $projectinfos = null;
    if (!empty($projectid)) {
        $projectinfos = Project::where('id', $projectid)->first();
    }
    if (!$projectinfos && $request->has('devis_id')) {
        $devis = \App\Models\devis_history::find($request->query('devis_id'));
        if ($devis) {
            $projectinfos = Project::where('id', $devis->project_id)->first();
        }
    }
    if (!$projectinfos) {
        return back()->with('message', 'Unable to resolve project from selection');
    }
    $client = Client::where('id', $projectinfos->client_id)->first();
    $user_enterprise = Auth::user()->entreprise_id;
    $entreprise = Entreprise::where('id', $user_enterprise)->first();

    $invoice_link = '/admin/projects/invoice/' . $projectinfos->id;

    // Check if an invoice with the same details already exists and have a credit note
    $existingInvoice = invoices_history::where('link', $invoice_link)
        ->where('client_id', $client->id)
        ->where('type', 'Percentage')
        ->where('entreprise_id', $user_enterprise)
        ->where('Payment_status', '!=', -1)
        ->first();

    $latestInvoice = invoices_history::latest()
    ->where('entreprise_id', $user_enterprise)
    ->first(); // Get the latest invoice
    $currentYear = date('Y'); // Get the current year
    $lastInvoiceYear = $latestInvoice ? date('Y', strtotime($latestInvoice->created_at)) : null; // Get the year of the last invoice
    // Check if the last invoice year is different from the current year
    if ($lastInvoiceYear !== $currentYear) {
        $number = 0; // Initialize to 0 if the years are different
    } else {
        $number = $latestInvoice ? $latestInvoice->number : 0; // Set number to the latest invoice number or 0 if no invoice exists
    }

    $invoice_id = sprintf('%03d', $number + 1); // Initialize $invoice_id outside the if block
    $invoicerealID = 0;

    // If a devis is selected, base percentage on devis initial budget
    $amount = ((float)$projectinfos->budget * (float)$percentage) / 100;
    $principalBudget = (float)($projectinfos->budget ?? 0);
    if ($request->has('devis_id') && !empty($request->query('devis_id'))) {
        $selectedDevis = \App\Models\devis_history::where('id', $request->query('devis_id'))
            ->where('entreprise_id', $user_enterprise)
            ->first();
        if ($selectedDevis && $selectedDevis->budget) {
            $amount = ((float)$selectedDevis->budget * (float)$percentage) / 100;
            $principalBudget = (float)$selectedDevis->budget;
        }
    }

    $comment = $request->has('comment') ? urldecode($request->comment) : null;

    if(!$projectinfos->budget){
        return back()->with('message', 'The project budget is not set');
    }elseif($projectinfos->budget - $projectinfos->amount_billed == 0){
        return back()->with('message', 'The project is already fully billed');
    }elseif($projectinfos->budget - $projectinfos->amount_billed < $amount){
        return back()->with('message', 'The amount is greater than the project unbilled budget');
    }else{
        return view('admin.projects.percentageInvoice', compact('projectinfos', 'client', 'entreprise', 'number', 'invoice_id', 'invoicerealID', 'amount', 'percentage', 'comment', 'principalBudget'));   
    }
    
}

public function confirmFullInvoice(Request $request, $projectID, $budget, $comment = null){
    $projectinfos = Project::where('id', $projectID)->first();
    $client = Client::where('id', $projectinfos->client_id)->first();
        $user_enterprise = Auth::user()->entreprise_id;
        $entreprise = Entreprise::where('id', $user_enterprise)->first();

        $invoice_link = '/admin/projects/invoice/' . $projectID;

        // Check if an invoice with the same details already exists and have a credit note
        $existingInvoice = invoices_history::where('link', $invoice_link)
            ->where('client_id', $client->id)
            ->where('type', 'Full')
            ->where('entreprise_id', $user_enterprise)
            ->where('Payment_status', '!=', -1)
            ->first();

        $latestInvoice = invoices_history::latest()
            ->where('entreprise_id', $user_enterprise)
            ->first();
        
        $currentYear = date('Y');
        $lastInvoiceYear = $latestInvoice ? date('Y', strtotime($latestInvoice->created_at)) : null;
        
        if ($lastInvoiceYear !== $currentYear) {
            $number = 0;
        } else {
            $number = $latestInvoice ? $latestInvoice->number : 0;
        }

        $invoice_id = 0;
        $invoicerealID = 0;

        $budget = floatval($budget);
        $hours = $request->has('hours') ? floatval($request->hours) : null;

        
            $save_invoice = new invoices_history();
            $save_invoice->project_name = $projectinfos->name;
            $save_invoice->link = '/admin/projects/invoice/' . $projectID;
            $save_invoice->client_id = $client->id;
            $save_invoice->entreprise_id = $user_enterprise;
            $save_invoice->start_date = $projectinfos->start_date;
            $save_invoice->end_date = $projectinfos->end_date;
            $save_invoice->number = sprintf('%03d', $number + 1);        
            $save_invoice->sentReminder = 0;
            $save_invoice->total_htva = $budget;
            $save_invoice->total_tva = ($budget*($projectinfos->tva/100));
            $save_invoice->total_ttc = $budget + ($budget*($projectinfos->tva/100));
            $save_invoice->project_id = $projectinfos->id;
            if (request()->has('devis_id') && !empty(request()->query('devis_id'))) {
                $save_invoice->devis_id = (int) request()->query('devis_id');
            }
            $save_invoice->comment = $comment ? base64_decode($comment) : null;
            $save_invoice->hours = $hours;
            
            $invoice_date = new DateTime();
            
            $days = (int) $client->due_date;
            $due_date_interval = new DateInterval('P' . $days . 'D');
            $save_invoice->due_date = $invoice_date->add($due_date_interval);
            
            $save_invoice->type = 'Full';

            $save_invoice->save();

            $invoice_id = $save_invoice->number;
            $invoicerealID = $save_invoice->id;

            $projectinfos->amount_billed +=  $save_invoice->total_htva;
            $projectinfos->save();

            $is_Editing = false;

            $pdf = PDF::loadView('admin.invoices.fullinvoicePDF', [ 
                'projectinfos' => $projectinfos,
                'client' => $client,
                'entreprise' => $entreprise,
                'number' => $save_invoice->number,
                'invoice_id' => $invoice_id,
                'comment' => $save_invoice->comment,
                'is_Editing' => $is_Editing,
                'due_date' => $save_invoice->due_date->format('d-m-Y'),
                'invoice_date' => $save_invoice->created_at->format('d-m-Y'),
                'hours' => $hours, // Pass hours to the view
                'cost_per_hour' => $projectinfos->cost_per_hour, // Pass cost per hour to the view
                'budget' => $budget
            ]); 
        
            $filePath = 'invoices/' . $entreprise->id . '/' . date('Y') . '/' . $entreprise->invoice_prefix . '-' . $save_invoice->number . '.pdf';
            Storage::disk('do_spaces')->put($filePath, $pdf->output(), 'public');

            if(Auth::user()->hasRole('Admin')){
                return redirect()->route('admin.invoices.invoicehistory')->with('message', 'Invoice created successfully.');
            }
            if(Auth::user()->hasRole('Responsable de projet')){
                return redirect()->route('Responsable_de_projet.invoices.invoicehistory')->with('message', 'Invoice created successfully.');
            }
            if(Auth::user()->hasRole('Responsable de projet et gestionnaire')){
                return redirect()->route('Responsable_gestionnaire.invoices.invoicehistory')->with('message', 'Invoice created successfully.');
            }
        
}


public function confirmPeriodInvoice($projectID, $startdate, $enddate, $comment = null){
    $projectinfos = Project::findOrFail($projectID); // Use findOrFail to handle case when project doesn't exist
    $client = Client::where('id', $projectinfos->client_id)->first();
    $user_enterprise = Auth::user()->entreprise_id;
    $entreprise = Entreprise::where('id', $user_enterprise)->first();

    $enddate = Carbon::parse($enddate)->endOfDay();

    // Parse selected task IDs if passed through query string
    $selectedTaskIds = [];
    if (request()->has('taskIds')) {
        $decoded = json_decode(request()->query('taskIds'), true);
        if (is_array($decoded)) {
            $selectedTaskIds = array_values(array_filter(array_map('intval', $decoded)));
        }
    }

    $tasks_timer = tasks_timer::where('entreprise_id', auth()->user()->entreprise_id)
        ->Where('start_date' , '>=', $startdate)
        ->whereNotNull('end_date')
        ->Where('end_date' , '<=', $enddate)
        ->where('duration', '!=', 0)
        ->orderBy('start_date', 'asc') // Order by start_date
        ->when(!empty($selectedTaskIds), function($q) use ($selectedTaskIds) {
            $q->whereIn('task_id', $selectedTaskIds);
        })
        ->get(); 

    $collection = collect();

    foreach ($tasks_timer as $task) {
        $task_project_id = Tasks::where('id', $task->task_id)->first();
        if ($task_project_id && $task_project_id->project_id == $projectID) {
            $collection->push($task); // Use push() to add the task to the collection
        }
    }

    $cost = 0;
    $duration = 0;

    foreach ($collection as $task) {
        $cost = $cost + $task->cost;
        $duration = $duration +  $task->duration;
    }

    $invoice_link = '/admin/projects/invoicebyduration2/' . $projectID . '/' . $startdate . '/' . $enddate;
    // Check if an invoice with the same details already exists and have a credit note
    $existingInvoice = invoices_history::where('link', $invoice_link)
        ->where('client_id', $client->id)
        ->where('type', 'Period')
        ->where('entreprise_id', $user_enterprise)
        ->where('Payment_status', '!=', -1)
        ->first();


        $latestInvoice = invoices_history::latest()
        ->where('entreprise_id', $user_enterprise)
        ->first(); // Get the latest invoice
        
        $currentYear = date('Y'); // Get the current year
        $lastInvoiceYear = $latestInvoice ? date('Y', strtotime($latestInvoice->created_at)) : null; // Get the year of the last invoice
        // Check if the last invoice year is different from the current year
        if ($lastInvoiceYear !== $currentYear) {
            $number = 0; // Initialize to 0 if the years are different
        } else {
            $number = $latestInvoice ? $latestInvoice->number : 0; // Set number to the latest invoice number or 0 if no invoice exists
        }

        $invoice_id = 0; // Initialize $invoice_id outside the if block
        $invoicerealID = 0;

        if (!$existingInvoice) {
           

            $save_invoice = new invoices_history();
            $save_invoice->project_name = $projectinfos->name;
            $save_invoice->link = '/admin/projects/invoicebyduration2/' . $projectID . '/' . $startdate . '/' . $enddate;
            $save_invoice->client_id = $client->id;
            $save_invoice->start_date = $startdate;
            $save_invoice->end_date = $enddate;
            $save_invoice->entreprise_id = $user_enterprise;
            $save_invoice->number = sprintf('%03d', $number + 1);            
            $save_invoice->sentReminder = 0;
            $save_invoice->duration = $duration;
            // Ensure comment is properly handled
            $save_invoice->comment = $comment ? base64_decode($comment) : null;

            $save_invoice->total_htva = ($projectinfos->cost_per_hour * ($duration/60));
            $save_invoice->total_tva = ($projectinfos->cost_per_hour * ($duration/60))* ($projectinfos->tva / 100);
            $save_invoice->total_ttc = ($projectinfos->cost_per_hour * ($duration/60))+(($projectinfos->cost_per_hour * ($duration/60))* ($projectinfos->tva / 100));

            $save_invoice->project_id = $projectinfos->id;
            if (request()->has('devis_id') && !empty(request()->query('devis_id'))) {
                $save_invoice->devis_id = (int) request()->query('devis_id');
            }
            $invoice_date = new DateTime(); 
            $days = (int) $client->due_date;
            $due_date_interval = new DateInterval('P' . $days . 'D');
            $save_invoice->due_date = $invoice_date->add($due_date_interval); 

            $save_invoice->type = 'Period';
        
            $save_invoice->save();
            $invoice_id = $save_invoice->number; // Get the invoice number
            $invoicerealID = $save_invoice->id;

            $projectinfos->amount_billed +=  $save_invoice->total_htva;
            $projectinfos->save();

            $is_Editing = false;
        $pdf = PDF::loadView('admin.invoices.invoicebydurationPDF', [
            'invoice' => $save_invoice,
            'projectinfos' => $projectinfos,
            'client' => $client,
            'entreprise' => $entreprise,
            'duration' => $duration,
            'invoice_id' => $invoice_id,
            'collection' => $collection,
            'comment' => $save_invoice->comment,
            'is_Editing' => $is_Editing,
            'due_date' => $save_invoice->due_date->format('d-m-Y'),
            'invoice_date' => $save_invoice->created_at->format('d-m-Y')
        ]);

        // Save the PDF to a file from do_spaces
        $filePath = 'invoices/' . $entreprise->id . '/' . date('Y') . '/' . $entreprise->invoice_prefix . '-' . $save_invoice->number . '.pdf';
        Storage::disk('do_spaces')->put($filePath, $pdf->output(), 'public');

        if(Auth::user()->hasRole('Admin')){
            return redirect()->route('admin.invoices.invoicehistory')->with('message', 'Invoice created successfully.');
        }
        if(Auth::user()->hasRole('Responsable de projet')){
            return redirect()->route('Responsable_de_projet.invoices.invoicehistory')->with('message', 'Invoice created successfully.');
        }
        if(Auth::user()->hasRole('Responsable de projet et gestionnaire')){
            return redirect()->route('Responsable_gestionnaire.invoices.invoicehistory')->with('message', 'Invoice created successfully.');
        }

        }else{
            if(Auth::user()->hasRole('Admin')){
                return back()->with('message', 'Invoice Already Exists: ' . $entreprise->invoice_prefix . '-' . date('ym', strtotime($existingInvoice->created_at)) . $existingInvoice->number);
            }
            if(Auth::user()->hasRole('Responsable de projet')){
                return back()->with('message', 'Invoice Already Exists: ' . $entreprise->invoice_prefix . '-' . date('ym', strtotime($existingInvoice->created_at)) . $existingInvoice->number);
            }
            if(Auth::user()->hasRole('Responsable de projet et gestionnaire')){
                return back()->with('message', 'Invoice Already Exists: ' . $entreprise->invoice_prefix . '-' . date('ym', strtotime($existingInvoice->created_at)) . $existingInvoice->number);
            }
        }
}
public function confirmPercentageInvoice($projectID, $percentage, $comment = null){

    // Resolve project via ID param or devis_id query
    $projectinfos = null;
    if (!empty($projectID)) {
        $projectinfos = Project::where('id', $projectID)->first();
    }
    if (!$projectinfos && request()->has('devis_id')) {
        $devis = \App\Models\devis_history::find(request()->query('devis_id'));
        if ($devis) {
            $projectinfos = Project::where('id', $devis->project_id)->first();
        }
    }
    if (!$projectinfos) {
        return back()->with('message', 'Unable to resolve project from selection');
    }
    $client = Client::where('id', $projectinfos->client_id)->first();
    $user_enterprise = Auth::user()->entreprise_id;
    $entreprise = Entreprise::where('id', $user_enterprise)->first();

    $invoice_link = '/admin/projects/invoice/' . $projectinfos->id;

    // Check if an invoice with the same details already exists and have a credit note
    $existingInvoice = invoices_history::where('link', $invoice_link)
        ->where('client_id', $client->id)
        ->where('type', 'Percentage')
        ->where('entreprise_id', $user_enterprise)
        ->where('Payment_status', '!=', -1)
        ->first();

    $latestInvoice = invoices_history::latest()
    ->where('entreprise_id', $user_enterprise)
    ->first(); // Get the latest invoice

    $currentYear = date('Y'); // Get the current year
    $lastInvoiceYear = $latestInvoice ? date('Y', strtotime($latestInvoice->created_at)) : null; // Get the year of the last invoice
    // Check if the last invoice year is different from the current year
    if ($lastInvoiceYear !== $currentYear) {
        $number = 0; // Initialize to 0 if the years are different
    } else {
        $number = $latestInvoice ? $latestInvoice->number : 0; // Set number to the latest invoice number or 0 if no invoice exists
    }

    $invoice_id = sprintf('%03d', $number + 1); // Initialize $invoice_id outside the if block
    $invoicerealID = 0;


    
        // If a devis is selected via query param, base on devis initial budget
        $amount = ((float)$projectinfos->budget * (float)$percentage) / 100;
        $principalBudget = (float)$projectinfos->budget;
        if (request()->has('devis_id') && !empty(request()->query('devis_id'))) {
            $selectedDevis = \App\Models\devis_history::where('id', request()->query('devis_id'))
                ->where('entreprise_id', $user_enterprise)
                ->first();
            if ($selectedDevis && $selectedDevis->budget) {
                $amount = ((float)$selectedDevis->budget * (float)$percentage) / 100;
                $principalBudget = (float)$selectedDevis->budget;
            }
        }

        $projectinfos->amount_billed += $amount;
        $projectinfos->save();

        $save_invoice = new invoices_history();
        $save_invoice->project_name = $projectinfos->name;
        $save_invoice->link = '/admin/projects/invoice/' . $projectID;
        $save_invoice->client_id = $client->id;
        $save_invoice->entreprise_id = $user_enterprise;
        $save_invoice->start_date = $projectinfos->start_date;
        $save_invoice->end_date = $projectinfos->end_date;
        $save_invoice->number = sprintf('%03d', $number + 1); 
        $save_invoice->comment = $comment ? base64_decode($comment) : null;      
        $save_invoice->sentReminder = 0;
        $save_invoice->total_htva = $amount;
        $save_invoice->total_tva = ($amount*($projectinfos->tva/100));
        $save_invoice->total_ttc = $amount + ($amount*($projectinfos->tva/100));
        $save_invoice->project_id = $projectinfos->id;
        if (request()->has('devis_id') && !empty(request()->query('devis_id'))) {
            $save_invoice->devis_id = (int) request()->query('devis_id');
        }
        $invoice_date = new DateTime(); // This is the date when the invoice is created
        $days = (int) $client->due_date;
        $due_date_interval = new DateInterval('P' . $days . 'D'); // This represents the due date in days
        $save_invoice->due_date = $invoice_date->add($due_date_interval); // This adds the due date to the invoice date
        
        $save_invoice->type = 'Percentage';
        $save_invoice->percentage = $percentage;

        $save_invoice->save();

        $invoice_id = $save_invoice->number; // Get the invoice number
        $invoicerealID = $save_invoice->id;

        $is_Editing = false;

        // Fetch devis number if provided
        $devisNumber = null;
        if (request()->has('devis_id') && !empty(request()->query('devis_id'))) {
            $devisRecord = \App\Models\devis_history::find((int) request()->query('devis_id'));
            if ($devisRecord) {
                $devisNumber = $devisRecord->devis_number;
            }
        }

        $pdf = PDF::loadView('admin.invoices.percentageInvoicePDF', [ 
            'projectinfos' => $projectinfos,
            'client' => $client,
            'entreprise' => $entreprise,
            'number' => $save_invoice->number,
            'invoice_id' => $invoice_id,
            'amount' => $amount,
            'comment' => $save_invoice->comment,
            'percentage' => $percentage,
            'principalBudget' => $principalBudget,
            'is_Editing' => $is_Editing,
            'due_date' => $save_invoice->due_date->format('d-m-Y'),
            'invoice_date' => $save_invoice->created_at->format('d-m-Y'),
            'devis_number' => $devisNumber,
        ]); 

        // Save the PDF to a file from do_spaces
        $filePath = 'invoices/' . $entreprise->id . '/' . date('Y') . '/' . $entreprise->invoice_prefix . '-' . $save_invoice->number . '.pdf';
        Storage::disk('do_spaces')->put($filePath, $pdf->output(), 'public');

        if(Auth::user()->hasRole('Admin')){
            return redirect()->route('admin.invoices.invoicehistory')->with('message', 'Invoice created successfully.');
        }
        if(Auth::user()->hasRole('Responsable de projet')){
            return redirect()->route('Responsable_de_projet.invoices.invoicehistory')->with('message', 'Invoice created successfully.');
        }
        if(Auth::user()->hasRole('Responsable de projet et gestionnaire')){
            return redirect()->route('Responsable_gestionnaire.invoices.invoicehistory')->with('message', 'Invoice created successfully.');
        }
    
}
public function confirmPartialInvoice($projectID, $amount, $comment = null){

    $projectinfos = Project::where('id', $projectID)->first();
    $client = Client::where('id', $projectinfos->client_id)->first();
    $user_enterprise = Auth::user()->entreprise_id;
    $entreprise = Entreprise::where('id', $user_enterprise)->first();

    $invoice_link = '/admin/projects/invoice/' . $projectinfos->id;

    // Check if an invoice with the same details already exists and have a credit note
    $existingInvoice = invoices_history::where('link', $invoice_link)
        ->where('client_id', $client->id)
        ->where('type', 'Partial')
        ->where('entreprise_id', $user_enterprise)
        ->where('Payment_status', '!=', -1)
        ->first();

    $latestInvoice = invoices_history::latest()
    ->where('entreprise_id', $user_enterprise)
    ->first(); // Get the latest invoice

    $currentYear = date('Y'); // Get the current year
    $lastInvoiceYear = $latestInvoice ? date('Y', strtotime($latestInvoice->created_at)) : null; // Get the year of the last invoice
    // Check if the last invoice year is different from the current year
    if ($lastInvoiceYear !== $currentYear) {
        $number = 0; // Initialize to 0 if the years are different
    } else {
        $number = $latestInvoice ? $latestInvoice->number : 0; // Set number to the latest invoice number or 0 if no invoice exists
    }


    $invoice_id = 0; // Initialize $invoice_id outside the if block
    $invoicerealID = 0;

    
    
        $projectinfos->amount_billed += $amount;
        $projectinfos->save();

        $save_invoice = new invoices_history();
        $save_invoice->project_name = $projectinfos->name;
        $save_invoice->link = '/admin/projects/invoice/' . $projectID;
        $save_invoice->client_id = $client->id;
        $save_invoice->entreprise_id = $user_enterprise;
        $save_invoice->start_date = $projectinfos->start_date;
        $save_invoice->end_date = $projectinfos->end_date;
        $save_invoice->number = sprintf('%03d', $number + 1);  
        $save_invoice->comment = $comment ? base64_decode($comment) : null;      
        $save_invoice->sentReminder = 0;
        $save_invoice->total_htva = $amount;
        $save_invoice->total_tva = ($amount*($projectinfos->tva/100));
        $save_invoice->total_ttc = $amount + ($amount*($projectinfos->tva/100));
        $save_invoice->project_id = $projectinfos->id;
        if (request()->has('devis_id') && !empty(request()->query('devis_id'))) {
            $save_invoice->devis_id = (int) request()->query('devis_id');
        }
        $invoice_date = new DateTime(); // This is the date when the invoice is created
        $days = (int) $client->due_date;
        $due_date_interval = new DateInterval('P' . $days . 'D'); // This represents the due date in days
        $save_invoice->due_date = $invoice_date->add($due_date_interval); // This adds the due date to the invoice date
        
        $save_invoice->type = 'Partial';
        $save_invoice->partial = $amount;

        $save_invoice->save();

        $invoice_id = $save_invoice->number; // Get the invoice number
        $invoicerealID = $save_invoice->id;

        $is_Editing = false;

        // Fetch devis number if provided
        $devisNumber = null;
        if (request()->has('devis_id') && !empty(request()->query('devis_id'))) {
            $devisRecord = \App\Models\devis_history::find((int) request()->query('devis_id'));
            if ($devisRecord) {
                $devisNumber = $devisRecord->devis_number;
            }
        }

        $pdf = PDF::loadView('admin.invoices.partialInvoicePDF', [ 
            'projectinfos' => $projectinfos,
            'client' => $client,
            'entreprise' => $entreprise,
            'number' => $save_invoice->number,
            'invoice_id' => $invoice_id,
            'amount' => $amount,
            'comment' => $save_invoice->comment,
            'principalBudget' => request()->has('devis_id') && isset($selectedDevis) && $selectedDevis ? (float)$selectedDevis->budget : (float)$projectinfos->budget,
            'is_Editing' => $is_Editing,
            'due_date' => $save_invoice->due_date->format('d-m-Y'),
            'invoice_date' => $save_invoice->created_at->format('d-m-Y'),
            'devis_number' => $devisNumber,
        ]); 

    
        // Save the PDF to a file from do_spaces
        $filePath = 'invoices/' . $entreprise->id . '/' . date('Y') . '/' . $entreprise->invoice_prefix . '-' . $save_invoice->number . '.pdf';
        Storage::disk('do_spaces')->put($filePath, $pdf->output(), 'public');

        if(Auth::user()->hasRole('Admin')){
            return redirect()->route('admin.invoices.invoicehistory')->with('message', 'Invoice created successfully.');
        }
        if(Auth::user()->hasRole('Responsable de projet')){
            return redirect()->route('Responsable_de_projet.invoices.invoicehistory')->with('message', 'Invoice created successfully.');
        }
        if(Auth::user()->hasRole('Responsable de projet et gestionnaire')){
            return redirect()->route('Responsable_gestionnaire.invoices.invoicehistory')->with('message', 'Invoice created successfully.');
        }
    
}

public function declineInvoice(){
    if(Auth::user()->hasRole('Admin')){
        return redirect()->route('admin.invoices.invoicehistory');
    }
    if(Auth::user()->hasRole('Responsable de projet')){
        return redirect()->route('Responsable_de_projet.invoices.invoicehistory');
    }
    if(Auth::user()->hasRole('Responsable de projet et gestionnaire')){
        return redirect()->route('Responsable_gestionnaire.invoices.invoicehistory');
    }
}

public function projectArchiveIndex(){
    $projects = Project::where('entreprise_id', auth()->user()->entreprise_id)->where(function($query) {
        $query->whereNull('AcceptedDevisProject')
              ->orWhere('AcceptedDevisProject', '=', 1);
    })
    ->where('archived', 1)
    ->orderBy('name')
    ->get();
    $employees = Employees::where('entreprise_id', Auth::user()->entreprise_id)->where('archived', 0)->orderBy('name', 'asc')->get();
    $tasks = Tasks::where('user_id', Auth::user()->id)->get();
    $teams = Teams::where('entreprise_id', Auth::user()->entreprise_id)->get();
    return view('admin.projects.archive', compact('projects', 'employees', 'tasks', 'teams'));  
}

public function archiveProject($id){
    $project = Project::find($id);
    $project->archived = 1;
    $project->save();
    if(Auth::user()->hasRole('Admin')){
        return redirect()->route('admin.projects.index')->with('message', 'Project archived successfully.');
    }
    if(Auth::user()->hasRole('Responsable de projet')){
        return redirect()->route('Responsable_de_projet.projects.index')->with('message', 'Project archived successfully.');
    }
    if(Auth::user()->hasRole('Responsable de projet et gestionnaire')){
        return redirect()->route('Responsable_gestionnaire.projects.index')->with('message', 'Project archived successfully.');
    }
}

public function unarchiveProject($id){
    $project = Project::find($id);
    $project->archived = 0;
    $project->save();
    if(Auth::user()->hasRole('Admin')){
        return redirect()->route('admin.projects.index')->with('message', 'Project unarchived successfully.');
    }
    if(Auth::user()->hasRole('Responsable de projet')){
        return redirect()->route('Responsable_de_projet.projects.index')->with('message', 'Project unarchived successfully.');
    }
    if(Auth::user()->hasRole('Responsable de projet et gestionnaire')){
        return redirect()->route('Responsable_gestionnaire.projects.index')->with('message', 'Project unarchived successfully.');
    }
}

public function devisArchiveIndex(){
    $entreprise = Entreprise::where('id', Auth::user()->entreprise_id)->first();
    $devis = devis_history::where('entreprise_id', Auth::user()->entreprise_id)
    ->where('archived', 1)  
    ->get();
    $clients = Client::where('entreprise_id', Auth::user()->entreprise_id)->where(function($query) {
        $query->whereNull('AcceptedDevisClient')
              ->orWhere('AcceptedDevisClient', '=', 1);
    })->get();
    $projects = Project::where('entreprise_id', Auth::user()->entreprise_id)->where(function($query) {
            $query->whereNull('AcceptedDevisProject')
                  ->orWhere('AcceptedDevisProject', '=', 1);
        })
        ->where('archived', 0)
        ->get();
    return view('admin.devis.archive', compact('devis','clients','projects', 'entreprise'));
}

public function archiveDevis($id){
    $devis = devis_history::find($id);
    $devis->archived = 1;
    $devis->save();
    if(Auth::user()->hasRole('Admin')){
        return redirect()->route('admin.devis.devishistory')->with('message', 'devis archived successfully.');
    }
    if(Auth::user()->hasRole('Responsable de projet')){
        return redirect()->route('Responsable_de_projet.devis.devishistory')->with('message', 'devis archived successfully.');
    }
    if(Auth::user()->hasRole('Responsable de projet et gestionnaire')){
        return redirect()->route('Responsable_gestionnaire.devis.devishistory')->with('message', 'devis archived successfully.');
    }
}

public function unarchiveDevis($id){
    $devis = devis_history::find($id);
    $devis->archived = 0;
    $devis->save();
    if(Auth::user()->hasRole('Admin')){
        return redirect()->route('admin.devis.devishistory')->with('message', 'devis unarchived successfully.');
    }
    if(Auth::user()->hasRole('Responsable de projet')){
        return redirect()->route('Responsable_de_projet.devis.devishistory')->with('message', 'devis unarchived successfully.');
    }
    if(Auth::user()->hasRole('Responsable de projet et gestionnaire')){
        return redirect()->route('Responsable_gestionnaire.devis.devishistory')->with('message', 'devis unarchived successfully.');
    }
}

public function invoiceArchiveIndex()
    {
        $invoices = invoices_history::where('entreprise_id', Auth::user()->entreprise_id)
        ->where('archived', 1)
        ->orderBy('created_at')
        ->get();
        $clients = Client::where('entreprise_id', Auth::user()->entreprise_id)->where(function($query) {
            $query->whereNull('AcceptedDevisClient')
                  ->orWhere('AcceptedDevisClient', '=', 1);
        })->get();
        $projects = Project::where('entreprise_id', Auth::user()->entreprise_id)->where(function($query) {
            $query->whereNull('AcceptedDevisProject')
                  ->orWhere('AcceptedDevisProject', '=', 1);
        })
        ->where('archived', 0)
        ->get();
        $entreprise_infos = Entreprise::where('id', Auth::user()->entreprise_id)->first();

        return view('admin.invoices.archive', compact('invoices','clients','projects', 'entreprise_infos'));
    }

    public function editFullInvoice($id)
{
    $invoice = invoices_history::findOrFail($id);
    $entreprise = Entreprise::where('id', Auth::user()->entreprise_id)->first();
    
    // Get all projects for the dropdown
    $projects = Project::where('entreprise_id', Auth::user()->entreprise_id)
        ->where(function($query) {
            $query->whereNull('AcceptedDevisProject')
                  ->orWhere('AcceptedDevisProject', '=', 1);
        })
        ->where('archived', 0)
        ->get();

    return view('admin.invoices.editFullInvoice', compact('invoice', 'projects', 'entreprise'));
}

// Edit functions
public function editPartialInvoice($id)
{
    $invoice = invoices_history::findOrFail($id);
    $entreprise = Entreprise::where('id', Auth::user()->entreprise_id)->first();
    
    // Get all projects for the dropdown
    $projects = Project::where('entreprise_id', Auth::user()->entreprise_id)
        ->where(function($query) {
            $query->whereNull('AcceptedDevisProject')
                  ->orWhere('AcceptedDevisProject', '=', 1);
        })
        ->where('archived', 0)
        ->get();

    return view('admin.invoices.editPartialInvoice', compact('invoice', 'projects', 'entreprise'));
}

public function editPercentageInvoice($id)
{
    $invoice = invoices_history::findOrFail($id);
    $entreprise = Entreprise::where('id', Auth::user()->entreprise_id)->first();
    
    $projects = Project::where('entreprise_id', Auth::user()->entreprise_id)
        ->where(function($query) {
            $query->whereNull('AcceptedDevisProject')
                  ->orWhere('AcceptedDevisProject', '=', 1);
        })
        ->where('archived', 0)
        ->get();

    return view('admin.invoices.editPercentageInvoice', compact('invoice', 'projects', 'entreprise'));
}

public function editPeriodInvoice($id)
{
    $invoice = invoices_history::findOrFail($id);
    $entreprise = Entreprise::where('id', Auth::user()->entreprise_id)->first();
    
    $projects = Project::where('entreprise_id', Auth::user()->entreprise_id)
        ->where(function($query) {
            $query->whereNull('AcceptedDevisProject')
                  ->orWhere('AcceptedDevisProject', '=', 1);
        })
        ->where('archived', 0)
        ->get();

    return view('admin.invoices.editPeriodInvoice', compact('invoice', 'projects', 'entreprise'));
}

public function updateFullInvoice(Request $request, $id)
{
    $invoice = invoices_history::findOrFail($id);
    $project = Project::findOrFail($request->project_id);
    
    // Validate the request
    $request->validate([
        'project_id' => 'required|exists:projects,id',
        'comment' => 'nullable|string',
    ]);

    DB::beginTransaction();
    try {
        // 1. First revert the old amount from the old project
        $oldProject = Project::findOrFail($invoice->project_id);
        $oldProject->amount_billed -= $invoice->total_htva;
        $oldProject->save();

        // 2. Update invoice details
        if ($request->project_id != $invoice->project_id) {
            $invoice->project_id = $request->project_id;
            $invoice->project_name = $project->name;
        }

        // 3. Calculate and set the new amounts
        $invoice->total_htva = $project->budget;
        $invoice->total_tva = $project->budget * ($project->tva/100);
        $invoice->total_ttc = $project->budget + ($project->budget * ($project->tva/100));
        $invoice->comment = $request->comment ? urldecode($request->comment) : null;

        // 4. Save the invoice first
        $invoice->save();

        // 5. Update the project's billed amount
        $totalBilled = invoices_history::where('project_id', $project->id)
            ->where('id', '!=', $invoice->id)
            ->sum('total_htva');
        
        $totalBilled += $invoice->total_htva;
        $project->amount_billed = $totalBilled;
        $project->save();
        
        // Generate new PDF
        $client = Client::findOrFail($project->client_id);
        $entreprise = Entreprise::findOrFail(Auth::user()->entreprise_id);
        
        $decodedComment = urldecode($request->comment);
        $formattedComment = str_replace('+++', "\n", $decodedComment);
        $is_Editing = true;

        $pdf = PDF::loadView('admin.invoices.fullinvoicePDF', [ 
            'projectinfos' => $project,
            'client' => $client,
            'entreprise' => $entreprise,
            'number' => $invoice->number,
            'invoice_id' => $invoice->number,
            'comment' => $formattedComment,
            'is_Editing' => $is_Editing,
            'due_last_date' => Carbon::parse($invoice->due_date)->format('d-m-Y'),
            'invoice_last_date' => Carbon::parse($invoice->created_at)->format('d-m-Y')
        ]);

        $filePath = 'invoices/' . $entreprise->id . '/' . date('Y') . '/' . $entreprise->invoice_prefix . '-' . $invoice->number . '.pdf';
        Storage::disk('do_spaces')->put($filePath, $pdf->output(), 'public');
        
        DB::commit();
        if(Auth::user()->hasRole('Admin')){
            return redirect()->route('admin.invoices.invoicehistory')
                ->with('success', 'Invoice updated successfully');
        }
        if(Auth::user()->hasRole('Responsable de projet')){
            return redirect()->route('Responsable_de_projet.invoices.invoicehistory')
                ->with('success', 'Invoice updated successfully');
        }
        if(Auth::user()->hasRole('Responsable de projet et gestionnaire')){
            return redirect()->route('Responsable_gestionnaire.invoices.invoicehistory')
                ->with('success', 'Invoice updated successfully');
        }
            
    } catch (\Exception $e) {
        DB::rollback();
        return back()->with('error', 'Error updating invoice: ' . $e->getMessage());
    }
}

public function updatePartialInvoice(Request $request, $id)
{
    $invoice = invoices_history::findOrFail($id);
    $project = Project::findOrFail($request->project_id);
    
    // Validate the request
    $request->validate([
        'project_id' => 'required|exists:projects,id',
        'amount' => 'required|numeric',
        'comment' => 'nullable|string',
    ]);

    DB::beginTransaction();
    try {
        // 1. First revert the old amount from the old project
        $oldProject = Project::findOrFail($invoice->project_id);
        $oldProject->amount_billed -= $invoice->total_htva;
        $oldProject->save();

        // 2. Update invoice details
        if ($request->project_id != $invoice->project_id) {
            $invoice->project_id = $request->project_id;
            $invoice->project_name = $project->name;
        }

        // 3. Calculate and set the new amounts
        $invoice->total_htva = $request->amount;
        $invoice->total_tva = $request->amount * ($project->tva/100);
        $invoice->total_ttc = $request->amount + ($request->amount * ($project->tva/100));
        $invoice->comment = $request->comment ? urldecode($request->comment) : null;
        $invoice->partial = $request->amount;

        // 4. Save the invoice first
        $invoice->save();

        // 5. Update the project's billed amount
        $totalBilled = invoices_history::where('project_id', $project->id)
            ->where('id', '!=', $invoice->id)
            ->sum('total_htva');
        
        $totalBilled += $invoice->total_htva;
        $project->amount_billed = $totalBilled;
        $project->save();
        
        // Generate new PDF
        $client = Client::findOrFail($project->client_id);
        $entreprise = Entreprise::findOrFail(Auth::user()->entreprise_id);
        
        $is_Editing = true;

        $pdf = PDF::loadView('admin.invoices.partialInvoicePDF', [ 
            'projectinfos' => $project,
            'client' => $client,
            'entreprise' => $entreprise,
            'number' => $invoice->number,
            'invoice_id' => $invoice->number,
            'amount' => $request->amount,
            'comment' => $request->comment ? urldecode($request->comment) : null,
            'is_Editing' => $is_Editing,
            'due_last_date' => Carbon::parse($invoice->due_date)->format('d-m-Y'),
            'invoice_last_date' => Carbon::parse($invoice->created_at)->format('d-m-Y')
        ]);

        $filePath = 'invoices/' . $entreprise->id . '/' . date('Y') . '/' . $entreprise->invoice_prefix . '-' . $invoice->number . '.pdf';
        Storage::disk('do_spaces')->put($filePath, $pdf->output(), 'public');
        
        DB::commit();
        if(Auth::user()->hasRole('Admin')){
            return redirect()->route('admin.invoices.invoicehistory')
                ->with('success', 'Invoice updated successfully');
        }
        if(Auth::user()->hasRole('Responsable de projet')){
            return redirect()->route('Responsable_de_projet.invoices.invoicehistory')
                ->with('success', 'Invoice updated successfully');
        }
        if(Auth::user()->hasRole('Responsable de projet et gestionnaire')){
            return redirect()->route('Responsable_gestionnaire.invoices.invoicehistory')
                ->with('success', 'Invoice updated successfully');
        }
            
    } catch (\Exception $e) {
        DB::rollback();
        return back()->with('error', 'Error updating invoice: ' . $e->getMessage());
    }
}

public function updatePercentageInvoice(Request $request, $id)
{
    $invoice = invoices_history::findOrFail($id);
    $project = Project::findOrFail($request->project_id);
    
    // Validate the request
    $request->validate([
        'project_id' => 'required|exists:projects,id',
        'percentage' => 'required|numeric|min:0|max:100',
        'comment' => 'nullable|string',
    ]);

    DB::beginTransaction();
    try {
        // 1. First revert the old amount from the old project
        $oldProject = Project::findOrFail($invoice->project_id);
        $oldProject->amount_billed -= $invoice->total_htva;
        $oldProject->save();

        // 2. Update invoice details
        if ($request->project_id != $invoice->project_id) {
            $invoice->project_id = $request->project_id;
            $invoice->project_name = $project->name;
        }

        // 3. Calculate and set the new amounts
        $amount = ((float)$project->budget * (float)$request->percentage) / 100;
        $invoice->total_htva = $amount;
        $invoice->total_tva = $amount * ($project->tva/100);
        $invoice->total_ttc = $amount + ($amount * ($project->tva/100));
        $invoice->comment = $request->comment ? urldecode($request->comment) : null;
        $invoice->percentage = $request->percentage;

        // 4. Save the invoice first
        $invoice->save();

        // 5. Update the project's billed amount
        $totalBilled = invoices_history::where('project_id', $project->id)
            ->where('id', '!=', $invoice->id)
            ->sum('total_htva');
        
        $totalBilled += $invoice->total_htva;
        $project->amount_billed = $totalBilled;
        $project->save();
        
        // Generate new PDF
        $client = Client::findOrFail($project->client_id);
        $entreprise = Entreprise::findOrFail(Auth::user()->entreprise_id);
        
        $is_Editing = true;

        $pdf = PDF::loadView('admin.invoices.percentageInvoicePDF', [ 
            'projectinfos' => $project,
            'client' => $client,
            'entreprise' => $entreprise,
            'number' => $invoice->number,
            'invoice_id' => $invoice->number,
            'amount' => $amount,
            'comment' => $request->comment ? urldecode($request->comment) : null,
            'percentage' => $request->percentage,
            'is_Editing' => $is_Editing,
            'due_last_date' => Carbon::parse($invoice->due_date)->format('d-m-Y'),
            'invoice_last_date' => Carbon::parse($invoice->created_at)->format('d-m-Y')
        ]);

        $filePath = 'invoices/' . $entreprise->id . '/' . date('Y') . '/' . $entreprise->invoice_prefix . '-' . $invoice->number . '.pdf';
        Storage::disk('do_spaces')->put($filePath, $pdf->output(), 'public');
        
        DB::commit();
        if(Auth::user()->hasRole('Admin')){
            return redirect()->route('admin.invoices.invoicehistory')
                ->with('success', 'Invoice updated successfully');
        }
        if(Auth::user()->hasRole('Responsable de projet')){
            return redirect()->route('Responsable_de_projet.invoices.invoicehistory')
                ->with('success', 'Invoice updated successfully');
        }
        if(Auth::user()->hasRole('Responsable de projet et gestionnaire')){
            return redirect()->route('Responsable_gestionnaire.invoices.invoicehistory')
                ->with('success', 'Invoice updated successfully');
        }
            
    } catch (\Exception $e) {
        DB::rollback();
        return back()->with('error', 'Error updating invoice: ' . $e->getMessage());
    }
}

public function updatePeriodInvoice(Request $request, $id)
{
    $invoice = invoices_history::findOrFail($id);
    $project = Project::findOrFail($request->project_id);
    
    // Validate the request
    $request->validate([
        'project_id' => 'required|exists:projects,id',
        'start_date' => 'required|date',
        'end_date' => 'required|date|after:start_date',
        'comment' => 'nullable|string',
    ]);

    DB::beginTransaction();
    try {
        // 1. First revert the old amount from the old project
        $oldProject = Project::findOrFail($invoice->project_id);
        $oldProject->amount_billed -= $invoice->total_htva;
        $oldProject->save();

        $enddate = Carbon::parse($request->end_date)->endOfDay();

        // Get tasks for the period
        $tasks_timer = tasks_timer::where('entreprise_id', auth()->user()->entreprise_id)
            ->Where('start_date' , '>=', $request->start_date)
            ->whereNotNull('end_date')
            ->Where('end_date' , '<=', $enddate)
            ->orderBy('start_date', 'asc')
            ->get();

        $collection = collect();
        foreach ($tasks_timer as $task) {
            $task_project_id = Tasks::where('id', $task->task_id)->first();
            if ($task_project_id && $task_project_id->project_id == $request->project_id) {
                $collection->push($task);
            }
        }

        $duration = $collection->sum('duration');

        // 2. Update invoice details
        if ($request->project_id != $invoice->project_id) {
            $invoice->project_id = $request->project_id;
            $invoice->project_name = $project->name;
        }

        // 3. Calculate and set the new amounts
        $total_htva = ($project->cost_per_hour * ($duration/60));
        $total_tva = $total_htva * ($project->tva/100);
        $total_ttc = $total_htva + $total_tva;

        $invoice->start_date = $request->start_date;
        $invoice->end_date = $request->end_date;
        $invoice->duration = $duration;
        $invoice->total_htva = $total_htva;
        $invoice->total_tva = $total_tva;
        $invoice->total_ttc = $total_ttc;
        $invoice->comment = $request->comment ? urldecode($request->comment) : null;

        // 4. Save the invoice first
        $invoice->save();

        // 5. Update the project's billed amount
        $totalBilled = invoices_history::where('project_id', $project->id)
            ->where('id', '!=', $invoice->id)
            ->sum('total_htva');
        
        $totalBilled += $invoice->total_htva;
        $project->amount_billed = $totalBilled;
        $project->save();
        
        // Generate new PDF
        $client = Client::findOrFail($project->client_id);
        $entreprise = Entreprise::findOrFail(Auth::user()->entreprise_id);
        
        $is_Editing = true;

        $pdf = PDF::loadView('admin.invoices.invoicebydurationPDF', [
            'invoice' => $invoice,
            'projectinfos' => $project,
            'client' => $client,
            'entreprise' => $entreprise,
            'duration' => $duration,
            'invoice_id' => $invoice->number,
            'collection' => $collection,
            'comment' => $invoice->comment,
            'is_Editing' => $is_Editing,
            'due_last_date' => Carbon::parse($invoice->due_date)->format('d-m-Y'),
            'invoice_last_date' => Carbon::parse($invoice->created_at)->format('d-m-Y')
        ]);

        $filePath = 'invoices/' . $entreprise->id . '/' . date('Y') . '/' . $entreprise->invoice_prefix . '-' . $invoice->number . '.pdf';
        Storage::disk('do_spaces')->put($filePath, $pdf->output(), 'public');
        
        DB::commit();
        if(Auth::user()->hasRole('Admin')){
            return redirect()->route('admin.invoices.invoicehistory')
                ->with('success', 'Invoice updated successfully');
        }
        if(Auth::user()->hasRole('Responsable de projet')){
            return redirect()->route('Responsable_de_projet.invoices.invoicehistory')
                ->with('success', 'Invoice updated successfully');
        }
        if(Auth::user()->hasRole('Responsable de projet et gestionnaire')){
            return redirect()->route('Responsable_gestionnaire.invoices.invoicehistory')
                ->with('success', 'Invoice updated successfully');
        }
            
    } catch (\Exception $e) {
        DB::rollback();
        return back()->with('error', 'Error updating invoice: ' . $e->getMessage());
    }
}

public function editDevis($devis_id) {
    $devis = devis_history::where('id', $devis_id)->first();
    $devis_details = devis_details::where('devis_id', $devis_id)->get();
    $clients = Client::where('entreprise_id', Auth::user()->entreprise_id)
        ->where(function($query) {
            $query->whereNull('AcceptedDevisClient')
                  ->orWhere('AcceptedDevisClient', '=', 1);
        })
        ->orderby('name', 'asc')
        ->get();
    $projects = Project::where('entreprise_id', Auth::user()->entreprise_id)
        ->where(function($query) {
            $query->whereNull('AcceptedDevisProject')
                  ->orWhere('AcceptedDevisProject', '=', 1);
        })
        ->where('archived', 0)
        ->orderby('name', 'asc')
        ->get();
    
    $client = Client::where('id', $devis->client_id)->where('entreprise_id', Auth::user()->entreprise_id)->first();

    $selectedContacts = client_contacts_devis::where('devis_id', $devis->id)
    ->pluck('contact_id')
    ->toArray();
    return view('admin.devis.editDevis', compact('devis', 'clients', 'projects', 'client', 'devis_details','selectedContacts'));
}

public function updateDevis(Request $request, $devis_id)
{
    $entreprise = Entreprise::where('id', Auth::user()->entreprise_id)->first();    
    $devis = devis_history::findOrFail($devis_id);
    
    // Handle billing type (forfait / horaire) similarly to saveDevisPdf
    $billingType = $request->input('billing_type', 'forfait');
    $hoursInput = $request->input('hours');
    $amountInput = $request->input('amount_per_hour');

    $hoursValue = is_numeric($hoursInput) ? (float) $hoursInput : 0;
    $amountValue = is_numeric($amountInput) ? (float) $amountInput : 0;

    $calculatedBudget = (float) $request->input('budget', 0);
    $costPerHour = (float) $request->input('cost_per_hour', 0);

    if ($billingType === 'horaire') {
        $calculatedBudget = $hoursValue * $amountValue;
        $costPerHour = $amountValue;
    }

    $storedHours = $billingType === 'horaire' ? $hoursValue : null;
    $storedAmountPerHour = $billingType === 'horaire' ? $amountValue : null;

    // Store old file path for deletion
    $oldFilePath = 'devis/' . $entreprise->id . '/' . $devis->devis_number . '.pdf';

    // Update devis details
    $devis->Project_name = $request->Project_name;
    $devis->cost_per_hour = $costPerHour;
    $devis->budget = $calculatedBudget;
    $devis->tva = $request->project_tva;
    $devis->billing_type = $billingType;
    $devis->hours = $storedHours;
    $devis->amount_per_hour = $storedAmountPerHour;

    if ($request->input('newclient_name')) {
        $newClient = new Client();
        $devis->emails = $request->emails; 
        $newClient->name = $request->newclient_name;
        $newClient->client_number = $request->newclient_number;
        $newClient->phone = $request->newclient_phone;
        $newClient->address = $request->newclient_street;
        $newClient->city = $request->newclient_city;
        $newClient->zip = $request->newclient_zip;
        $newClient->due_date = 0;
        $newClient->country = $request->newclient_country;
        $newClient->entreprise_id = Auth::user()->entreprise_id;
        $newClient->AcceptedDevisClient = 0;
        $newClient->save();
        $devis->client_id = $newClient->id;
        $devis->client_name = $request->newclient_name;

        if($request->Project_name){
            $newProject = new Project();
            $newProject->name = $request->Project_name; 
            $newProject->cost_per_hour = $request->cost_per_hour;
            $newProject->budget = $request->budget;
            $newProject->start_date = now();
            $newProject->tva = $request->project_tva;
            $newProject->AcceptedDevisProject = 0;
            $newProject->client_id = $newClient->id;
            $newProject->entreprise_id = Auth::user()->entreprise_id;
            $newProject->save(); 
            $devis->project_id = $newProject->id;
        }else{
            $devis->project_id = $request->Existing_project_id;
            $newProject = Project::where('id', $request->Existing_project_id)->first();
            $devis->Existing_project_name = $newProject->name;
        }   

    }else{
        $devis->client_id = $request->client;
        $devis->client_name = Client::where('id', $request->client)->first()->name;

        if($request->Project_name){
            $newProject = new Project();
            $newProject->name = $request->Project_name; 
            $newProject->cost_per_hour = $request->cost_per_hour;
            $newProject->budget = $request->budget;
            $newProject->start_date = now();
            $newProject->tva = $request->project_tva;
            $newProject->AcceptedDevisProject = 0;
            $newProject->client_id = $request->client;
            $newProject->entreprise_id = Auth::user()->entreprise_id;
            $newProject->save();
            $devis->project_id = $newProject->id;
        }else{
            $devis->project_id = $request->Existing_project_id;
            $newProject = Project::where('id', $request->Existing_project_id)->first();
            $devis->Existing_project_name = $newProject->name;
        }
    }


    $devis->description = $request->description;
    // Note: We don't update entreprise_id or devis_number as these should remain constant

    $devis->save();

    // Update contacts
    client_contacts_devis::where('devis_id', $devis->id)->delete(); // Remove old contacts
    if($request->input('contacts')) {
        $selectedContacts = $request->input('contacts');
        $contacts = client_contacts::whereIn('id', $selectedContacts)->get();
        foreach ($contacts as $c) {
            $client_contacts_devis = new client_contacts_devis([
                'devis_id' => $devis->id,
                'contact_id' => $c->id,
                'client_id' => $request->client,
                'devis_number' => $devis->devis_number,
                'entreprise_id' => Auth::user()->entreprise_id
            ]);
            $client_contacts_devis->save();
        }
    }

    $tasks = $request->input('task');
    $taskBudget = $request->input('taskBudget');

    // Delete all existing devis details for this devis
    devis_details::where('devis_id', $devis->id)->delete();

    // Create new devis details
    if ($tasks && $taskBudget) {
        foreach ($tasks as $index => $task) {
            if (!empty($task) && isset($taskBudget[$index])) {
                $devis_detail = new devis_details();
                $devis_detail->devis_id = $devis->id;
                $devis_detail->task_details = $task;
                $devis_detail->task_budget = $taskBudget[$index];
                $devis_detail->entreprise_id = Auth::user()->entreprise_id;
                $devis_detail->save();
            }
        }
    }
    

    $project_name = $request->Project_name ?: $devis->Existing_project_name;

    // Generate new PDF
    $pdf = PDF::loadView('admin.devis.view', [
        'project_name' => $project_name,
        'prix_unitaire' => $request->cost_per_hour,
        'budget' => $request->budget,
        'tva' => $devis->tva,
        'description' => $request->description,
        'entreprise' => $entreprise,
        'devisNum' => $devis->devis_number,
        'cost_per_hour' => $request->cost_per_hour,
        'tasks' => $tasks,
        'taskBudget' => $taskBudget,
        'devis_id' => $devis->id
    ]);

    // Delete old PDF if it exists
    if (Storage::disk('do_spaces')->exists($oldFilePath)) {
        Storage::disk('do_spaces')->delete($oldFilePath);
    }

    // Save new PDF
    $newFilePath = 'devis/' . $entreprise->id . '/' . $devis->devis_number . '.pdf';
    Storage::disk('do_spaces')->put($newFilePath, $pdf->output(), 'public');

    // Get the full URL
    $fullUrl = Storage::disk('do_spaces')->url($newFilePath);

    return redirect($fullUrl)->with('success', 'Devis updated successfully');
}

public function uploadProjectFile(Request $request, $projectId)
{
    $request->validate([
        'file' => 'required|file|max:2048', // Adjust max size as needed
    ]);

    $entreprise = Entreprise::where('id', Auth::user()->entreprise_id)->first();

    $project = Project::findOrFail($projectId);
    $file = $request->file('file');
    // {{ edit_1 }}
    // $fileName = time() . '_' . $file->getClientOriginalName(); // Remove timestamp prefix
    $fileName = $file->getClientOriginalName(); // Save the original file name
    // {{ edit_1 }}

    $filePath = 'project_files/'. $entreprise->id . '/' . $project->id . '/' . $fileName;

    // Save the file to storage
    Storage::disk('do_spaces')->put($filePath, file_get_contents($file), 'public');

    // Save file info to the database
    $projectFile = new ProjectFile();
    $projectFile->project_id = $project->id;
    $projectFile->file_name = $fileName;
    $projectFile->file_path = $filePath;
    $projectFile->entreprise_id = Auth::user()->entreprise_id;
    $projectFile->save();

    return redirect()->back()->with('success', 'File uploaded successfully!');
}

//download files
public function downloadfiles($file_name)
{
    $entreprise = Entreprise::where('id', Auth::user()->entreprise_id)->first();
    $file = ProjectFile::where('file_name', $file_name)->where('entreprise_id', Auth::user()->entreprise_id)->first();
    $filePath = 'project_files/'. $entreprise->id . '/' . $file->project_id . '/' . $file->file_name;
    return Storage::disk('do_spaces')->download($filePath);
}

//delete files
public function deletefiles($file_name)
{
    $entreprise = Entreprise::where('id', Auth::user()->entreprise_id)->first();
    $file = ProjectFile::where('file_name', $file_name)->where('entreprise_id', Auth::user()->entreprise_id)->first();
    $filePath = 'project_files/'. $entreprise->id . '/' . $file->project_id . '/' . $file->file_name;
    Storage::disk('do_spaces')->delete($filePath);
    $file->delete();
    return redirect()->back()->with('success', 'File deleted successfully!');
}

public function generateDetailedInvoice()
{
    $projects = Project::where('entreprise_id', Auth::user()->entreprise_id)
        ->where(function($query) {
            $query->whereNull('AcceptedDevisProject')
                ->orWhere('AcceptedDevisProject', '=', 1);
        })
        ->where('archived', 0)
        ->orderBy('client_id')  // This will group projects by client
        ->orderBy('name')       // This will sort projects by name within each client group
        ->get();
    return view('admin.invoices.detailedinvoice', compact('projects'));
}

public function detailedInvoice(Request $request)
{
    try {
        // Validate the request
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'details' => 'required|array',
            'details.*.description' => 'required|string',
            'details.*.quantity' => 'required|numeric|min:0',
            'details.*.unit_price' => 'required|numeric|min:0',
        ]);

        // Get project and related information
        $projectinfos = Project::findOrFail($request->project_id);
        $client = Client::findOrFail($projectinfos->client_id);
        $user_enterprise = Auth::user()->entreprise_id;
        $entreprise = Entreprise::where('id', $user_enterprise)->first();

        if (!$entreprise) {
            throw new \Exception('Enterprise not found');
        }

        // Get latest invoice number for preview
        $latestInvoice = invoices_history::latest()
            ->where('entreprise_id', $user_enterprise)
            ->first();
        
        $currentYear = date('Y');
        $lastInvoiceYear = $latestInvoice ? date('Y', strtotime($latestInvoice->created_at)) : null;
        
        if ($lastInvoiceYear !== $currentYear) {
            $number = 0;
        } else {
            $number = $latestInvoice ? $latestInvoice->number : 0;
        }

        $invoice_id = sprintf('%03d', $number + 1);

        // Calculate totals
        $total_htva = 0;
        $details = [];

        foreach ($request->details as $detail) {
            $lineTotal = $detail['quantity'] * $detail['unit_price'];
            $total_htva += $lineTotal;

            $details[] = [
                'description' => $detail['description'],
                'quantity' => $detail['quantity'],
                'unit_price' => $detail['unit_price'],
                'total' => $lineTotal
            ];
        }

        // Calculate TVA and total TTC
        $tva_amount = $total_htva * ($projectinfos->tva / 100);
        $total_ttc = $total_htva + $tva_amount;

        // Set up dates for preview
        $invoice_date = new DateTime();
        $due_date = clone $invoice_date;
        $days = (int) $client->due_date;
        $due_date_interval = new DateInterval('P' . $days . 'D');

        // Return view with preview data
        return view('admin.invoices.detailedinvoiceview', [
            'projectinfos' => $projectinfos,
            'client' => $client,
            'entreprise' => $entreprise,
            'number' => $invoice_id,
            'details' => $details,
            'total_htva' => $total_htva,
            'tva_amount' => $tva_amount,
            'total_ttc' => $total_ttc,
            'comment' => $request->comment,
            'invoice_date' => $invoice_date->format('d-m-Y'),
            'due_date' => $due_date->format('d-m-Y')
        ]);

    } catch (\Exception $e) {
        \Log::error('Detailed Invoice Error: ' . $e->getMessage());
        \Log::error($e->getTraceAsString());
        
        return response()->json([
            'error' => true,
            'message' => 'Error generating invoice preview: ' . $e->getMessage()
        ], 500);
    }
}

public function confirmDetailedInvoice($projectID, Request $request)
{
    try {
        // Validate the request
        $request->validate([
            'details' => 'required|string', // JSON string of details
            'comment' => 'nullable|string'
        ]);

        $details = json_decode($request->details, true);
        
        // Get project and related information
        $projectinfos = Project::findOrFail($projectID);
        $client = Client::findOrFail($projectinfos->client_id);
        $user_enterprise = Auth::user()->entreprise_id;
        $entreprise = Entreprise::where('id', $user_enterprise)->first();

        // Get latest invoice number
        $latestInvoice = invoices_history::latest()
            ->where('entreprise_id', $user_enterprise)
            ->first();
        
        $currentYear = date('Y');
        $lastInvoiceYear = $latestInvoice ? date('Y', strtotime($latestInvoice->created_at)) : null;
        
        if ($lastInvoiceYear !== $currentYear) {
            $number = 0;
        } else {
            $number = $latestInvoice ? $latestInvoice->number : 0;
        }

        $invoice_id = sprintf('%03d', $number + 1);

        // Calculate totals
        $total_htva = 0;
        foreach ($details as $detail) {
            $total_htva += $detail['quantity'] * $detail['unit_price'];
        }

        // Calculate TVA and total TTC
        $tva_amount = $total_htva * ($projectinfos->tva / 100);
        $total_ttc = $total_htva + $tva_amount;
        
        // Create new invoice record
        $invoice = new invoices_history();
        $invoice->project_id = $projectinfos->id;
        $invoice->project_name = $projectinfos->name;
        $invoice->client_id = $client->id;
        //$invoice->client_name = $client->name;
        $invoice->entreprise_id = $user_enterprise;
        $invoice->start_date = $projectinfos->start_date;
        $invoice->end_date = $projectinfos->end_date;
        $invoice->number = $invoice_id;
        $invoice->type = 'Detailed';
        $invoice->link = '/admin/projects/invoice/' . $projectinfos->id;
        $invoice->total_htva = $total_htva;
        $invoice->total_tva = $tva_amount;
        $invoice->total_ttc = $total_ttc;
        $invoice->details = $request->details; // Store the JSON string
        $invoice->comment = $request->comment;
        $invoice->sentReminder = 0;

        // Set invoice dates
        $invoice_date = new DateTime();
        $today = new DateTime();
        $days = (int) $client->due_date;
        $due_date_interval = new DateInterval('P' . $days . 'D');
        $invoice->due_date = $today->add($due_date_interval);
        
        
        $invoice->save();

        // Update project's billed amount
        $projectinfos->amount_billed += $total_htva;
        $projectinfos->save();

        // Generate PDF
        $pdf = PDF::loadView('admin.invoices.detailedinvoicePDF', [
            'projectinfos' => $projectinfos,
            'client' => $client,
            'entreprise' => $entreprise,
            'number' => $invoice_id,
            'details' => $details,
            'total_htva' => $total_htva,
            'tva_amount' => $tva_amount,
            'total_ttc' => $total_ttc,
            'comment' => $request->comment,
            'invoice_date' => $invoice_date->format('d-m-Y'),
            'due_date' => $invoice->due_date->format('d-m-Y'),
            'invoice_id' => $invoice->number
        ]);

        // Save the PDF
        $filePath = 'invoices/' . $entreprise->id . '/' . date('Y') . '/' . $entreprise->invoice_prefix . '-' . $invoice->number . '.pdf';
        Storage::disk('do_spaces')->put($filePath, $pdf->output(), 'public');

        if(Auth::user()->hasRole('Admin')){
            return redirect()->route('admin.invoices.invoicehistory')
                ->with('success', 'Invoice created successfully');
        }
        if(Auth::user()->hasRole('Responsable de projet')){
            return redirect()->route('Responsable_de_projet.invoices.invoicehistory')
                ->with('success', 'Invoice created successfully');
        }
        if(Auth::user()->hasRole('Responsable de projet et gestionnaire')){
            return redirect()->route('Responsable_gestionnaire.invoices.invoicehistory')
                ->with('success', 'Invoice created successfully');
        }

    } catch (\Exception $e) {
        \Log::error('Detailed Invoice Confirmation Error: ' . $e->getMessage());
        \Log::error($e->getTraceAsString());
        
        return redirect()->back()
            ->with('error', 'Error confirming invoice: ' . $e->getMessage());
    }
}

public function editDetailedInvoice($id)
{
    $invoice = invoices_history::findOrFail($id);
    $project = Project::findOrFail($invoice->project_id);
    
    return view('admin.invoices.editDetailedInvoice', compact('invoice', 'project'));
}

public function updateDetailedInvoice(Request $request, $id)
{
    $invoice = invoices_history::findOrFail($id);
    $project = Project::findOrFail($request->project_id);
    
    // Validate the request
    $request->validate([
        'project_id' => 'required|exists:projects,id',
        'details' => 'required|array',
        'details.*.description' => 'required|string',
        'details.*.quantity' => 'required|numeric|min:0',
        'details.*.unit_price' => 'required|numeric|min:0',
        'comment' => 'nullable|string',
    ]);

    DB::beginTransaction();
    try {
        // First revert the old amount from the project
        $project->amount_billed -= $invoice->total_htva;
        $project->save();

        // Calculate new totals
        $total_htva = 0;
        $details = [];
        
        foreach ($request->details as $detail) {
            $lineTotal = $detail['quantity'] * $detail['unit_price'];
            $total_htva += $lineTotal;
            
            $details[] = [
                'description' => $detail['description'],
                'quantity' => $detail['quantity'],
                'unit_price' => $detail['unit_price'],
                'total' => $lineTotal
            ];
        }

        // Update invoice details
        $invoice->total_htva = $total_htva;
        $invoice->total_tva = $total_htva * ($project->tva/100);
        $invoice->total_ttc = $total_htva + ($total_htva * ($project->tva/100));
        $invoice->details = json_encode($details);
        $invoice->comment = $request->comment;
        
        $invoice->save();

        // Update project's billed amount
        $project->amount_billed += $total_htva;
        $project->save();

        // Generate new PDF
        $client = Client::findOrFail($project->client_id);
        $entreprise = Entreprise::findOrFail(Auth::user()->entreprise_id);

        // Parse the dates correctly
        $invoice_date = Carbon::parse($invoice->created_at)->format('d-m-Y');
        $due_date = Carbon::parse($invoice->due_date)->format('d-m-Y');

        $pdf = PDF::loadView('admin.invoices.detailedinvoicePDF', [
            'projectinfos' => $project,
            'client' => $client,
            'entreprise' => $entreprise,
            'number' => $invoice->number,
            'details' => $details,
            'total_htva' => $total_htva,
            'tva_amount' => $invoice->total_tva,
            'total_ttc' => $invoice->total_ttc,
            'comment' => $request->comment,
            'invoice_date' => $invoice_date,
            'due_date' => $due_date,
            'invoice_id' => $invoice->number
        ]);

        $filePath = 'invoices/' . $entreprise->id . '/' . date('Y') . '/' . $entreprise->invoice_prefix . '-' . $invoice->number . '.pdf';
        Storage::disk('do_spaces')->put($filePath, $pdf->output(), 'public');

        DB::commit();
        if(Auth::user()->hasRole('Admin')){
            return redirect()->route('admin.invoices.invoicehistory')
                ->with('success', 'Invoice updated successfully');
        }
        if(Auth::user()->hasRole('Responsable de projet')){
            return redirect()->route('Responsable_de_projet.invoices.invoicehistory')
                ->with('success', 'Invoice updated successfully');
        }
        if(Auth::user()->hasRole('Responsable de projet et gestionnaire')){
            return redirect()->route('Responsable_gestionnaire.invoices.invoicehistory')
                ->with('success', 'Invoice updated successfully');
        }

    } catch (\Exception $e) {
        DB::rollback();
        return back()->with('error', 'Error updating invoice: ' . $e->getMessage());
    }
}

public function getDevisByProject($projectId)
{
    try {
        $devis = devis_history::where('project_id', $projectId)
            ->where('acceptanceStatus', 1)
            ->where('entreprise_id', Auth::user()->entreprise_id)
            ->select('id', 'devis_number as number') // Make sure we're selecting the correct column name
            ->get();

        $entreprise = Entreprise::findOrFail(Auth::user()->entreprise_id);
        $devis_prefix = $entreprise->devis_prefix;
            
        return response()->json(['devis' => $devis, 'prefix' => $devis_prefix]);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

public function getAllDevis()
{
    try {
        $entrepriseId = Auth::user()->entreprise_id;
        $devis = devis_history::where('entreprise_id', $entrepriseId)
            ->where('acceptanceStatus', 1)
            ->select('id', 'devis_number as number', 'project_id', 'budget')
            ->orderBy('created_at', 'desc')
            ->get();

        // Map project ids to names
        $projectNames = \App\Models\Project::whereIn('id', $devis->pluck('project_id')->filter()->unique())
            ->pluck('name', 'id');

        // Calculate total billed per devis
        $billedPerDevis = \App\Models\invoices_history::where('entreprise_id', $entrepriseId)
            ->whereIn('devis_id', $devis->pluck('id'))
            ->selectRaw('devis_id, COALESCE(SUM(total_htva), 0) as total')
            ->groupBy('devis_id')
            ->pluck('total', 'devis_id');

        // Enrich devis items
        $devis = $devis->map(function ($d) use ($projectNames, $billedPerDevis) {
            $projectName = $d->project_id ? ($projectNames[$d->project_id] ?? null) : ($d->Project_name ?? null);
            $billed = (float) ($billedPerDevis[$d->id] ?? 0);
            $budget = (float) ($d->budget ?? 0);
            $unbilled = max($budget - $billed, 0);
            return [
                'id' => $d->id,
                'number' => $d->number,
                'project_id' => $d->project_id,
                'project_name' => $projectName,
                'unbilled' => round($unbilled, 2),
            ];
        });

        $entreprise = Entreprise::findOrFail($entrepriseId);
        $devis_prefix = $entreprise->devis_prefix;

        return response()->json(['devis' => $devis, 'prefix' => $devis_prefix]);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

public function getDevisComment($devisId)
{
    $devis = devis_history::find($devisId);
    return response()->json(['comment' => $devis->description]);
}

public function createDetailedCreditNote($invoiceID, Request $request)
{
    try {
        $invoice = invoices_history::findOrFail($invoiceID);
        
        // Check if invoice is of type 'Detailed'
        if ($invoice->type != 'Detailed') {
            return redirect()->back()->with('error', 'This is not a detailed invoice.');
        }
        
        $projectinfos = Project::findOrFail($invoice->project_id);
        $client = Client::findOrFail($invoice->client_id);
        $entreprise = Entreprise::findOrFail(Auth::user()->entreprise_id);
        
        // Get the original details from the invoice
        $details = json_decode($invoice->details, true);
        
        // Get the last credit note number
        $last_credit_note_number = credit_note::where('entreprise_id', $invoice->entreprise_id)->max('credit_note_number');
        if (!$last_credit_note_number) {
            $last_credit_note_number = 0;
        }
        
        // Determine if it's a partial or full credit note based on URL parameters
        $type = $request->query('type', 'full');
        $amount = $request->query('amount', null);
        $partial = ($type === 'partial') ? 1 : 0;
        
        \Log::info('Credit note parameters: type=' . $type . ', amount=' . $amount . ', partial=' . $partial);
        
        // For partial credit notes, we need to calculate the proportion
        if ($partial && $amount) {
            // Calculate the proportion of the credit note
            $proportion = $amount / $invoice->total_htva;
            
            // Adjust the details based on the proportion
            foreach ($details as &$detail) {
                $detail['total'] = $detail['total'] * $proportion;
                $detail['quantity'] = $detail['quantity'] * $proportion;
            }
            
            $total_htva = $amount;
            $original_total = $invoice->total_htva;
        } else {
            // Full credit note
            $total_htva = $invoice->total_htva;
            $original_total = null;
        }
        
        // Calculate TVA and total TTC
        $tva_amount = $total_htva * ($projectinfos->tva / 100);
        $total_ttc = $total_htva + $tva_amount;
        
        // Return the credit note view
        return view('admin.creditNote.detailedinvoicecreditNote', [
            'projectinfos' => $projectinfos,
            'client' => $client,
            'entreprise' => $entreprise,
            'invoice_id' => $invoice->id,
            'invoice_number' => $invoice->number,
            'details' => $details,
            'total_htva' => $total_htva,
            'tva_amount' => $tva_amount,
            'total_ttc' => $total_ttc,
            'comment' => $invoice->comment,
            'invoice_date' => $invoice->created_at->format('d-m-Y'),
            'due_date' => Carbon::parse($invoice->due_date)->format('d-m-Y'),
            'last_credit_note_number' => $last_credit_note_number,
            'partial' => $partial,
            'original_total' => $original_total
        ]);
    } catch (\Exception $e) {
        \Log::error('Detailed Credit Note Error: ' . $e->getMessage());
        \Log::error($e->getTraceAsString());
        
        return redirect()->back()->with('error', 'Error creating credit note: ' . $e->getMessage());
    }
}

public function confirmDetailedCreditNote($invoiceID, Request $request)
{
    try {
        // Validate the request
        $request->validate([
            'details' => 'required|string', // JSON string of details
            'comment' => 'nullable|string',
            'partial' => 'required',
            'total_htva' => 'required|numeric',
            'total_tva' => 'required|numeric',
            'total_ttc' => 'required|numeric'
        ]);
        
        $invoice = invoices_history::findOrFail($invoiceID);
        $projectinfos = Project::findOrFail($invoice->project_id);
        $client = Client::findOrFail($invoice->client_id);
        $entreprise = Entreprise::findOrFail(Auth::user()->entreprise_id);
        
        // Get the last credit note number
        $last_credit_note_number = credit_note::where('entreprise_id', $invoice->entreprise_id)->max('credit_note_number');
        if (!$last_credit_note_number) {
            $last_credit_note_number = 0;
        }
        
        // Convert partial to boolean or integer as needed
        $partial = filter_var($request->partial, FILTER_VALIDATE_BOOLEAN);
        
        \Log::info('Confirming credit note: partial=' . ($partial ? 'true' : 'false') . ', total_htva=' . $request->total_htva);
        
        // Create a new credit note
        $credit_note = new credit_note();
        $credit_note->project_name = $invoice->project_name;
        $credit_note->number = $invoice->number;
        $credit_note->link = $invoice->link;
        $credit_note->client_id = $invoice->client_id;
        $credit_note->project_id = $invoice->project_id;
        $credit_note->due_date = $invoice->due_date;
        $credit_note->end_date = $invoice->end_date;
        $credit_note->start_date = $invoice->start_date;
        $credit_note->type = $invoice->type;
        $credit_note->Payment_status = $invoice->Payment_status;
        $credit_note->sentReminder = 0;
        $credit_note->entreprise_id = $invoice->entreprise_id;
        $credit_note->total_htva = $request->total_htva;
        $credit_note->total_tva = $request->total_tva;
        $credit_note->total_ttc = $request->total_ttc;
        $credit_note->credit_note_number = str_pad($last_credit_note_number + 1, 3, '0', STR_PAD_LEFT);
        $credit_note->credit_note_type = $partial ? 'Partial' : 'Full';
        $credit_note->invoice_id = $invoice->id;
        $credit_note->save();
        
        // Update the invoice to indicate it has a credit note
        $invoice->hasCreditNote = 1;
        $invoice->Payment_status = -1;
        $invoice->archived = 1;
        $invoice->save();
        
        // Parse the details
        $details = json_decode($request->details, true);
        
        // Generate PDF
        $pdf = PDF::loadView('admin.creditNote.detailedinvoicePDFcreditNote', [
            'projectinfos' => $projectinfos,
            'client' => $client,
            'entreprise' => $entreprise,
            'invoice_number' => $invoice->number,
            'details' => $details,
            'total_htva' => $request->total_htva,
            'tva_amount' => $request->total_tva,
            'total_ttc' => $request->total_ttc,
            'comment' => $request->comment,
            'invoice_date' => $invoice->created_at->format('d-m-Y'),
            'credit_note_number' => $credit_note->credit_note_number,
            'partial' => $partial,
            'original_total' => $partial ? $invoice->total_htva : null
        ]);
        
        // Create year folder if it doesn't exist
        $yearFolder = date('Y');
        $basePath = 'creditNote/' . $entreprise->id . '/' . $yearFolder;
        
        // Check if the year folder exists, if not create it
        if (!Storage::disk('do_spaces')->exists($basePath)) {
            Storage::disk('do_spaces')->makeDirectory($basePath, 0775, true);
        }
        
        // Format the filename to match the existing system
        $yearMonth = date('ym');
        $fileName = 'CN-' . $yearMonth . $credit_note->credit_note_number . '.pdf';
        $filePath = $basePath . '/' . $fileName;
        
        // Save the file
        Storage::disk('do_spaces')->put($filePath, $pdf->output(), 'public');


        
        // Log the successful creation
        \Log::info('Credit note PDF created: ' . $filePath);
        
        if(Auth::user()->hasRole('Admin')){
            return redirect()->route('admin.creditNote.creditNoteHistory')
                ->with('success', 'Credit note created successfully');
        }
        if(Auth::user()->hasRole('Responsable de projet')){
            return redirect()->route('Responsable_de_projet.creditNote.creditNoteHistory')
                ->with('success', 'Credit note created successfully');
        }
        if(Auth::user()->hasRole('Employee')){
            return redirect()->route('employee.creditNote.creditNoteHistory')
                ->with('success', 'Credit note created successfully');
        }
        if(Auth::user()->hasRole('Responsable de projet et gestionnaire')){
            return redirect()->route('Responsable_gestionnaire.creditNote.creditNoteHistory')
                ->with('success', 'Credit note created successfully');
        }
        
    } catch (\Exception $e) {
        \Log::error('Detailed Credit Note Confirmation Error: ' . $e->getMessage());
        \Log::error($e->getTraceAsString());
        
        return redirect()->back()->with('error', 'Error confirming credit note: ' . $e->getMessage());
    }
}

public function sendCreditNote(Request $request, $credit_note_id)
{
    try {
        $credit_note = credit_note::findOrFail($credit_note_id);
        $client = Client::findOrFail($credit_note->client_id);
        $project = Project::findOrFail($credit_note->project_id);
        $entreprise = Entreprise::findOrFail(Auth::user()->entreprise_id);
        $project_contacts = client_contacts_projects::where('project_id', $project->id)->get();

        $subject = "Credit Note NC-" . date('ym') . $credit_note->credit_note_number;
        $body = "Please find attached the credit note for " . $project->name;

        $filePaths = [];
        
        if ($request->hasFile('file')) {
            foreach ($request->file('file') as $file) {
                $fileName = $file->getClientOriginalName();
                $filePath = 'credit_notes/' . $entreprise->id . '/' . date('Y') . '/' . $fileName;
                Storage::disk('do_spaces')->putFileAs(
                    'credit_notes/' . $entreprise->id . '/' . date('Y'),
                    $file,
                    $fileName,
                    'public'
                );
                $filePaths[] = $filePath;
            }
        }
        
        // Send to client's email if available
        $recipient = $client->email;
        $failedRecipients = [];
        
        if ($recipient) {
            try {
                Mail::to($recipient)->send(new CreditNoteEmail(
                    $subject, 
                    $body, 
                    $credit_note_id, 
                    $filePaths,
                    [
                        'client' => $client,
                        'credit_note' => $credit_note,
                        'entreprise' => $entreprise
                    ]
                ));
            } catch (\Exception $e) {
                $failedRecipients[] = $recipient;
                \Log::error('Failed to send credit note to client email', [
                    'error' => $e->getMessage(),
                    'recipient' => $recipient
                ]);
            }
        }
        
        // Send to project contacts
        foreach ($project_contacts as $contact) {
            $contact_email = client_contacts::where('id', $contact->contact_id)->value('email');
            if ($contact_email) {
                try {
                    Mail::to($contact_email)->send(new CreditNoteEmail(
                        $subject, 
                        $body, 
                        $credit_note_id, 
                        $filePaths,
                        [
                            'client' => $client,
                            'credit_note' => $credit_note,
                            'entreprise' => $entreprise
                        ]
                    ));
                } catch (\Exception $e) {
                    $failedRecipients[] = $contact_email;
                    \Log::error('Failed to send credit note to contact email', [
                        'error' => $e->getMessage(),
                        'recipient' => $contact_email
                    ]);
                }
            }
        }

        if (count($failedRecipients) > 0) {
            return redirect()->back()->with('error', 'Failed to send credit note to some recipients: ' . implode(', ', $failedRecipients));
        }

        $credit_note->emailsent = 1;
        $credit_note->save();

        if(Auth::user()->hasRole('Admin')){
            return redirect()->route('admin.creditNote.creditNoteHistory')->with('message', 'Credit Note Sent successfully!');
        }
        if(Auth::user()->hasRole('Responsable de projet')){
            return redirect()->route('Responsable_de_projet.creditNote.creditNoteHistory')->with('message', 'Credit Note Sent successfully!');
        }
        if(Auth::user()->hasRole('Responsable de projet et gestionnaire')){
            return redirect()->route('Responsable_gestionnaire.creditNote.creditNoteHistory')->with('message', 'Credit Note Sent successfully!');
        }
    } catch (\Exception $e) {
        \Log::error('Credit note sending failed', [
            'error' => $e->getMessage()
        ]);
        return redirect()->back()->with('error', 'Failed to send credit note: ' . $e->getMessage());
    }
}

public function getRecipients(invoices_history $invoice)
{
    // Get the client associated with the invoice
    $client = Client::findOrFail($invoice->client_id);
    
    // Get the project associated with the invoice
    $project = Project::findOrFail($invoice->project_id);
    
    // Get recipients
    $recipients = [];
    if ($client->email) {
        $recipients[] = [
            'name' => $client->name,
            'email' => $client->email,
            'is_comptable' => 0 // Client is not a comptable
        ];
    }

    $project_contacts = client_contacts_projects::where('client_contacts_projects.project_id', $project->id)
        ->join('client_contacts', 'client_contacts.id', '=', 'client_contacts_projects.contact_id')
        ->whereNotNull('client_contacts.email')
        ->select('client_contacts.name', 'client_contacts.email', 'client_contacts.is_comptable')
        ->get();

    foreach ($project_contacts as $contact) {
        $recipients[] = [
            'name' => $contact->name,
            'email' => $contact->email,
            'is_comptable' => $contact->is_comptable
        ];
    }
    
    return response()->json(['recipients' => $recipients]);
}

    public function getDevisContacts($devisId)
    {
        try {
            $devis = devis_history::where('id', $devisId)
                ->where('entreprise_id', Auth::user()->entreprise_id)
                ->first();

            if (!$devis) {
                return response()->json([
                    'success' => false,
                    'message' => 'Devis not found'
                ]);
            }

            // Get all contacts for the client
            $contacts = client_contacts::where('client_id', $devis->client_id)
                ->where('entreprise_id', Auth::user()->entreprise_id)
                ->select('id', 'name', 'email')
                ->get();

            return response()->json([
                'success' => true,
                'contacts' => $contacts
            ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching devis contacts:', [
                'devis_id' => $devisId,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error fetching contacts: ' . $e->getMessage()
            ]);
        }
    }
}