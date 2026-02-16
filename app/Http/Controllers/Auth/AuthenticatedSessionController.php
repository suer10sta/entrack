<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Http\Controllers\admin\projectController; // Import the projectController


class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        // Check if the user is authenticated
        if (Auth::check()) {
            $user = Auth::user();

            // Determine if the user's entreprise is blocked (trial expired or explicitly inactive)
            $entreprise = $user->entreprise ?? null;
            $entrepriseBlocked = false;

            if ($entreprise) {
                $entrepriseBlocked = $entreprise->isTrialExpired()
                    || $entreprise->account_status === 'inactive';

                // Never treat exempt entreprises (e.g. ID = 43) as blocked
                if (method_exists($entreprise, 'isBlockingExempt') && $entreprise->isBlockingExempt()) {
                    $entrepriseBlocked = false;
                }
            }

            // Only Admin / SuperAdmin users are allowed to log in when entreprise is blocked
            $isAdminUser = $user->hasRole('Admin')
                || $user->hasRole('SuperAdmin')
                || $user->hasRole('super_admin');

            if ($entrepriseBlocked && !$isAdminUser) {
                // Immediately log the user out and show a clear error message on the login page
                Auth::logout();

                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'email' => 'Your entreprise account is blocked. Please contact your administrator.',
                ])->onlyInput('email');
            }

            // Mark user as authenticated/online
            $user->Auth_status = 1;
            $user->save();

            if ($user->hasRole('Admin')) {
                $projectController = new projectController(); // Instantiate the projectController
                //$projectController->sendemailreminder(); // Call the sendemailreminder method 
            }
        }

        $request->session()->regenerate();

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        // Retrieve the authenticated user
        $user = Auth::guard('web')->user();
        
        // Check if a user is authenticated
        if ($user) {
            // Update the user's status to 0
            $user->Auth_status = 0;
            $user->save();
        }

        // Proceed with the logout process
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
