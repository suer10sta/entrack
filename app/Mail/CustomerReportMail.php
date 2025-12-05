<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use App\Models\Entreprise;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CustomerReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public $subject;
    public $body;
    public $pdfPath;
    public $projectName;
    public $startDate;
    public $endDate;

    /**
     * Create a new message instance.
     */
    public function __construct($subject, $body, $pdfPath, $projectName, $startDate, $endDate)
    {
        $this->subject = $subject;
        $this->body = $body;
        $this->pdfPath = $pdfPath;
        $this->projectName = $projectName;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $user = auth()->user();
        $companyEmail = $user->entreprise->email ?? config('mail.from.address');

        return new Envelope(
            subject: $this->subject,
            from: new Address($companyEmail),
            replyTo: [
                new Address($companyEmail)
            ]
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'admin.tasks.customer-email',
            with: [
                'body' => $this->body
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [
            Attachment::fromPath($this->pdfPath)
                ->as('Project_Report_' . $this->projectName . '_' . $this->startDate . '_to_' . $this->endDate . '.pdf')
                ->withMime('application/pdf'),
        ];
    }
} 
