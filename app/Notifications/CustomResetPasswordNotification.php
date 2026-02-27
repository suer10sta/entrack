<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;

class CustomResetPasswordNotification extends Notification
{
    /**
     * The password reset token.
     *
     * @var string
     */
    public string $token;

    /**
     * Create a notification instance.
     */
    public function __construct(string $token)
    {
        $this->token = $token;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array<int, string>
     */
    public function via($notifiable): array
    {
        return ['mail'];
    }

    /**
     * Build the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable): MailMessage
    {
        $resetUrl = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        // Force a non-empty From header (Symfony Mailer requires From or Sender).
        $fromAddress = config('mail.from.address') ?: 'dessin@bureaumercier.com';
        $fromName = 'DalyTrac';
        // Log for debugging in production to ensure the notification is being built
        \Log::info('Sending password reset email', [
            'to' => $notifiable->getEmailForPasswordReset(),
            'from' => $fromAddress,
            'from_name' => $fromName,
        ]);

        $expire = (int) config('auth.passwords.'.config('auth.defaults.passwords').'.expire');

        $mailMessage = (new MailMessage)
            ->subject(Lang::get('Reset Password Notification'))
            ->view('emails.password-reset', [
                'resetUrl'      => $resetUrl,
                'expireMinutes' => $expire,
                'userName'      => method_exists($notifiable, 'getFullName')
                    ? $notifiable->getFullName()
                    : ($notifiable->name ?? null),
            ]);

        $mailMessage->from($fromAddress, $fromName);

        return $mailMessage;
    }
}


