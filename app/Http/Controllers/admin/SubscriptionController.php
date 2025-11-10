<?php

namespace App\Http\Controllers\admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class SubscriptionController extends Controller
{
    //
    public function choosePlan(Request $request, $planName)
    {
        // 1. Valider que le nom du plan est correct
        $validPlans = ['solo', 'premium', 'pro'];
        if (!in_array($planName, $validPlans)) {
            // Si quelqu'un essaie une URL inventée, on le renvoie en arrière
            return redirect()->route('profile.upgrade')->with('error', 'Plan invalide.');
        }

        // 2. Obtenir l'entreprise de l'utilisateur connecté
        $entreprise = Auth::user()->entreprise;

        // 3. Mettre à jour la base de données
        $entreprise->type_compt = $planName;      // Définit le plan (ex: "premium")
        $entreprise->trial_ends_at = null; // L'essai est officiellement terminé
        $entreprise->save();

        // 4. Rediriger vers le tableau de bord avec un message de succès
        //    (changez 'dashboard' si votre route principale a un autre nom)
        return redirect()->route('dashboard')
                         ->with('success', 'Vous êtes maintenant abonné au plan ' . ucfirst($planName) . ' !');
    }
}
