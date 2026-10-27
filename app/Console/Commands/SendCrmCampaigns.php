<?php

namespace App\Console\Commands;

use App\Services\CrmCampaignSender;
use Illuminate\Console\Command;

class SendCrmCampaigns extends Command
{
    protected $signature = 'crm:send-campaigns';

    protected $description = 'Process one bounded batch of pending Prospect campaign emails using the existing mailer';

    public function handle(CrmCampaignSender $sender): int
    {
        $this->info($sender->batch().' email(s) accepted by the mailer.');

        return self::SUCCESS;
    }
}
