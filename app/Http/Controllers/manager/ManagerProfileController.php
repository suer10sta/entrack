<?php

namespace App\Http\Controllers\manager;

use App\Http\Controllers\Controller;
use App\Models\Manager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ManagerProfileController extends Controller
{
    public function index()
    {
        $manager = auth('manager')->user();
        
        return view('manager.profile.index', [
            'manager' => $manager,
        ]);
    }

    public function edit()
    {
        $manager = auth('manager')->user();
        
        return view('manager.profile.edit', [
            'manager' => $manager,
        ]);
    }

    public function update(Request $request)
    {
        $manager = auth('manager')->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:managers,email,' . $manager->id],
        ]);

        $manager->update($validated);

        return redirect()->route('manager.profile.index')
            ->with('success', 'Profile updated successfully.');
    }

    public function updatePassword(Request $request)
    {
        $manager = auth('manager')->user();

        $validated = $request->validate([
            'current_password' => ['required', function ($attribute, $value, $fail) use ($manager) {
                if (!Hash::check($value, $manager->password)) {
                    $fail('The current password is incorrect.');
                }
            }],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $manager->update([
            'password' => $validated['password'], // Will be auto-hashed by the model cast
        ]);

        return redirect()->route('manager.profile.index')
            ->with('success', 'Password updated successfully.');
    }
}

