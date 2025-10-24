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
use mikehaertl\pdftk\Pdf;

use App\Models\Entreprise;
use Illuminate\Support\Facades\Auth;
use App\Models\devis_history;
use Illuminate\Support\Facades\Storage;


class DevisEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $subject;
    public $body;

    public $devisId;

    public $filePath;

    public $entreprise;
    

    public function __construct($subject, $body, $devisId=null, $filePath = null)
    {
        $this->subject = $subject;
        $this->body = $body;
        $this->devisId = $devisId;
        $this->filePath = $filePath; // This will now be the path to the merged PDF

    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $entreprise_id = Auth::user()->entreprise_id;
        $entreprise_email = Entreprise::where('id', $entreprise_id)->value('email');
        $entreprise = Entreprise::where('id', $entreprise_id)->first();
        return new Envelope(
            from: new Address($entreprise_email, 'Devis-'.$entreprise->name),
            replyTo: [
                new Address($entreprise_email, 'Devis-'.$entreprise->name)
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
            view: 'admin.invoices.emailTest',
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
        
        // Attach the merged PDF if the filePath is provided
        if ($this->filePath && file_exists($this->filePath)) {
            try {
                // Fetch devis to get the devis number for the filename
                $devis = devis_history::find($this->devisId);
                $entreprise = Entreprise::where('id', Auth::user()->entreprise_id)->first();
                $devisNumber = $devis ? $devis->devis_number : 'devis_attachment'; // Fallback filename
                $attachmentFilename = $entreprise->devis_prefix . '-' . $devisNumber . '.pdf';

                $attachments[] = Attachment::fromPath($this->filePath)
                    ->as($attachmentFilename)
                    ->withMime(mime_content_type($this->filePath) ?: 'application/octet-stream');
                
                \Log::info('Attached merged PDF to email.', ['path' => $this->filePath]);
            } catch (\Exception $e) {
                \Log::error('Error attaching merged PDF to email: ' . $e->getMessage(), ['path' => $this->filePath, 'trace' => $e->getTraceAsString()]);
                // Handle error, e.g., attach nothing or a default file
            }
        } else if ($this->devisId) {
            // If no merged file path, fall back to attaching the original devis from DO Spaces
            $devis = devis_history::find($this->devisId);
            if ($devis) {
                $entreprise = Entreprise::where('id', Auth::user()->entreprise_id)->first();
                $devisPath = 'devis/' . $entreprise->id . '/' . $devis->devis_number . '.pdf';
                 try {
                     if (Storage::disk('do_spaces')->exists($devisPath)) {
                         $attachments[] = Attachment::fromStorageDisk('do_spaces', $devisPath)
                             ->as($devis->devis_number . '.pdf')
                             ->withMime('application/pdf');
                         
                         \Log::info('Attached original devis PDF from storage as fallback.', ['path' => $devisPath]);
                     } else {
                         \Log::warning('Original Devis PDF not found in DigitalOcean Spaces for fallback attachment:', ['path' => $devisPath]);
                     }
                 } catch (\Exception $e) {
                     \Log::error('Error fetching original devis PDF from storage for fallback attachment: ' . $e->getMessage(), ['path' => $devisPath, 'trace' => $e->getTraceAsString()]);
                 }
            } else {
                \Log::warning('Devis not found for fallback attachment.', ['devis_id' => $this->devisId]);
            }
        } else {
            \Log::warning('No file path or devis ID provided for attachment.');
        }
        
        return $attachments;
    }
}