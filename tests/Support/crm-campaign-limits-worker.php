<?php

require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->loadEnvironmentFrom('.env.testing');
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
[$script, $schema, $database, $mode, $campaignId] = $argv;
if (! preg_match('/^crm_campaign_test_[a-f0-9]{16}$/', $schema)
    || ! preg_match('/(_test|_testing)$/', $database)
    || ! $app->environment('testing')
    || config('database.connections.pgsql.database') !== $database
    || ! in_array($mode, ['send', 'launch'], true)) {
    throw new RuntimeException('Refusing unsafe CRM campaign limits concurrency target.');
}
config(['database.default' => 'pgsql', 'mail.default' => 'smtp',
    'mail.mailers.smtp.host' => 'smtp.example.test', 'mail.mailers.smtp.username' => 'campaigns@example.test',
    'crm.campaign_batch_size' => 100, 'crm.campaign_interval_ms' => 0, 'crm.campaign_hourly_limit' => 150]);
Illuminate\Support\Facades\DB::statement('SET search_path TO "'.$schema.'"');
// No real transport may be used in this worker.
Illuminate\Support\Facades\Mail::shouldReceive('to')->andReturnUsing(function () {
    return new class
    {
        public function send($mail)
        {
            usleep(200000);

            return (object) ['accepted' => true];
        }
    };
});
if ($mode === 'send') {
    echo $app->make(App\Services\CrmCampaignSender::class)->batch();
} else {
    try {
        $app->make(App\Services\CrmCampaignService::class)->enqueue(App\Models\CrmCampaign::findOrFail($campaignId));
        echo '1';
    } catch (Illuminate\Validation\ValidationException $error) {
        echo '0';
    }
}
