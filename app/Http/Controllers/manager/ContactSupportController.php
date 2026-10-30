<?php

namespace App\Http\Controllers\manager;

use App\Enums\ContactEmailStatus;
use App\Enums\ContactPriority;
use App\Enums\ContactSource;
use App\Enums\ContactStatus;
use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Services\ContactManagementService;
use App\Services\ContactReplySender;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Throwable;

class ContactSupportController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in(array_column(ContactStatus::cases(), 'value'))],
            'source' => ['nullable', Rule::in(array_column(ContactSource::cases(), 'value'))],
            'priority' => ['nullable', Rule::in(array_column(ContactPriority::cases(), 'value'))],
            'sort' => ['nullable', Rule::in(['newest', 'oldest'])],
            'per_page' => ['nullable', 'integer', Rule::in([15, 25, 50, 100])],
        ]);
        $counts = DB::table('contacts')->selectRaw('status, COUNT(*) AS total')->groupBy('status')->pluck('total', 'status');
        $counts = ['all' => (int) $counts->sum()] + $counts->map(fn ($count) => (int) $count)->all();
        $query = Contact::query()->select(['id', 'ticket_num', 'name', 'email', 'subject', 'source', 'status', 'priority', 'created_at'])
            ->selectRaw('SUBSTR(message, 1, 601) AS message_preview');
        if (filled($filters['q'] ?? null)) {
            $pattern = '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], mb_strtolower(trim($filters['q']))).'%';
            $query->where(function ($query) use ($pattern) {
                foreach (['ticket_num', 'name', 'email', 'subject'] as $column) {
                    $query->orWhereRaw("LOWER({$column}) LIKE ? ESCAPE '!'", [$pattern]);
                }
            });
        }
        foreach (['status', 'source', 'priority'] as $field) {
            if (filled($filters[$field] ?? null)) {
                $query->where($field, $filters[$field]);
            }
        }
        $direction = ($filters['sort'] ?? 'newest') === 'oldest' ? 'asc' : 'desc';
        $contacts = $query->orderBy('created_at', $direction)->orderBy('id', $direction)
            ->paginate((int) ($filters['per_page'] ?? 15))->withQueryString();

        return view('manager.support.index', compact('contacts', 'counts', 'filters'));
    }

    public function show(Contact $contact)
    {
        $contact->load(['user:id,name,email', 'entreprise:id,name']);
        $events = $contact->events()->with('manager:id,name')->orderBy('created_at')->orderBy('id')
            ->paginate(20, ['id', 'contact_id', 'manager_id', 'event_type', 'created_at'], 'events_page')->withQueryString();
        $emails = $contact->emailLogs()->latest('created_at')->orderByDesc('id')
            ->paginate(20, ['id', 'contact_id', 'recipient_email', 'subject', 'status', 'attempted_at', 'sent_at', 'failed_at', 'created_at'], 'emails_page')->withQueryString();
        $replies = $contact->replies()->with('manager:id,name')->orderBy('created_at')->orderBy('id')
            ->paginate(20, ['id', 'contact_id', 'manager_id', 'message', 'created_at'], 'replies_page')->withQueryString();

        $submissionKey = (string) Str::uuid();

        return view('manager.support.show', compact('contact', 'events', 'emails', 'replies', 'submissionKey'));
    }

    public function reply(Request $request, Contact $contact, ContactManagementService $service, ContactReplySender $sender)
    {
        $data = $request->validate(['message' => ['required', 'string', 'max:50000'], 'submission_key' => ['required', 'uuid']], [
            'message.required' => __('support.reply_required'), 'message.string' => __('support.reply_required'),
            'message.max' => __('support.reply_max'), 'submission_key.required' => __('support.invalid_submission'),
            'submission_key.uuid' => __('support.invalid_submission'),
        ]);
        $log = $service->reply($contact, $request->user('manager'), $data['message'], $data['submission_key']);
        try {
            $result = $sender->send($log);
        } catch (Throwable $error) {
            Log::error('Contact reply attempt could not be confirmed.', ['contact_id' => $contact->id, 'email_log_id' => $log->id, 'error_class' => get_class($error)]);
            $result = ContactEmailStatus::Uncertain;
        }

        return redirect()->route('manager.support.show', $contact)->with(
            $result === ContactEmailStatus::Sent ? 'success' : 'warning',
            __($result === ContactEmailStatus::Sent ? 'support.reply_sent' : 'support.reply_saved_unconfirmed')
        );
    }

    public function close(Request $request, Contact $contact, ContactManagementService $service)
    {
        $request->validate(['confirmed' => ['required', 'accepted']], ['confirmed.required' => __('support.confirm_required'), 'confirmed.accepted' => __('support.confirm_required')]);
        $service->close($contact, $request->user('manager'));

        return redirect()->route('manager.support.show', $contact)->with('success', __('support.ticket_closed'));
    }

    public function reopen(Request $request, Contact $contact, ContactManagementService $service)
    {
        $service->reopen($contact, $request->user('manager'));

        return redirect()->route('manager.support.show', $contact)->with('success', __('support.ticket_reopened'));
    }

    public function destroy(Request $request, Contact $contact, ContactManagementService $service)
    {
        $data = $request->validate(['confirmation' => ['required', 'string']], ['confirmation.required' => __('support.delete_confirmation_required'), 'confirmation.string' => __('support.delete_confirmation_required')]);
        $service->delete($contact, $request->user('manager'), $data['confirmation']);

        return redirect()->route('manager.support.index')->with('success', __('support.ticket_deleted'));
    }
}
