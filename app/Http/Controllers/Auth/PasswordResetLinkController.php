<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        // Check Honeypot first
        if (!empty($request->input('website'))) {
            Log::info('Bot password reset link request blocked via Honeypot.');
            return redirect()->route('welcome');
        }

        // Turnstile Validation
        $turnstileResponse = $request->input('cf-turnstile-response');
        if (!$turnstileResponse) {
            return back()->withInput()->withErrors(['cf-turnstile-response' => __('messages.auth_turnstile_required')]);
        }

        $verify = Http::asForm()->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
            'secret' => config('services.turnstile.secret_key'),
            'response' => $turnstileResponse,
            'remoteip' => $request->ip(),
        ]);

        if (!$verify->json('success')) {
            Log::warning('Turnstile verification failed for password reset link request.', ['response' => $verify->json()]);
            return back()->withInput()->withErrors(['cf-turnstile-response' => __('messages.auth_turnstile_failed')]);
        }

        $request->validate([
            'email' => ['required', 'email'],
        ], [
            'email.required' => __('messages.auth_val_email_required'),
            'email.email' => __('messages.auth_val_email_invalid'),
        ]);

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status == Password::RESET_LINK_SENT
                    ? back()->with('status', __($status))
                    : back()->withInput($request->only('email'))
                            ->withErrors(['email' => __($status)]);
    }
}
