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
        $user= Auth::user();

        $user->load('employee');
        //dd($user->employee);

        return view('admin.profile.index',['user'=>$user]);
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

    // 4. ADD THIS PART: Save data to the 'employees' table
    $user->employee()->updateOrCreate(
        ['user_id' => $user->id],  // Find the employee by user_id
        $validatedData            // Update it with the validated data
    );

    

    return Redirect::route('profile.edit')->with('status', 'profile-updated');
}
    public function showUpgradePage() : view
    {
        // This will look foa file at:
        // resources/views/profile/upgrade.blade.php
        return view('admin.profile.upgrade_page');
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
