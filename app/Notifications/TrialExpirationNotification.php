<?php

namespace App\Notifications;

use App\Models\Entreprise;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TrialExpirationNotification extends Notification
{
    protected Entreprise $entreprise;
    protected int $daysRemaining;

    public function __construct(Entreprise $entreprise, int $daysRemaining)
    {
        $this->entreprise = $entreprise;
        $this->daysRemaining = $daysRemaining;
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $fromAddress = config('mail.from.address') ?: 'dessin@bureaumercier.com';
        $fromName = config('mail.from.name') ?: 'DalyTrac';

        $upgradeUrl = route('profile.upgrade');

        return (new MailMessage)
            ->subject('Votre essai expire bientôt - DalyTrac')
            ->view('emails.trial_expiration', [
                'userName'       => $notifiable->name,
                'entrepriseName' => $this->entreprise->name,
                'daysRemaining'  => $this->daysRemaining,
                'trialEndsAt'    => $this->entreprise->trial_ends_at->format('d/m/Y'),
                'upgradeUrl'     => $upgradeUrl,
            ])
            ->from($fromAddress, $fromName);
    }
}
