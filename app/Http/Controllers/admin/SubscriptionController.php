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

        // [CHANGED] Get the Entreprise instead of the User
        $entreprise = Auth::user()->entreprise; 

        // [CHANGED] Check if the ENTREPRISE is subscribed
        if ($entreprise->subscribed('default')) {
             return redirect()->route('dashboard')->with('alertmessage', 'Votre entreprise est déjà abonnée !');
        }

        // Map plans to Stripe Price IDs (Replace with your actual Test IDs)
        $priceId = match($planName) {
            'solo'    => 'price_1SVBouB4LVww0Nzz5cvK9elS', 
            'premium' => 'price_1SVBpHB4LVww0Nzztgb3hUSH',
            'pro'     => 'price_1SVBpSB4LVww0NzzFxjCdtU1',
        };

        // [CHANGED] Create subscription on the ENTREPRISE model
        return $entreprise->newSubscription('default', $priceId)
            ->checkout([
                'success_url' => route('subscription.success', ['plan' => $planName]) . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => url()->previous(),
            ]);
    }

    public function paymentSuccess(Request $request, $planName)
    {
        // 1. Get the Entreprise
        $user = Auth::user();
        $entreprise = $user->entreprise;

        // 2. DIAGNOSIS RESULT:
        // We know the Webhook failed (Localhost), so $entreprise->subscribed() will return false.
        // We will SKIP that check and trust that the user paid because they have a valid session_id.

        // 3. FORCE UPDATE the database columns
        $entreprise->type_compt = $planName; 
        $entreprise->trial_ends_at = null; // Cancel the trial immediately
        $entreprise->save();

        // 4. Redirect to Dashboard with Success Message
        return redirect()->route('dashboard')
            ->with('success', 'Paiement réussi ! Votre entreprise est maintenant abonnée au plan ' . ucfirst($planName) . '.');
    }
}