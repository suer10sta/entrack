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
use App\Models\credit_note;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CreditNoteEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $subject;
    public $body;
    public $creditNoteId;
    public $filePath;
    public $viewData;

    public function __construct($subject, $body, $creditNoteId = null, $filePath = [], $viewData = [])
    {
        $this->subject = $subject;
        $this->body = $body;
        $this->creditNoteId = $creditNoteId;
        $this->filePath = $filePath;
        $this->viewData = $viewData;
    }

    public function envelope(): Envelope
    {
        $entreprise_id = Auth::user()->entreprise_id;
        $entreprise_email = Entreprise::where('id', $entreprise_id)->value('email');
        $entreprise = Entreprise::where('id', $entreprise_id)->first();

        return new Envelope(
            from: new Address($entreprise_email, 'Credit Note-'.$entreprise->name),
            replyTo: [
                new Address($entreprise_email, 'Credit Note-'.$entreprise->name)
            ],
            subject: $this->subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'admin.creditNote.email',
            with: $this->viewData
        );
    }

    public function attachments(): array
    {
        $attachments = [];
        
        if ($this->creditNoteId) {
            $credit_note = credit_note::find($this->creditNoteId);
            $entreprise = Entreprise::where('id', Auth::user()->entreprise_id)->first();
            
            $creditnoteyear = $credit_note->created_at->format('Y');
            $creditnotemonth = $credit_note->created_at->format('m');
            $creditnotey = $credit_note->created_at->format('y');
            
            // Try different possible file paths
            $possiblePaths = [
                // Current format in your system
                'creditNote/' . $entreprise->id . '/' . $creditnoteyear . '/CN-' . $creditnotey . $creditnotemonth . $credit_note->credit_note_number . '.pdf',
                
                // Format for detailed credit notes (NC- prefix)
                'creditNote/' . $entreprise->id . '/' . $creditnoteyear . '/NC-' . $creditnotey . $creditnotemonth . $credit_note->credit_note_number . '.pdf',
                
                // Alternative format without year/month in filename
                'creditNote/' . $entreprise->id . '/' . $creditnoteyear . '/CN-' . $credit_note->credit_note_number . '.pdf',
                
                // Alternative format without year/month in filename with NC prefix
                'creditNote/' . $entreprise->id . '/' . $creditnoteyear . '/NC-' . $credit_note->credit_note_number . '.pdf'
            ];
            
            // Try each path until we find the file
            foreach ($possiblePaths as $path) {
                if (Storage::disk('public')->exists($path)) {
                    $fileName = basename($path);
                    $attachments[] = Attachment::fromStorageDisk('public', $path)
                        ->as($fileName)
                        ->withMime('application/pdf');
                    \Log::info('Credit note PDF attached from: ' . $path);
                    break;
                }
            }
            
            if (empty($attachments)) {
                \Log::error('Credit note PDF not found for credit note ID: ' . $this->creditNoteId);
            }
        }

        // Add any additional files if provided
        foreach ($this->filePath as $file) {
            if (Storage::disk('public')->exists($file)) {
                $attachments[] = Attachment::fromStorageDisk('public', $file);
            }
        }

        return $attachments;
    }
} 
