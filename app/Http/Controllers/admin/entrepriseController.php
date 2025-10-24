<?php

namespace App\Http\Controllers\admin;

use Dotenv\Validator;
use App\Models\Entreprise;
use App\Models\smtpConfig;
use Illuminate\Http\Request;
use App\Models\entrepriseCharges;
use App\Models\billings_budget;
use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Illuminate\Support\Facades\File;
use setasign\Fpdi\Fpdi;
use Illuminate\Support\Facades\Storage;

class entrepriseController extends Controller
{
    public function index()
    {
        $entreprise = Entreprise::where('id', auth()->user()->entreprise_id)->first();
        $entrepriseCharges = entrepriseCharges::where('entreprise_id', auth()->user()->entreprise_id)->get();
        $smtp = smtpConfig::where('entreprise_id', auth()->user()->entreprise_id)->first();

        return view('admin.entreprise.index', compact('entreprise', 'entrepriseCharges','smtp'));
    }

    public function edit( $entreprise_id)
    {
        $entreprise = Entreprise::where('id', $entreprise_id)->first();
        $entrepriseCharges = entrepriseCharges::where('entreprise_id', $entreprise_id)->get();

        return view('admin.entreprise.edit', compact('entreprise', 'entrepriseCharges'));
    }

    public function updateBillings(Request $request)
    {
        
        $entrepriseCharge = entrepriseCharges::find($request->id);
        $entrepriseCharge->{$request->column} = $request->content;
        $entrepriseCharge->save();

        return response()->json(['success'=>'Entreprise updated successfully.']);
    }

    public function updateInfos(Request $request)
    {
        $Entreprise = Entreprise::find($request->id);
        $Entreprise->{$request->column} = $request->content;
        $Entreprise->save();

        return response()->json(['success'=>'Entreprise updated successfully.']);
    }

    public function updateSMTPInfos(Request $request)
    {
        $smtp = SmtpConfig::firstOrCreate(
            ['entreprise_id' => $request->id],
            ['mail_host' => '', 'mail_port' => '', 'mail_username' => '', 'mail_password' => '']
        );

        $smtp->{$request->column} = $request->content;
        $smtp->save();

        // Update .env file
        $envPath = base_path('.env');
        $envFile = file_get_contents($envPath);
        
        if($request->column == 'mail_host') {
            $envFile = preg_replace('/^MAIL_HOST=.*/m', 'MAIL_HOST=' . $smtp->mail_host, $envFile);
            file_put_contents($envPath, $envFile);
            \Artisan::call('config:cache');
        }
        if($request->column == 'mail_port') {
            $envFile = preg_replace('/^MAIL_PORT=.*/m', 'MAIL_PORT=' . $smtp->mail_port, $envFile);
            file_put_contents($envPath, $envFile);
            \Artisan::call('config:cache');
        }
        if($request->column == 'mail_username') {
            $envFile = preg_replace('/^MAIL_USERNAME=.*/m', 'MAIL_USERNAME=' . $smtp->mail_username, $envFile);
            file_put_contents($envPath, $envFile);
            \Artisan::call('config:cache');
        }
        if($request->column == 'mail_password') {
            $envFile = preg_replace('/^MAIL_PASSWORD=.*/m', 'MAIL_PASSWORD=' . $smtp->mail_password, $envFile);
            file_put_contents($envPath, $envFile);
            \Artisan::call('config:cache');
        }
        
        

        return response()->json(['success'=>'SMTP Config updated successfully.']);
    }

    public function create()
    {
        $billings_budget = billings_budget::where('entreprise_id', auth()->user()->entreprise_id)->orderBy('number', 'desc')->get();
        $latestBudget = billings_budget::where('entreprise_id', auth()->user()->entreprise_id)->where('sold_out', 0)->latest()->first();
        $entreprise = Entreprise::where('id', auth()->user()->entreprise_id)->first();

        return view('admin.entreprise.create', compact('billings_budget', 'entreprise', 'latestBudget'));
    }

    public function store(Request $request)
    {
        $names = $request->name;
        $prices = $request->price;
        $entreprise = Entreprise::where('id', auth()->user()->entreprise_id)->first();
        $entrepriseCharges = entrepriseCharges::where('entreprise_id', auth()->user()->entreprise_id)->get();
        $smtp = smtpConfig::where('entreprise_id', auth()->user()->entreprise_id)->first();
        $latestBudget = billings_budget::where('entreprise_id', auth()->user()->entreprise_id)->where('sold_out', 0)->latest()->first();


        if($latestBudget == null) {
            return redirect()->back()->with('error', 'Please add a budget first');
        }else{
            if(is_array($names) && is_array($prices) && count($names) == count($prices)) {
                for($i = 0; $i < count($names); $i++) {
                    // Check if name and price are set and price is numeric
                    if(!isset($names[$i]) || !isset($prices[$i]) || !is_numeric($prices[$i])) {
                        return redirect()->back()->with('error', 'Invalid name or price');
                    }
    
                    // Create a new entrepriseCharges instance
                    $entrepriseCharges = new entrepriseCharges();
                    $entrepriseCharges->charge_name = $names[$i];
                    $entrepriseCharges->price = $prices[$i];
                    $entrepriseCharges->entreprise_id = auth()->user()->entreprise_id;
                    $entrepriseCharges->Billings_budget_id = $latestBudget->id;
                    $entrepriseCharges->save();
                }
    
                return redirect()->back()->with('successNewDepense', 'Charges created successfully');
            } else {        
                return redirect()->back()->with('errorNewDepense', 'Mismatch between names and prices');
            }
        }
        
    }

    public function updateCharges(Request $request)
    {
        $entreprise = entrepriseCharges::where('entreprise_id', auth()->user()->entreprise_id)->first();
        $entreprise->update($request->all());

        return redirect()->back()->with('success', 'Charges updated successfully');
    }

    public function updateImage(Request $request)
    {
        $request->validate([
            'entreprise_image' => 'required',
        ]);
    
        $entreprise = Entreprise::where('id', auth()->user()->entreprise_id)->first();
    
        if ($request->hasFile('entreprise_image')) {
            $image = $request->file('entreprise_image');
            $imageName = time().'.'.$image->getClientOriginalExtension();
            $imagePath = 'entreprise_images/'.$entreprise->id.'/' . $imageName; // Keep the same folder structure

            // Store the image in Digital Ocean Spaces
            \Storage::disk('do_spaces')->put($imagePath, file_get_contents($image), 'public');
            
            // Assuming 'image_name' is the column in your database where you store the image name
            $entreprise->image = $imagePath;
            $entreprise->save();
        }
    
        return redirect()->back()->with('success', 'Image updated successfully');
    }

    public function destroy($id)
    {
        $entreprise = entrepriseCharges::where('id', $id)->first();
        $entreprise->delete();

        return redirect()->back()->with('success', 'Charges deleted successfully');
    }

    public function validateBilling($chargeid, $budget_id)
{
    $entreprise = Entreprise::where('id', auth()->user()->entreprise_id)->first();
    $charge = entrepriseCharges::where('id', $chargeid)
                                ->where('Billings_budget_id', $budget_id)
                                ->first();

   $budget = billings_budget::where('id', $budget_id)->first();

    if($budget->budget_remaining < $charge->price) {
        return redirect()->back()->with([
            'error' => 'Budget remaining is not enough to validate this charge',
        ]);
    }else{
        $charge->validated = 1;
        $charge->save();

        $budget->budget_remaining = $budget->budget_remaining - $charge->price;
        $budget->budget_spent = $budget->budget_spent + $charge->price;
        $budget->save();

        return redirect()->back()->with([
            'success' => 'Dépense validated successfully',
         
        ]);
    }

}

public function unvalidateBilling($chargeid, $budget_id)
{
    $entreprise = Entreprise::where('id', auth()->user()->entreprise_id)->first();
    $charge = entrepriseCharges::where('id', $chargeid)
                                ->where('Billings_budget_id', $budget_id)
                                ->first();

   $budget = billings_budget::where('id', $budget_id)->first();

    $charge->validated = 0;
    $charge->save();

    $budget->budget_remaining = $budget->budget_remaining + $charge->price;
    $budget->budget_spent = $budget->budget_spent - $charge->price;
    $budget->save();
   

    return redirect()->back()->with([
        'success' => 'Dépense unvalidated successfully',
       
    ]);
}

    public function addBudget(Request $request)
    {
        $latestBudget = billings_budget::where('entreprise_id', auth()->user()->entreprise_id)->latest()->first();
        $newBudget  = new billings_budget();

        if($latestBudget) {
            $latestBudget->sold_out = 1;
            $latestBudget->save();
            $newBudget->number = $latestBudget->number + 1;
            if($latestBudget->budget_remaining == 0) {
                $newBudget->budget_remaining = $request->newBudget;
                $newBudget->budget = $request->newBudget;
            } else {
                $newBudget->budget = $latestBudget->budget_remaining + $request->newBudget;
                $newBudget->budget_remaining = $latestBudget->budget_remaining + $request->newBudget;
            }
        } else {
            $newBudget->number = 1;
            $newBudget->budget = $request->newBudget;
            $newBudget->budget_remaining = $request->newBudget;
        }

        $newBudget->entreprise_id = auth()->user()->entreprise_id;
        $newBudget->save();

        

        return redirect()->back()->with('successNewBudget', 'Budget added successfully');
    }

    public function downloadBillingsRepports(Request $request)
    {
        $billings_budget = billings_budget::where('entreprise_id', auth()->user()->entreprise_id)->orderBy('number', 'desc');

        // Check if start and end dates are provided
        if ($request->has(['startDate', 'endDate'])) {
            $startDate = $request->startDate;
            $endDate = $request->endDate;
            $billings_budget->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate);
        }

        $billings = $billings_budget->get();
        $entreprise = Entreprise::where('id', auth()->user()->entreprise_id)->first();

        $pdf = PDF::loadView('admin.entreprise.downloadBillingsRepports', [
            'billings' => $billings,
            'entreprise' => $entreprise,
            'startDate' => $request->startDate,
            'endDate' => $request->endDate,
        ]);

        return $pdf->stream('billingsRepports.pdf');
    }

    public function destroyBillings($id)
    {
        $billing = billings_budget::where('id', $id)->first();

        $entreprise_charge = entrepriseCharges::where('Billings_budget_id', $id)->get();
        foreach($entreprise_charge as $charge) {
            $charge->delete();
        }

        $billing->delete();

        return redirect()->back()->with('success', 'Billing deleted successfully');
    }

    public function updateCharge(Request $request, $id)
{
    $request->validate([
        'budget' => 'required',
    ]);

    $billings_budget = billings_budget::where('id', $id)->first();
    $entreprise_charge = entrepriseCharges::where('Billings_budget_id', $id)->get();

    if ($entreprise_charge->isNotEmpty()) {
        return response()->json(['error' => 'Please delete all charges before updating the budget']);
    }

    $billings_budget->budget = $request->input('budget');
    $billings_budget->budget_remaining = $request->input('budget');
    $billings_budget->save();

    return response()->json(['success' => true]);
}
    
}
