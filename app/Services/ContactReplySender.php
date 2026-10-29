<?php

namespace App\Services;

use App\Enums\ContactEmailStatus;
use App\Mail\ContactReplyMail;
use App\Models\EmailSendsLog;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Throwable;

class ContactReplySender
{
    public function send(EmailSendsLog $log): ContactEmailStatus
    {
        // Claim once after commit. A replay never retries failed/uncertain mail.
        $claimed = EmailSendsLog::on($log->getConnectionName())->whereKey($log->id)
            ->where('status', ContactEmailStatus::Pending->value)->whereNull('attempted_at')
            ->update(['attempted_at' => now()]);
        if (! $claimed) {
            return $log->fresh()->status;
        }
        $inTransport = false;
        $accepted = false;
        try {
            $log->refresh()->load(['contact', 'reply']);
            if (! $log->reply || (int) $log->reply->contact_id !== (int) $log->contact_id) {
                throw new RuntimeException('Contact/reply mismatch');
            }
            $mail = new ContactReplyMail($log->contact->ticket_num, $log->contact->subject, $log->reply->message);
            if (! filter_var($log->recipient_email, FILTER_VALIDATE_EMAIL)) {
                throw new RuntimeException('Invalid recipient');
            }
            $log->update(['body' => view('emails.contact_reply', ['ticketNum' => $log->contact->ticket_num, 'originalSubject' => $log->contact->subject, 'replyMessage' => $log->reply->message])->render()]);
            $mail->envelope();
            $inTransport = true;
            $result = Mail::to($log->recipient_email)->send($mail);
            if (! $result && ! (Mail::getFacadeRoot() instanceof \Illuminate\Support\Testing\Fakes\MailFake)) {
                $inTransport = false;
                throw new RuntimeException('Cancelled before transport acceptance');
            }
            $accepted = true;
            $log->update(['status' => ContactEmailStatus::Sent, 'sent_at' => now(), 'error_message' => null]);

            return ContactEmailStatus::Sent;
        } catch (Throwable $error) {
            // Same conservative SMTP classification as the initial Contact notification.
            $rejected = $error instanceof TransportExceptionInterface && preg_match('/(?:got|code|response)[^0-9]{0,20}[45][0-9]{2}\b/i', $error->getMessage());
            $beforeAcceptance = $error instanceof TransportExceptionInterface && preg_match('/could not connect|connection could not be established|connection refused|unable to connect|failed to authenticate|authentication failed/i', $error->getMessage());
            $uncertain = ! $accepted && $inTransport && ! $rejected && ! $beforeAcceptance;
            $status = $accepted ? ContactEmailStatus::Sent : ($uncertain ? ContactEmailStatus::Uncertain : ContactEmailStatus::Failed);
            try {
                $log->update(['status' => $status, 'sent_at' => $accepted ? ($log->sent_at ?? now()) : null,
                    'failed_at' => $status === ContactEmailStatus::Failed ? now() : null,
                    'error_message' => $accepted ? null : ($uncertain ? 'Transport acceptance unconfirmed. No automatic retry.' : 'Reply notification failed.')]);
            } catch (Throwable $persistError) {
                Log::error('Contact reply delivery result persistence failed.', ['email_log_id' => $log->id, 'error_class' => get_class($persistError)]);

                // Do not report a confirmed, persisted success if its audit write failed.
                return ContactEmailStatus::Uncertain;
            }
            Log::warning('Contact reply transport incident.', ['email_log_id' => $log->id, 'status' => $status->value, 'error_class' => get_class($error)]);

            return $status;
        }
    }
}
