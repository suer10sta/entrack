<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class D3BusinessMail extends Mailable
{
    public function __construct(public string $kind, string $locale)
    {
        $this->locale($locale);
    }

    public function build(): self
    {
        return $this->subject(__('d3-actions.'.$this->kind.'.subject'))->view('emails.d3-business', ['kind' => $this->kind]);
    }
}
