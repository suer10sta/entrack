<?php

namespace App\Services;

use App\Enums\ContactEmailStatus;
use App\Enums\ContactEventType;
use App\Enums\ContactStatus;
use App\Mail\ContactReplyMail;
use App\Models\Contact;
use App\Models\Manager;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ContactManagementService
{
    public function reply(Contact $contact, Manager $manager, string $message, string $submissionKey): \App\Models\EmailSendsLog
    {
        Validator::make(['message' => $message, 'submission_key' => $submissionKey], [
            'message' => ['required', 'string', 'max:50000'], 'submission_key' => ['required', 'uuid'],
        ], [
            'message.required' => __('support.reply_required'), 'message.max' => __('support.reply_max'),
            'submission_key.required' => __('support.invalid_submission'), 'submission_key.uuid' => __('support.invalid_submission'),
        ])->validate();

        return $contact->getConnection()->transaction(function () use ($contact, $manager, $message, $submissionKey) {
            $locked = Contact::on($contact->getConnectionName())->whereKey($contact->id)->lockForUpdate()->firstOrFail();
            $existing = $locked->replies()->where('submission_key', $submissionKey)->first();
            if ($existing) {
                if ((int) $existing->manager_id !== (int) $manager->id || $existing->message !== $message) {
                    abort(409, __('support.submission_conflict'));
                }

                return $existing->emailLogs()->sole();
            }
            if ($locked->status === ContactStatus::Closed) {
                throw ValidationException::withMessages(['message' => __('support.closed_reply')]);
            }
            $reply = $locked->replies()->create(['manager_id' => $manager->id, 'message' => $message, 'submission_key' => $submissionKey]);
            $locked->update(['status' => ContactStatus::Replied]);
            $locked->events()->create(['manager_id' => $manager->id, 'event_type' => ContactEventType::Replied, 'metadata' => ['contact_reply_id' => $reply->id]]);
            $mail = new ContactReplyMail($locked->ticket_num, $locked->subject, $message);

            // Persist the log in the same transaction; SMTP happens only after commit.
            return $locked->emailLogs()->create(['contact_reply_id' => $reply->id, 'user_id' => $locked->user_id,
                'manager_id' => $manager->id, 'recipient_email' => $locked->email, 'subject' => $mail->notificationSubject(),
                'body' => $message,
                'status' => ContactEmailStatus::Pending]);
        }, 3);
    }

    public function close(Contact $contact, Manager $manager): void
    {
        $this->transition($contact, $manager, ContactStatus::Closed, ContactEventType::Closed);
    }

    public function reopen(Contact $contact, Manager $manager): void
    {
        $this->transition($contact, $manager, ContactStatus::Pending, ContactEventType::Reopened);
    }

    private function transition(Contact $contact, Manager $manager, ContactStatus $target, ContactEventType $event): void
    {
        $contact->getConnection()->transaction(function () use ($contact, $manager, $target, $event) {
            $locked = Contact::on($contact->getConnectionName())->whereKey($contact->id)->lockForUpdate()->firstOrFail();
            if ($target === ContactStatus::Closed && $locked->status === ContactStatus::Closed) {
                return;
            }
            if ($target === ContactStatus::Pending && $locked->status !== ContactStatus::Closed) {
                return;
            }
            $locked->update(['status' => $target]);
            $locked->events()->create(['manager_id' => $manager->id, 'event_type' => $event]);
        }, 3);
    }

    public function delete(Contact $contact, Manager $manager, string $confirmation): void
    {
        $contact->getConnection()->transaction(function () use ($contact, $confirmation) {
            $locked = Contact::on($contact->getConnectionName())->whereKey($contact->id)->lockForUpdate()->firstOrFail();
            if (! hash_equals($locked->ticket_num, $confirmation)) {
                throw ValidationException::withMessages(['confirmation' => __('support.delete_confirmation_required')]);
            }
            // Initial event and all history cascade with the permanently deleted ticket.
            // Never touch the independent numbering allocator.
            $locked->delete();
        }, 3);
    }
}
