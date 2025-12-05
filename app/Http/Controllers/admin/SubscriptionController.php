<?php

namespace App\Http\Controllers\admin; // Make sure namespace matches your folder structure

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class SubscriptionController extends Controller
{
    public function choosePlan(Request $request, $planName)
{
    $validPlans = ['solo', 'premium', 'pro'];
    if (!in_array($planName, $validPlans)) {
        return redirect()->back()->with('alertmessage', 'Plan invalide.');
    }

    $entreprise = Auth::user()->entreprise; 

    if ($entreprise->subscribed('default')) {
         return redirect()->route('dashboard')->with('alertmessage', 'Votre entreprise est déjà abonnée !');
    }

    $priceId = match($planName) {
        'solo'    => 'price_1SVBouB4LVww0Nzz5cvK9elS', 
        'premium' => 'price_1SVBpHB4LVww0Nzztgb3hUSH',
        'pro'     => 'price_1SVBpSB4LVww0NzzFxjCdtU1',
    };

    return $entreprise->newSubscription('default', $priceId)
        ->checkout([
            'success_url' => route('subscription.success', ['plan' => $planName]) . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => url()->previous(),
            'metadata' => [ // ✅ Ajouter les métadonnées
                'plan_name' => $planName,
                'entreprise_id' => $entreprise->id,
                'user_id' => Auth::id(),
            ],
        ]);
}

public function paymentSuccess(Request $request, $planName)
{
    $user = Auth::user();
    $entreprise = $user->entreprise;

    // ✅ Vérifier si le webhook a déjà mis à jour
    if ($entreprise->subscribed('default') && $entreprise->type_compt === $planName) {
        return redirect()->route('dashboard')
            ->with('success', 'Abonnement déjà activé !');
    }

    // ✅ Mise à jour manuelle (utile en local)
    $entreprise->forceFill([
        'type_compt' => $planName,
        'trial_ends_at' => null,
    ])->save();

    return redirect()->route('dashboard')
        ->with('success', 'Paiement réussi ! Plan ' . ucfirst($planName) . ' activé.');
}
}
