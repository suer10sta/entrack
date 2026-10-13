<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
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
        // Check Honeypot first
        if (!empty($request->input('website'))) {
            Log::info('Bot login attempt blocked via Honeypot.');
            return redirect()->route('login');
        }

        // TEMPORARILY DISABLED: Cloudflare Turnstile verification (restore to re-enable anti-bot check).
        // // Turnstile Validation
        // $turnstileResponse = $request->input('cf-turnstile-response');
        // if (!$turnstileResponse) {
        //     return back()->withInput()->withErrors(['cf-turnstile-response' => __('messages.auth_turnstile_required')]);
        // }
        //
        // $verify = Http::asForm()->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
        //     'secret' => config('services.turnstile.secret_key'),
        //     'response' => $turnstileResponse,
        //     'remoteip' => $request->ip(),
        // ]);
        //
        // if (!$verify->json('success')) {
        //     Log::warning('Turnstile verification failed for login.', ['response' => $verify->json()]);
        //     return back()->withInput()->withErrors(['cf-turnstile-response' => __('messages.auth_turnstile_failed')]);
        // }

        $request->authenticate();

        // Check if the user is authenticated
        if (Auth::check()) {
            $user = Auth::user();
            // Track last login IP and geolocated country/city
            try {
                $ip = $request->ip();
                $country = null;
                $city = null;

                if ($ip && !in_array($ip, ['127.0.0.1', '::1'], true)) {
                    try {
                        $response = Http::get('http://ip-api.com/json/' . $ip . '?fields=status,country,city');
                        if ($response->ok() && ($response['status'] ?? null) === 'success') {
                            $country = $response['country'] ?? null;
                            $city = $response['city'] ?? null;
                        }
                    } catch (\Throwable $e) {
                        // Ignore geo lookup failures and still allow login.
                    }
                }

                $user->last_login_ip = $ip;
                $user->last_login_country = $country;
                $user->last_login_city = $city;
            } catch (\Throwable $e) {
                // Never block login if location tracking fails.
            }

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
                    'email' => __('messages.auth_account_blocked'),
                ])->onlyInput('email');
            }

            // Archived employees (archived = 1) must not be able to sign in,
            // even if the enterprise itself is otherwise active.
            if ($user->employee && $user->employee->archived) {
                Auth::logout();

                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'email' => __('messages.auth_account_archived'),
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
