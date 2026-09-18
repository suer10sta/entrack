<?php

namespace App\Console\Commands;

use App\Models\Entreprise;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SyncStripeSubscriptions extends Command
{
    protected $signature = 'subscriptions:sync {--entreprise= : Restrict the sync to one entreprise id}';

    protected $description = 'Backfill local Cashier subscription rows from Stripe for existing enterprises.';

    public function handle(): int
    {
        $query = Entreprise::query()->whereNotNull('stripe_id');

        if ($entrepriseId = $this->option('entreprise')) {
            $query->where('id', (int) $entrepriseId);
        }

        $synced = 0;
        $skipped = 0;

        foreach ($query->cursor() as $entreprise) {
            try {
                $stripeSubscription = $entreprise->latestStripeSubscriptionFromApi();

                if (! $stripeSubscription) {
                    $skipped++;
                    continue;
                }

                $items = $stripeSubscription->items->data ?? [];
                $firstItem = $items[0] ?? null;
                $isSinglePrice = count($items) === 1;

                $entreprise->subscriptions()->updateOrCreate(
                    ['stripe_id' => (string) $stripeSubscription->id],
                    [
                        'type' => 'default',
                        'stripe_status' => (string) ($stripeSubscription->status ?? ''),
                        'stripe_price' => $isSinglePrice && $firstItem
                            ? ($firstItem->price->id ?? null)
                            : null,
                        'quantity' => $isSinglePrice && $firstItem && isset($firstItem->quantity)
                            ? (int) $firstItem->quantity
                            : null,
                        'trial_ends_at' => !empty($stripeSubscription->trial_end)
                            ? Carbon::createFromTimestamp($stripeSubscription->trial_end)
                            : null,
                        'ends_at' => !empty($stripeSubscription->cancel_at_period_end)
                            ? Carbon::createFromTimestamp($stripeSubscription->current_period_end)
                            : null,
                    ]
                );

                $synced++;
            } catch (\Throwable $e) {
                $this->error("Entreprise {$entreprise->id}: {$e->getMessage()}");
            }
        }

        $this->info("Abonnements synchronisés: {$synced} (sans abonnement Stripe: {$skipped}).");

        return self::SUCCESS;
    }
}