<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminActivityAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $title;
    public string $body;
    public array $meta;

    /**
     * Create a new message instance.
     */
    public function __construct(string $title, string $body, array $meta = [])
    {
        $this->title = $title;
        $this->body = $body;
        $this->meta = $meta;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $fromAddress = config('mail.from.address') ?: 'support@dalytrac.com';
        $fromName = config('mail.from.name') ?: 'DalyTrac';

        return new Envelope(
            from: new Address($fromAddress, $fromName),
            subject: $this->title,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.admin_activity_alert',
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

