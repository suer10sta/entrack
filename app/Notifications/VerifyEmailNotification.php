<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as LaravelVerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\URL;

class VerifyEmailNotification extends LaravelVerifyEmail
{
    public function toMail($notifiable): MailMessage
    {
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $notifiable->getKey(), 'hash' => sha1($notifiable->getEmailForVerification())]
        );

        return (new MailMessage)
            ->subject('Vérifiez votre adresse email - DalyTrac')
            ->view('emails.verify-email', [
                'userName' => $notifiable->name ?? $notifiable->email,
                'verificationUrl' => $verificationUrl,
            ]);
    }
}
