<?php

// Test-only process: never use the application's development/production schema.
require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->loadEnvironmentFrom('.env.testing');
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$schema = $argv[1] ?? '';
$database = $argv[2] ?? '';
if (! preg_match('/^contact_test_[a-f0-9]{16}$/', $schema)
    || ! preg_match('/(_test|_testing)$/', $database)
    || ! $app->environment('testing')
    || config('database.connections.pgsql.database') !== $database) {
    throw new RuntimeException('Refusing unsafe contact concurrency test target.');
}
config(['database.default' => 'pgsql']);
Illuminate\Support\Facades\DB::statement('SET search_path TO "'.$schema.'"');
$start = (float) ($argv[3] ?? 0);
while (microtime(true) < $start) {
    usleep(1000);
}
$numbers = [];
for ($i = 0; $i < 10; $i++) {
    $numbers[] = $app->make(App\Services\TicketService::class)->create(
        name: 'Concurrent visitor', email: 'parallel@example.test', subject: 'Parallel', message: 'Test only',
    )->ticket_num;
}
echo json_encode($numbers, JSON_THROW_ON_ERROR);
