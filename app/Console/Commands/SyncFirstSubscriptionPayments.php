<?php

namespace App\Console\Commands;

use App\Models\Entreprise;
use App\Services\Surveys\FirstSubscriptionPayment;
use Illuminate\Console\Command;

class SyncFirstSubscriptionPayments extends Command
{
    protected $signature = 'surveys:sync-first-payments';

    protected $description = 'Retry only confirmed payment receipts whose first invoice history is not yet persisted.';

    public function handle(FirstSubscriptionPayment $payments): int
    {
        $pending = 0;
        $recorded = 0;
        Entreprise::whereNull('first_paid_at')->whereNotNull('first_paid_lookup_customer_id')->chunkById(100, function ($companies) use ($payments, &$pending, &$recorded) {
            foreach ($companies as $company) {
                if ($payments->retryPending($company)) {
                    $recorded++;
                } else {
                    $pending++;
                }
            }
        });
        $this->line(json_encode(['recorded' => $recorded, 'pending' => $pending], JSON_THROW_ON_ERROR));

        return $pending ? self::FAILURE : self::SUCCESS;
    }
}
