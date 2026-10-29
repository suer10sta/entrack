<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactReplyMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public string $ticketNum, public string $originalSubject, public string $replyMessage) {}

    public function notificationSubject(): string
    {
        return mb_substr('Re: [DalyTrac Support - '.$this->ticketNum.'] '.preg_replace('/[\r\n]+/', ' ', $this->originalSubject), 0, 255);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('mail.from.address'), config('mail.from.name') ?: 'DalyTrac'),
            replyTo: [new Address(config('support.email'), 'DalyTrac Support')],
            subject: $this->notificationSubject(),
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.contact_reply');
    }
}
