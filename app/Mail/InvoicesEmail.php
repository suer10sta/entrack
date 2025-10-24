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
        $entreprise_id = Auth::user()->entreprise_id;
        $entreprise_email = Entreprise::where('id', $entreprise_id)->value('email');
        $entreprise = Entreprise::where('id', $entreprise_id)->first();

        return new Envelope(
            from: new Address($entreprise_email, 'Invoice-'.$entreprise->name),
            replyTo: [
                new Address($entreprise_email, 'Invoice-'.$entreprise->name)
            ],
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
        $user_enterprise = Auth::user()->entreprise_id;
        $entreprise = Entreprise::where('id', $user_enterprise)->first();

        // For the main invoice (now handles merged PDFs as well)
        if ($this->invoiceId !== null && !$this->reminder) {
            $invoice = invoices_history::find($this->invoiceId);
            $year = $invoice->created_at->format('Y'); 
            $year_forname = $invoice->created_at->format('y');
            $month = $invoice->created_at->format('m');
            
            // Check if the path starts with 'merged_' which indicates a merged PDF
            if (!empty($this->filePath) && is_array($this->filePath) && isset($this->filePath[0])) {
                $pdfPath = Storage::disk('do_spaces')->url($this->filePath[0]);
                $customFilename = "{$entreprise->invoice_prefix}-{$year_forname}{$month}{$invoice->number}.pdf";
                $attachments[] = Attachment::fromPath($pdfPath)->as($customFilename);
            } else {
                // Original single PDF handling
                $pdfPath = Storage::disk('do_spaces')->url("invoices/{$entreprise->id}/{$year}/{$entreprise->invoice_prefix}-{$invoice->number}.pdf");
                $customFilename = "{$entreprise->invoice_prefix}-{$year_forname}{$month}{$invoice->number}.pdf";
                $attachments[] = Attachment::fromPath($pdfPath)->as($customFilename);
            }
        }

        // For reminder
        if ($this->reminder !== null && $this->invoiceId !== null) {
            $invoice = invoices_history::find($this->invoiceId);
            $year = $invoice->created_at->format('Y'); 
            $year_forname = $invoice->created_at->format('y');
            $month = $invoice->created_at->format('m');
            $reminderPath = Storage::disk('do_spaces')->url("invoices/{$entreprise->id}/{$year}/Reminder-{$entreprise->invoice_prefix}-{$invoice->number}.pdf");
            $customFilename = "Reminder-{$entreprise->invoice_prefix}-{$year_forname}{$month}{$invoice->number}.pdf";
        
            $attachments[] = Attachment::fromPath($reminderPath)->as($customFilename);
        }

        return $attachments;
    }
}