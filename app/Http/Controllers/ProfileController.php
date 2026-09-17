<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{

    public function index()
    {
        // Return the profile Blade view
        $user = Auth::user();

        // Load both employee AND entreprise relationships
        $user->load('employee', 'entreprise');
        
        // Determine the plan key based on entreprise data
        $entreprise = $user->entreprise;
        
        if ($entreprise) {
            if ($entreprise->isOnTrial()) {
                $planKey = 'trial';
            } else {
                // type_compt contains: 'solo', 'premium', or 'pro'
                $planKey = $entreprise->type_compt ?? 'trial';
            }
            
            // Calculate days left for trial
            $daysLeft = 0;
            if ($entreprise->isOnTrial() && $entreprise->trial_ends_at) {
                $daysLeft = now()->diffInDays($entreprise->trial_ends_at, false);
                $daysLeft = max(0, ceil($daysLeft)); // Ensure it's not negative
            }
        } else {
            $planKey = 'trial';
            $daysLeft = 0;
        }

        // Renewal sync status for auto/manual payment preference
        $renewalSync = $entreprise
            ? $entreprise->renewalSyncStatus()
            : [
                'state' => 'unknown',
                'is_synced' => false,
                'expected' => 'auto',
                'stripe' => null,
                'label' => 'No entreprise linked to this account.',
            ];

        return view('admin.profile.index', [
            'user' => $user,
            'planKey' => $planKey,
            'daysLeft' => $daysLeft,
            'renewalSync' => $renewalSync,
        ]);
    }
    
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = Auth::user();
        $user->load('employee');

        return view('admin.profile.edit', [
            'user' => $user,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        // 1. Get the validated data
        $validatedData = $request->validated();
        
        // 2. Get the User
        $user = $request->user();

        // 3. Fill and Save data to the 'users' table
        $user->fill($validatedData);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        // If the user updated their language, apply immediately for this session.
        if (!empty($validatedData['locale'])) {
            $request->session()->put('locale', $validatedData['locale']);
            app()->setLocale($validatedData['locale']);
        }

        // 4. Save data to the 'employees' table
        $user->employee()->updateOrCreate(
            ['user_id' => $user->id],  // Find the employee by user_id
            $validatedData            // Update it with the validated data
        );

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }
    
    public function showUpgradePage(): View
    {
        $entreprise = Auth::user()?->entreprise;
        
        // Calculate remaining trial days
        $trialDaysRemaining = 0;
        if ($entreprise && $entreprise->trial_ends_at && $entreprise->trial_ends_at->isFuture()) {
            $trialDaysRemaining = now()->diffInDays($entreprise->trial_ends_at, false);
        }
        
        $renewalSync = $entreprise
            ? $entreprise->renewalSyncStatus()
            : [
                'state' => 'unknown',
                'is_synced' => false,
                'expected' => 'auto',
                'stripe' => null,
                'label' => 'No entreprise linked to this account.',
            ];

        return view('admin.profile.upgrade_page', compact('renewalSync', 'trialDaysRemaining'));
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
