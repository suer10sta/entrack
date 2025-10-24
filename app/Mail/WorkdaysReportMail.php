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

class WorkdaysReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public $subject;
    public $body;
    public $pdfPath;
    public $month;
    public $year;
    public $extraAttachments = [];

    /**
     * Create a new message instance.
     */
    public function __construct($subject, $body, $pdfPath, $month, $year, $extraAttachments = [])
    {
        $this->subject = $subject;
        
        // Format the body with HTML
        $lines = explode("\n", $body);
        $formattedBody = '';
        
        // Make the first line bold
        if (count($lines) > 0) {
            $formattedBody .= '<strong>' . htmlspecialchars($lines[0]) . '</strong><br>';
            
            // Add the rest of the lines with proper line breaks
            for ($i = 1; $i < count($lines); $i++) {
                $line = trim($lines[$i]);
                if (empty($line)) {
                    $formattedBody .= '<br>';
                } else {
                    $formattedBody .= htmlspecialchars($line) . '<br>';
                }
            }
        }
        
        $this->body = $formattedBody;
        $this->pdfPath = $pdfPath;
        $this->month = $month;
        $this->year = $year;
        $this->extraAttachments = is_array($extraAttachments) ? $extraAttachments : [$extraAttachments];
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $entreprise_id = Auth::user()->entreprise_id;
        $entreprise = Entreprise::where('id', $entreprise_id)->first();
        $entreprise_email = $entreprise->email;

        return new Envelope(
            from: new Address($entreprise_email, 'Workdays Report - '.$entreprise->name),
            replyTo: [
                new Address($entreprise_email, 'Workdays Report - '.$entreprise->name)
            ],
            subject: $this->subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'admin.workDaysRepports.email',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $attachments = [
            Attachment::fromPath($this->pdfPath)
                ->as('workdays_report_'.$this->month.'_'.$this->year.'.pdf')
                ->withMime('application/pdf'),
        ];

        foreach ($this->extraAttachments as $uploadedFile) {
            if (!$uploadedFile) {
                continue;
            }
            try {
                $attachments[] = Attachment::fromPath($uploadedFile->getRealPath())
                    ->as($uploadedFile->getClientOriginalName())
                    ->withMime($uploadedFile->getClientMimeType());
            } catch (\Exception $e) {
                // ignore problematic attachment, continue with others
            }
        }

        return $attachments;
    }
} 