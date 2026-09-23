<?php

namespace App\Console\Commands;

use App\Models\Entreprise;
use App\Notifications\TrialExpirationNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendTrialWarnings extends Command
{
    protected $signature = 'subscriptions:send-trial-warnings {--dry-run : List enterprises that would be notified without sending}';

    protected $description = 'Send trial expiration warning emails to enterprises whose trial ends within 3 days.';

    public function handle(): int
    {
        $threshold = Carbon::now()->addDays(3);

        $enterprises = Entreprise::query()
            ->whereNotNull('trial_ends_at')
            ->where('trial_ends_at', '>', Carbon::now())
            ->where('trial_ends_at', '<=', $threshold)
            ->whereNull('type_compt')
            ->get();

        $sent = 0;
        $skipped = 0;

        foreach ($enterprises as $entreprise) {
            $owner = $entreprise->user()->first();

            if (! $owner) {
                $this->warn("Entreprise {$entreprise->id}: aucun utilisateur, ignorée.");
                $skipped++;
                continue;
            }

            $daysRemaining = (int) ceil(now()->floatDiffInDays($entreprise->trial_ends_at));

            if ($this->option('dry-run')) {
                $this->info("Envoi prévu → {$owner->email} (entreprise {$entreprise->id}): {$daysRemaining} jour(s) restant(s).");
                $sent++;
                continue;
            }

            try {
                $owner->notify(new TrialExpirationNotification($entreprise, $daysRemaining));
                $sent++;
            } catch (\Throwable $e) {
                $this->error("Entreprise {$entreprise->id}: {$e->getMessage()}");
                $skipped++;
            }
        }

        $label = $this->option('dry-run') ? 'seraient notifiées' : 'notifiée(s)';
        $this->info("Entreprises {$label}: {$sent} | ignorée(s): {$skipped}.");

        return self::SUCCESS;
    }
}
