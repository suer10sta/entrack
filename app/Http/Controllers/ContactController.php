<?php

namespace App\Http\Controllers;

use App\Enums\ContactSource;
use App\Models\User;
use App\Services\ContactSupportMailer;
use App\Services\TicketService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

class ContactController extends Controller
{
    public function submit(Request $request, TicketService $tickets, ContactSupportMailer $mailer)
    {
        if (! empty($request->input('website'))) {
            return redirect()->route('welcome');
        }
        if (is_string($request->input('email'))) {
            $request->merge(['email' => trim($request->input('email'))]);
        }
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'subject' => ['required', 'string', 'max:255', 'not_regex:/[\r\n]/'],
            'message' => ['required', 'string', 'max:50000'],
        ]);
        $user = Auth::guard('web')->user();
        $user = $user instanceof User ? $user : null;
        try {
            $contact = $tickets->create(
                name: $validated['name'], email: $validated['email'],
                subject: $validated['subject'], message: $validated['message'],
                source: ContactSource::Contact, ip: $request->ip(),
                user: $user, entreprise: $user?->entreprise,
            );
        } catch (ValidationException $error) {
            throw $error;
        } catch (Throwable $error) {
            // Do not log SQL bindings, submitted content or infrastructure secrets.
            Log::error('Contact persistence failed.', ['error_class' => get_class($error)]);

            return back()->withInput()->with('error', __('messages.contact_ticket_save_failed'));
        }

        // TicketService has committed before any transport work begins.
        $mailer->send($contact);

        // A recorded request is a success even when its internal notification fails.
        return back()->with('success', __('messages.contact_ticket_received', ['ticket' => $contact->ticket_num]));
    }
}
