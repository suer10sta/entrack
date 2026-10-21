<?php

namespace App\Notifications;

use App\Models\Entreprise;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeNotification extends Notification
{
    protected ?Entreprise $entreprise;

    public function __construct(?Entreprise $entreprise = null)
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

        return (new MailMessage)
            ->subject(__('emails.welcome_subject'))
            ->view('emails.welcome', [
                'userName'      => $notifiable->name,
                'entrepriseName' => trim((string) $this->entreprise?->name),
            ])
            ->from($fromAddress, $fromName);
    }
}
