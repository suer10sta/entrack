<?php

namespace App\Http\Controllers\superadmin;

use App\Models\User;
use App\Models\Entreprise;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class IndexController extends Controller
{
    public function index()
    {
        // Eager load the entreprise relationship to avoid N+1 query problem
        $users = User::with('entreprise')
                ->where('is_employee', 0)
                ->get(); 
        $entreprises=Entreprise::all();

        foreach ($users as $user) {
            // Get role names for the current user in the loop
            $roles = $user->getRoleNames();
            $user->roles = $roles; // Add roles to the user object

            // Access the associated Entreprise name
            $entrepriseName = optional($user->entreprise)->name;
            $user->entreprise_name = $entrepriseName; // Add entreprise_name to the user object
        }

        return view('superadmin.index', compact('users','entreprises'));
    }
}
