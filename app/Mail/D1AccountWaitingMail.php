<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class D1AccountWaitingMail extends Mailable
{
    public function __construct(string $locale)
    {
        $this->locale($locale);
    }

    public function build(): self
    {
        return $this->subject(__('d1-actions.subject'))->view('emails.d1-account-waiting');
    }
}
