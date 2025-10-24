<?php

namespace App\Http\Controllers\superadmin;

use App\Models\Entreprise;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class EntrepriseController extends Controller
{
   
    public function index()
    {
        $entreprises=Entreprise::all();
        $users = User::with('entreprise')
                ->where('is_employee', 0)
                ->get(); 

        return view('superadmin.entreprise.index', compact("entreprises", "users"));
    }
    public function disactivate(Entreprise $entreprise)
    {
        $entreprise->is_activated = false;
        $entreprise->save();

        return redirect()->back()->with('message', 'Entreprise deactivated successfully');
    }

    public function activate(Entreprise $entreprise)
    {
        $entreprise->is_activated = true;
        $entreprise->save();

        return redirect()->back()->with('message', 'Entreprise activated successfully');
    }
}
