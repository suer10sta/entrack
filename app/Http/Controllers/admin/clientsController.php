<?php

namespace App\Http\Controllers\admin;

use App\Models\client_contacts;
use App\Models\Tasks;
use App\Models\Teams;
use App\Models\Client;
use App\Models\Project;
use App\Models\Employees;
use App\Models\Entreprise;
use App\Models\invoices_history;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class clientsController extends Controller
{
    public function index()
    {
        $projects = Project::where('entreprise_id', auth()->user()->entreprise_id)->where(function($query) {
            $query->whereNull('AcceptedDevisProject')
                  ->orWhere('AcceptedDevisProject', '=', 1);
        })
        ->where('archived', 0)
        ->get();

        $teams = Teams::where('entreprise_id', Auth::user()->entreprise_id)->get();
        $tasks= Tasks::where('user_id', Auth::user()->id)->get(); 

        // Get the authenticated user's enterprise_id
        $enterpriseId = Auth::user()->entreprise_id;

        $employees = Employees::where('entreprise_id', $enterpriseId)->where('archived', 0)->orderBy('name', 'asc')->get();


        // Retrieve only clients with the same enterprise_id
        $clients = Client::where('entreprise_id', $enterpriseId)->where(function($query) {
            $query->whereNull('AcceptedDevisClient')
                  ->orWhere('AcceptedDevisClient', '=', 1);
        })
        ->orderBy('client_number', 'desc')
        ->get();

        return view('admin.clients.index', compact('clients', 'employees','tasks','teams','projects'));
    }

    public function show(Client $client)
    {
        $projects = Project::where('entreprise_id', auth()->user()->entreprise_id)
        ->where(function($query) {
            $query->whereNull('AcceptedDevisProject')
                  ->orWhere('AcceptedDevisProject', '=', 1);
        })
        ->where('archived', 0)
        ->where('client_id', $client->id)  
        ->get();

        $invoices = invoices_history::where('client_id', $client->id)->orderBy('created_at', 'desc')->get();
        $totalPaid = $invoices->where('Payment_status', 1)->sum('total_ttc');
        $totalUnpaid = $invoices->where('Payment_status', '!=', 1)->sum('total_ttc');
        $totalTVA = $invoices->sum('total_tva');
        $totalBudget = $invoices->sum('total_ttc');
        $entreprise_infos = Entreprise::where('id', Auth::user()->entreprise_id)->first();
        return view('admin.clients.view_client', compact('client', 'projects', 'invoices', 'entreprise_infos', 'totalPaid', 'totalUnpaid', 'totalTVA', 'totalBudget'));
    }


    public function create()
    {
        return view('admin.clients.create');
    }


    public function store(Request $request)
    {
        // Get the authenticated admin's enterprise_id
        $enterpriseId = Auth::user()->entreprise_id;
        $entreprise = Auth::user()->entreprise;

    // 2. VÉRIFIER LA LIMITE AVANT DE CONTINUER
    if (!$entreprise->canAddMoreUsers()) {
        
        // 3. Si la limite est atteinte, arrêter et renvoyer une erreur
        return back()->with('error', 'Vous avez atteint la limite de ' . $entreprise->getUserLimit() . ' utilisateurs pour votre plan. Veuillez passer au plan Pro pour en ajouter davantage.');
    }
        $validatedData = $request->validate([
            'client_number' => [
                'required',
                Rule::unique('clients')->where(function ($query) {
                    return $query->where('entreprise_id', Auth::user()->entreprise_id);
                }),
            ],
            
        ]);

        // Create the associated client
        $client = new Client([
            'name' => $request->name,
            'client_number' =>  $validatedData['client_number'],
            'TVA' => $request->TVA,
            'due_date' => $request->due_date,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'city' => $request->city,
            'zip' => $request->zip,
            'country' =>  $request->country,
            'entreprise_id' => $enterpriseId, 
        ]);

        // Check if all billing fields are not null, then set values from either default or alternative fields
        if($request->billing_street != null && $request->billing_city != null && $request->billing_country != null && $request->billing_zip != null){
                $client->billing_street = $request->billing_street;
                $client->billing_city = $request->billing_city;
                $client->billing_country = $request->billing_country;
                $client->billing_zip = $request->billing_zip;
        }else{
                $client->billing_street = $request->address;
                $client->billing_city = $request->city;
                $client->billing_country = $request->country;
                $client->billing_zip = $request->zip;
        }

        $client->save();

        $comptable=new client_contacts();
        $comptable->name = $request->comp_name;
        $comptable->functionality = "Comptable";
        $comptable->mobile_fix = $request->comp_phone;
        $comptable->email = $request->comp_email;
        $comptable->address = $request->comp_address;
        $comptable->client_id = $client->id;
        $comptable->is_comptable = 1;
        $comptable->entreprise_id = auth()->user()->entreprise_id;
        $comptable->save();


        $contact_names = $request->contact_name;
        $contact_functionalities = $request->contact_function;
        $contact_mobile_fix= $request->contact_mobile_fix;
        $contact_email= $request->contact_email;
        $contact_address= $request->contact_address;

        // Check if the first element of each array is null
        if((is_array($contact_names) && $contact_names[0] != null) && (is_array($contact_email) && $contact_email[0] != null) ) {
            for($i = 0; $i < count($contact_names); $i++) {
                // Create a new contact instance
                $Contact = new client_contacts();
                $Contact->name = $contact_names[$i];
                $Contact->functionality = $contact_functionalities[$i];
                $Contact->mobile_fix = $contact_mobile_fix[$i];
                $Contact->email = $contact_email[$i];
                $Contact->address = $contact_address[$i];
                $Contact->client_id = $client->id;
                $Contact->entreprise_id = auth()->user()->entreprise_id;
                $Contact->save();
            }
        }

        

        if(Auth::user()->hasRole('Responsable de projet')){
            return redirect()->route('Responsable_de_projet.clients.index')->with('message', 'client Added Successfully');
        }
        if(Auth::user()->hasRole('Responsable de projet et gestionnaire')){
            return redirect()->route('Responsable_gestionnaire.clients.index')->with('message', 'client Added Successfully');
        }
        if(Auth::user()->hasRole('Admin')){
            return redirect()->route('admin.clients.index')->with('message', 'client Added Successfully');
        }
    }

    public function edit(Client $client)
    {
        return view('admin.clients.edit', compact('client'));
    }

    public function update(Request $request, Client $client)
    {
        $validated = $request->validate([
            "name" => "required",
            "client_number" => "required",
            "TVA" => "required",
            "due_date" => "required",
            "email" => "required",
            "phone" => "required",
            "address" => "required",
            "city" => "required",
            "zip" => "required",
            "country" => "required",
            "Billing_address" => "required",
        ]);

        $client->update($validated);

        $contacts=client_contacts::where('client_id',$client->id)->get();
        $contact_names=$request->contact_name;
        $contact_functionalities=$request->contact_function;
        $contact_mobile_fix=$request->contact_mobile_fix;
        $contact_email=$request->contact_email;
        $contact_address=$request->contact_address;

        $i=0;
        foreach($contacts as $contact){
                if (!empty($contact_names[$i])) {
                    $contact->name = $contact_names[$i];
                }
                if (!empty($contact_functionalities[$i])) {
                    $contact->functionality = $contact_functionalities[$i];
                }
                if (!empty($contact_mobile_fix[$i])) {
                    $contact->mobile_fix = $contact_mobile_fix[$i];
                }
                if (!empty($contact_email[$i])) {
                    $contact->email = $contact_email[$i];
                }
                if (!empty($contact_address[$i])) {
                    $contact->address = $contact_address[$i];
                }
                $contact->save();
                $i++;
        }

        if(Auth::user()->hasRole('Responsable de projet')){
            return redirect()->route('Responsable_de_projet.clients.index')->with('message', 'client Updated Successfully');
        }
        if(Auth::user()->hasRole('Responsable de projet et gestionnaire')){
            return redirect()->route('Responsable_gestionnaire.clients.index')->with('message', 'client Updated Successfully');
        }
        if(Auth::user()->hasRole('Admin')){
            return redirect()->route('admin.clients.index')->with('message', 'client Updated Successfully');
        }
    }

    public function updateClient(Request $request)
    {
        $client = Client::find($request->id);
        $client->{$request->column} = $request->content;
        $client->save();

        return response()->json(['success'=>'Client updated successfully.']);
    }

    public function updateContact(Request $request)
    {
        $contact = client_contacts::find($request->id);
        $contact->{$request->column} = $request->content;
        $contact->save();

        return response()->json(['success'=>'contact updated successfully.']);
    }

    public function addContact(Request $request, Client $client)
    {
        $contact_names = $request->contact_name;
        $contact_functionalities = $request->contact_function;
        $contact_mobile_fix= $request->contact_mobile_fix;
        $contact_email= $request->contact_email;
        $contact_address= $request->contact_address;
        if(is_array($contact_names) && is_array($contact_functionalities) && count($contact_names) == count($contact_functionalities)) {
            for($i = 0; $i < count($contact_names); $i++) {
                // Create a new contact instance
                $Contact = new client_contacts();
                $Contact->name = $contact_names[$i];
                $Contact->functionality = $contact_functionalities[$i];
                $Contact->mobile_fix = $contact_mobile_fix[$i];
                $Contact->email = $contact_email[$i];
                $Contact->address = $contact_address[$i];
                $Contact->client_id = $client->id;
                $Contact->entreprise_id = auth()->user()->entreprise_id;
                $Contact->save();
            }
        } 
        return redirect()->route('admin.clients.show', $client->id)
        ->with('message', 'client Updated Successfully');
    }


    public function destroy(Client $client)
    {
        $client->delete();

        return back()->with('message', 'client Deleted Successfully');
    } 

    public function destroyContact($contact_id)
    {
        $contact = client_contacts::where('id', $contact_id)->first();
        $contact->delete();

        return back()->with('message', 'contact Deleted Successfully');
    }

    public function downloadClinetsRepports()
    {
        $clients = Client::where('entreprise_id', auth()->user()->entreprise_id)->where(function($query) {
            $query->whereNull('AcceptedDevisClient')
                  ->orWhere('AcceptedDevisClient', '=', 1);
        })
        ->orderBy('client_number', 'asc')
        ->get();
        $entreprise = Entreprise::where('id', auth()->user()->entreprise_id)->first();
        $pdf = PDF::loadView('admin.clients.downloadRepports', [
            'clients' => $clients,
            'entreprise' => $entreprise,
        ]); 
        $pdf->setPaper('a4', 'landscape'); // Set the paper to A4 and orientation to landscape
        return $pdf->stream('clientsRepports.pdf');
    }

    public function filterInvoices(Request $request, Client $client)
    {
        
        $projects = Project::where('entreprise_id', auth()->user()->entreprise_id)
        ->where(function($query) {
            $query->whereNull('AcceptedDevisProject')
                  ->orWhere('AcceptedDevisProject', '=', 1);
        })
        ->where('archived', 0)
        ->where('client_id', $client->id)  
        ->get();

        $startDate = $request->start_date;
        $endDate = $request->end_date;
        $clientId = $request->client_id;
        
        $client = Client::findOrFail($clientId);
        $entreprise_infos = Entreprise::where('id', Auth::user()->entreprise_id)->first();

        $invoices = invoices_history::where('client_id', $clientId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->get();

        $totalPaid = $invoices->where('Payment_status', 1)->sum('total_ttc');
        $totalUnpaid = $invoices->where('Payment_status', '!=', 1)->sum('total_ttc');
        $totalTVA = $invoices->sum('total_tva');
        $totalBudget = $invoices->sum('total_ttc');

        return view('admin.clients.view_client', compact(
            'client',
            'invoices',
            'entreprise_infos',
            'totalPaid',
            'totalUnpaid',
            'totalTVA',
            'totalBudget',
            'projects'
        ));
    }

    public function downloadInvoicesRepports(Request $request, Client $client)
{
    $query = invoices_history::where('entreprise_id', auth()->user()->entreprise_id)->where('client_id', $client->id)->orderBy('created_at', 'desc');

    // Check if start and end dates are provided
    if ($request->has(['startDate', 'endDate'])) {
        $startDate = $request->startDate;
        $endDate = $request->endDate;
        $query->whereDate('created_at', '>=', $startDate)
              ->whereDate('created_at', '<=', $endDate);
    }

    $invoices = $query->get();
    $entreprise = Entreprise::where('id', auth()->user()->entreprise_id)->first();

    $pdf = PDF::loadView('admin.clients.downloadInvoicesRepports', [
        'invoices' => $invoices,
        'entreprise' => $entreprise,
        'client' => $client,
    ]);

    return $pdf->stream('invoicesRepports.pdf');
}
}
