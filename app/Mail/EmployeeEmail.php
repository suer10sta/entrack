<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Http\UploadedFile;

class EmployeeEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $subject;
    public $body;
    public $employeeAttachments;

    /**
     * Create a new message instance.
     */
    public function __construct($subject, $body, $employeeAttachments = [])
    {
        $this->subject = $subject;
        $this->body = $body;
        // Normalize attachments to always be an array
        if (empty($employeeAttachments)) {
            $this->employeeAttachments = [];
        } elseif (is_array($employeeAttachments)) {
            $this->employeeAttachments = $employeeAttachments;
        } else {
            $this->employeeAttachments = [$employeeAttachments];
        }
        \Log::info('EmployeeEmail::__construct - Attachments after normalization', ['attachments' => $this->employeeAttachments]); 
    }

    public function build()
    {
        $entreprise_id = auth()->user()->entreprise_id;
        $entreprise = \App\Models\Entreprise::where('id', $entreprise_id)->first();
        CompanySender::apply($this, $entreprise);

        $email = $this->subject($this->subject)
            ->view('emails.employee-email')
            ->with(['body' => $this->body]);

        if (is_array($this->employeeAttachments) && !empty($this->employeeAttachments)) {
            foreach ($this->employeeAttachments as $attachment) {
                if ($attachment instanceof \Illuminate\Http\UploadedFile) {
                    $email->attach($attachment->getRealPath(), [
                        'as' => $attachment->getClientOriginalName(),
                        'mime' => $attachment->getMimeType(),
                    ]);
                }
            }
        }

        return $email;
    }
} 
