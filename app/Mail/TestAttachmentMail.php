<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;

class TestAttachmentMail extends Mailable
{
    use Queueable, SerializesModels;

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Test Attachment',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.contact_employee',
            with: [
                'body' => 'Test body',
                'subject' => 'Test Attachment'
            ],
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromPath(storage_path('app/test.pdf'))->as('test.pdf')
        ];
    }
}