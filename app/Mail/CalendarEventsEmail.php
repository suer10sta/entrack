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
use Barryvdh\DomPDF\Facade\Pdf as PDF;

class CalendarEventsEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $subject;
    public $body;
    public $pdf;
    public $monthName;
    public $extraAttachments = [];

    /**
     * Create a new message instance.
     */
    public function __construct($subject, $body, $pdf, $extraAttachments = [], $monthName = '')
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
        $this->pdf = $pdf;
        $this->monthName = $monthName;
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
            from: new Address($entreprise_email, 'Calendar Events - '.$entreprise->name),
            replyTo: [
                new Address($entreprise_email, 'Calendar Events - '.$entreprise->name)
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
            view: 'admin.calendar.email',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $attachments = [];

        // Add PDF attachment
        if ($this->pdf) {
            try {
                $pdfContent = $this->pdf->output();
                $tempPath = tempnam(sys_get_temp_dir(), 'calendar_events_');
                file_put_contents($tempPath, $pdfContent);
                
                $attachments[] = Attachment::fromPath($tempPath)
                    ->as('calendar-events-' . ($this->monthName ?: date('Y-m')) . '.pdf')
                    ->withMime('application/pdf');
            } catch (\Exception $e) {
                \Log::error('Error attaching PDF to calendar events email: ' . $e->getMessage());
            }
        }

        // Add extra attachments
        foreach ($this->extraAttachments as $uploadedFile) {
            if (!$uploadedFile) {
                continue;
            }
            try {
                $attachments[] = Attachment::fromPath($uploadedFile->getRealPath())
                    ->as($uploadedFile->getClientOriginalName())
                    ->withMime($uploadedFile->getClientMimeType());
            } catch (\Exception $e) {
                \Log::error('Error attaching file to calendar events email: ' . $e->getMessage());
            }
        }

        return $attachments;
    }
}

