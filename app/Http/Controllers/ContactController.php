<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\ContactFormMail;
use App\Mail\AdminActivityAlertMail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

class ContactController extends Controller
{
    /**
     * Handle contact form submissions from the public contact page.
     */
    public function submit(Request $request)
    {
        // Check Honeypot first
        if (!empty($request->input('website'))) {
            Log::info('Bot submission detected and blocked via Honeypot.');
            return redirect()->route('welcome');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
        ]);

        // Turnstile Validation
        $turnstileResponse = $request->input('cf-turnstile-response');
        if (!$turnstileResponse) {
            return back()->withInput()->withErrors(['cf-turnstile-response' => __('Veuillez cocher la case pour prouver que vous n\'êtes pas un robot.')]);
        }

        $verify = Http::asForm()->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
            'secret' => config('services.turnstile.secret_key'),
            'response' => $turnstileResponse,
            'remoteip' => $request->ip(),
        ]);

        if (!$verify->json('success')) {
            Log::warning('Turnstile verification failed.', ['response' => $verify->json()]);
            return back()->withInput()->withErrors(['cf-turnstile-response' => __('La validation de sécurité a échoué. Veuillez réessayer.')]);
        }

        // Basic debug info before sending
        Log::info('Contact form submission received.', [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'subject' => $validated['subject'],
            // Don't log full message body in case of sensitive info
            'message_length' => strlen($validated['message']),
            'mailer' => config('mail.default'),
            'mail_host' => config('mail.mailers.' . config('mail.default') . '.host'),
            'mail_from' => config('mail.from'),
        ]);

        try {
            // Send the email to the official support inbox using the current SMTP / mailer configuration
            Mail::to('support@dalytrac.com')->send(new ContactFormMail(
                $validated['name'],
                $validated['email'],
                $validated['subject'],
                $validated['message']
            ));

            // Check for low‑level send failures, if supported
            $failures = method_exists(Mail::getFacadeRoot(), 'failures') ? Mail::failures() : [];

            if (!empty($failures)) {
                Log::error('Mail::failures() reported problems when sending contact email.', [
                    'failures' => $failures,
                ]);

                return back()->withInput()->with('error', 'Your message could not be delivered (mail failures detected). Please try again later.');
            }

            Log::info('Contact email dispatched successfully (no Mail::failures reported).');

            // Fire an admin activity alert without impacting user flow if it fails
            // COMMENTED: admin alert emails to dessin@bureaumercier.com disabled
            /*
            try {
                Mail::to('dessin@bureaumercier.com')->send(new AdminActivityAlertMail(
                    'New contact form submission',
                    'A new contact message was submitted from the public contact page.',
                    [
                        'name' => $validated['name'],
                        'email' => $validated['email'],
                        'subject' => $validated['subject'],
                        'message_preview' => Str::limit($validated['message'], 200),
                    ]
                ));
            } catch (\Throwable $alertException) {
                Log::error('Failed to send admin activity alert for contact form submission.', [
                    'error_message' => $alertException->getMessage(),
                    'exception_class' => get_class($alertException),
                ]);
            }
            */

            return back()->with('success', 'Thank you for your message! We will get back to you soon.');
        } catch (\Throwable $e) {
            // Log detailed error so we can debug SMTP / configuration issues
            Log::error('Exception while sending contact email.', [
                'error_message' => $e->getMessage(),
                'exception_class' => get_class($e),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'We could not send your message due to a mail error. Please try again later or contact us by another channel.');
        }
    }
}


