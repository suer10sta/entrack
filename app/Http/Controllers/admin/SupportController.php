<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Mail\EnterpriseSupportMail;
use App\Mail\AdminActivityAlertMail;
use App\Models\Entreprise;
use App\Models\SupportTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SupportController extends Controller
{
    /**
     * Show the enterprise-optimized support interface.
     */
    public function create()
    {
        $user = Auth::user();
        $entreprise = $user->entreprise ?? Entreprise::find($user->entreprise_id);

        return view('admin.support.create', compact('user', 'entreprise'));
    }

    /**
     * Handle submission of an enterprise support ticket.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $entreprise = $user->entreprise ?? Entreprise::find($user->entreprise_id);

        $validated = $request->validate([
            'subject'  => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
            'priority' => ['required', 'in:low,medium,high'],
            'message'  => ['required', 'string'],
        ]);

        Log::info('Enterprise support ticket submitted.', [
            'user_id'      => $user->id ?? null,
            'user_email'   => $user->email ?? null,
            'entreprise_id'=> optional($entreprise)->id,
            'entreprise'   => optional($entreprise)->name,
            'subject'      => $validated['subject'],
            'category'     => $validated['category'] ?? null,
            'priority'     => $validated['priority'],
        ]);

        // Persist ticket so the user can track it
        $ticket = SupportTicket::create([
            'user_id'       => $user->id,
            'entreprise_id' => optional($entreprise)->id,
            'subject'       => $validated['subject'],
            'category'      => $validated['category'] ?? null,
            'priority'      => $validated['priority'],
            'message'       => $validated['message'],
            'status'        => 'open',
        ]);

        try {
            Mail::to('support@dalytrac.com')->send(
                new EnterpriseSupportMail($entreprise, $user, $validated + ['ticket_id' => $ticket->id])
            );

            // Send an admin activity alert, but don't break user flow if it fails
            try {
                Mail::to('dessin@bureaumercier.com')->send(
                    new AdminActivityAlertMail(
                        'New enterprise support ticket',
                        'A new enterprise support ticket has been submitted.',
                        [
                            'ticket_id' => $ticket->id,
                            'entreprise' => optional($entreprise)->name,
                            'user_name' => $user->name,
                            'user_email' => $user->email,
                            'subject' => $validated['subject'],
                            'priority' => $validated['priority'],
                            'category' => $validated['category'] ?? null,
                        ]
                    )
                );
            } catch (\Throwable $alertException) {
                Log::error('Failed to send admin activity alert for support ticket.', [
                    'error_message' => $alertException->getMessage(),
                    'exception_class' => get_class($alertException),
                    'ticket_id' => $ticket->id,
                ]);
            }

            return back()->with('success', 'Your support request has been sent. Our team will get back to you soon.');
        } catch (\Throwable $e) {
            Log::error('Failed to send enterprise support ticket.', [
                'error_message' => $e->getMessage(),
                'exception_class' => get_class($e),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'We could not send your support request due to a mail error. Please try again later.');
        }
    }

    /**
     * List support tickets for the current user / enterprise.
     */
    public function index()
    {
        $user = Auth::user();
        $entreprise = $user->entreprise ?? Entreprise::find($user->entreprise_id);

        $tickets = SupportTicket::query()
            ->where('user_id', $user->id)
            ->when($entreprise, fn ($q) => $q->where('entreprise_id', $entreprise->id))
            ->latest()
            ->paginate(10);

        return view('admin.support.index', compact('tickets', 'user', 'entreprise'));
    }

    /**
     * Delete a support ticket owned by the current user.
     */
    public function destroy(SupportTicket $ticket)
    {
        $user = Auth::user();

        // Only allow deletion of own tickets (and same enterprise if set)
        if ($ticket->user_id !== $user->id) {
            abort(403);
        }

        $ticket->delete();

        return redirect()
            ->route('admin.support.index')
            ->with('success', 'Ticket deleted successfully.');
    }
}


