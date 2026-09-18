<?php

namespace App\Notifications;

use App\Models\Entreprise;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeNotification extends Notification
{
    protected Entreprise $entreprise;

    public function __construct(Entreprise $entreprise)
    {
        $this->entreprise = $entreprise;
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $fromAddress = config('mail.from.address') ?: 'dessin@bureaumercier.com';
        $fromName = config('mail.from.name') ?: 'DalyTrac';

        $trialDays = $this->entreprise->trial_ends_at
            ? (int) ceil(now()->floatDiffInDays($this->entreprise->trial_ends_at))
            : 15;

        return (new MailMessage)
            ->subject('Bienvenue sur DalyTrac !')
            ->view('emails.welcome', [
                'userName'      => $notifiable->name,
                'entrepriseName' => $this->entreprise->name,
                'trialDays'     => $trialDays,
            ])
            ->from($fromAddress, $fromName);
    }
}
