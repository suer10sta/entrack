<?php

require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->loadEnvironmentFrom('.env.testing');
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
[$script, $schema, $database, $mode, $token] = $argv;
if (! preg_match('/^crm_unsubscribe_test_[a-f0-9]{16}$/', $schema)
    || ! preg_match('/(_test|_testing)$/', $database)
    || ! $app->environment('testing')
    || config('database.connections.pgsql.database') !== $database
    || ! in_array($mode, ['confirm', 'hold'], true)) {
    throw new RuntimeException('Refusing unsafe CRM unsubscribe concurrency target.');
}
config(['database.default' => 'pgsql', 'mail.default' => 'array']);
Illuminate\Support\Facades\DB::statement('SET search_path TO "'.$schema.'"');
$unsubscribe = $app->make(App\Services\CrmCampaignUnsubscribe::class);
if ($mode === 'hold') {
    [$recipient] = $unsubscribe->resolve($token);
    $app->make(App\Services\CrmCampaignConsent::class)->withRecipient($recipient, function () {
        echo "locked\n";
        flush();
        usleep(800000);
    });
} else {
    $unsubscribe->confirm($token);
}
echo 'ok';
