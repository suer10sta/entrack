<?php

namespace App\Listeners;

use App\Events\TrialExpirationWarning;
use App\Notifications\TrialExpirationNotification;
use Illuminate\Support\Facades\Log;

class SendTrialExpirationNotification
{
    public function handle(TrialExpirationWarning $event): void
    {
        try {
            $event->user->notify(
                new TrialExpirationNotification($event->entreprise, $event->daysRemaining)
            );
        } catch (\Throwable $e) {
            Log::warning('Failed to send trial expiration notification', [
                'user_id' => $event->user->id,
                'error'   => $e->getMessage(),
            ]);
        }
    }
}
