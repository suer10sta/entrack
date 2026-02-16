<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\ContactFormMail;

class ContactController extends Controller
{
    /**
     * Handle contact form submissions from the public contact page.
     */
    public function submit(Request $request)
    {
        $validated = $request->validate([
            'name'    => ['required', 'string', 'max:255'],
            'email'   => ['required', 'email', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
        ]);

        // Basic debug info before sending
        Log::info('Contact form submission received.', [
            'name'          => $validated['name'],
            'email'         => $validated['email'],
            'subject'       => $validated['subject'],
            // Don't log full message body in case of sensitive info
            'message_length'=> strlen($validated['message']),
            'mailer'        => config('mail.default'),
            'mail_host'     => config('mail.mailers.' . config('mail.default') . '.host'),
            'mail_from'     => config('mail.from'),
        ]);

        try {
            // Send the email using the current SMTP / mailer configuration
            Mail::to('user5.sta@outlook.com')->send(new ContactFormMail(
                $validated['name'],
                $validated['email'],
                $validated['subject'],
                $validated['message']
            ));

            // Check for low‑level send failures, if supported
            $failures = method_exists(Mail::getFacadeRoot(), 'failures') ? Mail::failures() : [];

            if (! empty($failures)) {
                Log::error('Mail::failures() reported problems when sending contact email.', [
                    'failures' => $failures,
                ]);

                return back()->withInput()->with('error', 'Your message could not be delivered (mail failures detected). Please try again later.');
            }

            Log::info('Contact email dispatched successfully (no Mail::failures reported).');

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


