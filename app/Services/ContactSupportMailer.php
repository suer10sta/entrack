<?php

namespace App\Services;

use App\Enums\ContactEmailStatus;
use App\Mail\ContactFormMail;
use App\Models\Contact;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Throwable;

class ContactSupportMailer
{
    /** One initial notification attempt. No retries, ticket creation or deletion. */
    public function send(Contact $contact): void
    {
        $log = null;
        $inTransport = false;
        $accepted = false;
        try {
            $mail = new ContactFormMail($contact->name, $contact->email, $contact->subject, $contact->message, $contact->ticket_num);
            $log = $contact->emailLogs()->create([
                'contact_reply_id' => null, 'user_id' => $contact->user_id, 'manager_id' => null,
                'recipient_email' => (string) config('support.email'),
                'subject' => $mail->notificationSubject(),
                'body' => $contact->message,
                'status' => ContactEmailStatus::Pending,
            ]);
            if (! filter_var(config('support.email'), FILTER_VALIDATE_EMAIL)) {
                throw new RuntimeException('Invalid support recipient configuration.');
            }
            $log->update(['body' => view('emails.contact', ['name' => $contact->name, 'email' => $contact->email, 'subjectLine' => $contact->subject, 'messageBody' => $contact->message, 'ticketNum' => $contact->ticket_num])->render(), 'attempted_at' => now()]);
            $mail->envelope(); // Validate the configured sender before transport begins.
            $inTransport = true;
            $result = Mail::to(config('support.email'))->send($mail);
            // MailFake intentionally returns null; a real mailer cancellation does too.
            if (! $result && ! (Mail::getFacadeRoot() instanceof \Illuminate\Support\Testing\Fakes\MailFake)) {
                $inTransport = false;
                throw new RuntimeException('Message cancelled before transport acceptance.');
            }
            $accepted = true;
            $log->update(['status' => ContactEmailStatus::Sent, 'sent_at' => now(), 'error_message' => null]);
        } catch (Throwable $error) {
            // A transport exception without an explicit rejection may have occurred
            // after SMTP accepted DATA. Never claim a certain failure in that case.
            $rejected = $error instanceof TransportExceptionInterface
                && preg_match('/(?:got|code|response)[^0-9]{0,20}[45][0-9]{2}\b/i', $error->getMessage());
            $beforeAcceptance = $error instanceof TransportExceptionInterface
                && preg_match('/could not connect|connection could not be established|connection refused|unable to connect|failed to authenticate|authentication failed/i', $error->getMessage());
            $uncertain = ! $accepted && $inTransport && ! $rejected && ! $beforeAcceptance;
            $status = $accepted ? ContactEmailStatus::Sent : ($uncertain ? ContactEmailStatus::Uncertain : ContactEmailStatus::Failed);
            if ($log) {
                try {
                    $log->update(['status' => $status, 'attempted_at' => $log->attempted_at ?? now(),
                        'sent_at' => $accepted ? ($log->sent_at ?? now()) : null,
                        'failed_at' => $status === ContactEmailStatus::Failed ? now() : null,
                        'error_message' => $accepted ? null : ($uncertain ? 'Transport acceptance could not be confirmed. No automatic retry.' : 'Support notification failed. See application diagnostics.')]);
                } catch (Throwable $loggingError) {
                    Log::error('Contact email result could not be persisted.', ['contact_id' => $contact->id, 'email_log_id' => $log->id, 'error_class' => get_class($loggingError)]);
                }
            }
            Log::warning('Contact support notification not confirmed.', ['contact_id' => $contact->id,
                'email_log_id' => $log?->id, 'status' => $status->value, 'error_class' => get_class($error)]);
        }
    }
}
