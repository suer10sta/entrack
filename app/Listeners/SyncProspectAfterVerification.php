<?php

namespace App\Listeners;

use App\Models\User;
use App\Services\CrmRegistrationSync;
use Illuminate\Auth\Events\Verified;

class SyncProspectAfterVerification
{
    public function __construct(private CrmRegistrationSync $sync) {}

    public function handle(Verified $event): void
    {
        if ($event->user instanceof User) {
            $this->sync->verified($event->user);
        }
    }
}
