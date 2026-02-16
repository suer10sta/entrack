<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Queue\SerializesModels;

class ContactFormMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $name;
    public string $email;
    public string $subjectLine;
    public string $messageBody;

    /**
     * Create a new message instance.
     */
    public function __construct(string $name, string $email, string $subjectLine, string $messageBody)
    {
        $this->name = $name;
        $this->email = $email;
        $this->subjectLine = $subjectLine;
        $this->messageBody = $messageBody;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            // Send from your fixed address for better deliverability...
            from: new Address('user5.sta@outlook.com', 'DalyTrac Contact'),
            subject: 'New contact message: ' . $this->subjectLine,
            replyTo: [
                // ...but keep Reply-To as the client's email so replying goes to them
                new Address($this->email, $this->name),
            ],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.contact',
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


