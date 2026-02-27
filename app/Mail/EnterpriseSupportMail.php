<?php

namespace App\Mail;

use App\Models\Entreprise;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EnterpriseSupportMail extends Mailable
{
    use Queueable, SerializesModels;

    public ?Entreprise $entreprise;
    public User $user;
    public array $data;

    /**
     * Create a new message instance.
     */
    public function __construct(?Entreprise $entreprise, User $user, array $data)
    {
        $this->entreprise = $entreprise;
        $this->user = $user;
        $this->data = $data;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        // Ensure we always have a valid "from" address, even if MAIL_FROM_* are misconfigured / empty
        $fromAddress = config('mail.from.address') ?: 'support@dalytrac.com';
        $fromName = config('mail.from.name') ?: 'DalyTrac Support';

        $subjectPrefix = $this->entreprise?->name
            ? '[Enterprise Support] [' . $this->entreprise->name . '] '
            : '[Enterprise Support] ';

        return new Envelope(
            from: new Address($fromAddress, $fromName),
            replyTo: [
                new Address($this->user->email, $this->user->name),
            ],
            subject: $subjectPrefix . ($this->data['subject'] ?? 'Support request'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.enterprise_support',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}


