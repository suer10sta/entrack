<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Attachment;
use App\Models\Entreprise;
use App\Models\invoices_history;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class InvoicesEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $subject;
    public $body;
    public $invoiceId;
    public $filePath;
    public $reminder;

    public function __construct($subject, $body, $invoiceId = null, $filePath = [], $reminder = null)
    {
        $this->subject = $subject;
        $this->body = $body;
        $this->invoiceId = $invoiceId;
        $this->filePath = $filePath;
        $this->reminder = $reminder;
    }

    public function envelope(): Envelope
    {
        $entrepriseId = Auth::user()?->entreprise_id;
        $entreprise = $entrepriseId ? Entreprise::where('id', $entrepriseId)->first() : null;

        return new Envelope(
            from: CompanySender::from($entreprise),
            replyTo: CompanySender::replyTo($entreprise),
            subject: $this->subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'admin.invoices.emailTest',
        );
    }

    public function attachments(): array
    {
        $attachments = [];
        $user_enterprise = Auth::user()?->entreprise_id;
        $entreprise = $user_enterprise ? Entreprise::where('id', $user_enterprise)->first() : null;

        // For the main invoice (now handles merged PDFs as well)
        if ($this->invoiceId !== null && !$this->reminder) {
            if (! $entreprise) {
                return $attachments;
            }
            $invoice = invoices_history::find($this->invoiceId);
            $year = $invoice->created_at->format('Y'); 
            $year_forname = $invoice->created_at->format('y');
            $month = $invoice->created_at->format('m');
            
            // Check if the path starts with 'merged_' which indicates a merged PDF
            if (!empty($this->filePath) && is_array($this->filePath) && isset($this->filePath[0])) {
                $pdfPath = Storage::disk('public')->url($this->filePath[0]);
                $customFilename = "{$entreprise->invoice_prefix}-{$year_forname}{$month}{$invoice->number}.pdf";
                $attachments[] = Attachment::fromPath($pdfPath)->as($customFilename);
            } else {
                // Original single PDF handling
                $pdfPath = Storage::disk('public')->url("invoices/{$entreprise->id}/{$year}/{$entreprise->invoice_prefix}-{$invoice->number}.pdf");
                $customFilename = "{$entreprise->invoice_prefix}-{$year_forname}{$month}{$invoice->number}.pdf";
                $attachments[] = Attachment::fromPath($pdfPath)->as($customFilename);
            }
        }

        // NEW: Direct attachment mode (used by subscription invoices)
        // When there's no `invoices_history` row, we still want to attach the provided PDF.
        if ($this->invoiceId === null && $this->reminder === null && !empty($this->filePath) && is_array($this->filePath) && isset($this->filePath[0])) {
            $disk = Storage::disk('public');

            foreach ($this->filePath as $relativePath) {
                if (!is_string($relativePath) || $relativePath === '') {
                    continue;
                }
                if (! $disk->exists($relativePath)) {
                    continue;
                }

                $absolutePath = $disk->path($relativePath);
                $customFilename = basename((string) $relativePath);
                $attachments[] = Attachment::fromPath($absolutePath)->as($customFilename);
            }
        }

        // For reminder
        if ($this->reminder !== null && $this->invoiceId !== null) {
            $invoice = invoices_history::find($this->invoiceId);
            $year = $invoice->created_at->format('Y'); 
            $year_forname = $invoice->created_at->format('y');
            $month = $invoice->created_at->format('m');
            $reminderPath = Storage::disk('public')->url("invoices/{$entreprise->id}/{$year}/Reminder-{$entreprise->invoice_prefix}-{$invoice->number}.pdf");
            $customFilename = "Reminder-{$entreprise->invoice_prefix}-{$year_forname}{$month}{$invoice->number}.pdf";
        
            $attachments[] = Attachment::fromPath($reminderPath)->as($customFilename);
        }

        return $attachments;
    }
}
