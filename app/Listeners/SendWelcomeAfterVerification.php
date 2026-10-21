<?php

namespace App\Listeners;

use App\Models\User;
use App\Notifications\WelcomeNotification;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Log;

class SendWelcomeAfterVerification
{
    public function handle(Verified $event): void
    {
        if (!$event->user instanceof User || !$event->user->hasVerifiedEmail()) return;

        $userId = $event->user->getKey();
        $locale = $event->user->locale ?: app()->getLocale();
        if (!in_array($locale, ['fr', 'en', 'nl', 'es'], true)) $locale = 'fr';

        // Match registration's existing mail dispatch pattern: deliver after
        // the verification redirect, without requiring a queue worker.
        $handled = false;
        app()->terminating(function () use ($userId, $locale, &$handled) {
            if ($handled) return;
            $handled = true;
            $user = User::with('entreprise')->find($userId);
            if (!$user || !$user->hasVerifiedEmail()) return;
            try {
                $user->notify((new WelcomeNotification($user->entreprise))->locale($locale));
            } catch (\Throwable $error) {
                Log::warning('Failed to send welcome notification after verification', [
                    'user_id' => $userId, 'error' => $error->getMessage(),
                ]);
            }
        });
    }
}
