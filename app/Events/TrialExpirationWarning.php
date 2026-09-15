<?php

namespace App\Events;

use App\Models\Entreprise;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TrialExpirationWarning
{
    use Dispatchable, SerializesModels;

    public User $user;
    public Entreprise $entreprise;
    public int $daysRemaining;

    public function __construct(User $user, Entreprise $entreprise, int $daysRemaining)
    {
        $this->user = $user;
        $this->entreprise = $entreprise;
        $this->daysRemaining = $daysRemaining;
    }
}
