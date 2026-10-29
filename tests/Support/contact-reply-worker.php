<?php

// Test-only process; no external mail and no application database/schema.
require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->loadEnvironmentFrom('.env.testing');
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
[$script, $schema, $database, $contactId, $managerId, $key, $start] = $argv;
if (! preg_match('/^contact_test_[a-f0-9]{16}$/', $schema)
    || ! preg_match('/(_test|_testing)$/', $database)
    || ! $app->environment('testing')
    || config('database.connections.pgsql.database') !== $database) {
    throw new RuntimeException('Unsafe reply concurrency test target.');
}
config(['database.default' => 'pgsql']);
Illuminate\Support\Facades\DB::statement('SET search_path TO "'.$schema.'"');
while (microtime(true) < (float) $start) {
    usleep(1000);
}
$contact = App\Models\Contact::findOrFail($contactId);
$manager = App\Models\Manager::findOrFail($managerId);
$log = $app->make(App\Services\ContactManagementService::class)->reply($contact, $manager, 'Concurrent answer', $key);
echo json_encode(['log_id' => $log->id], JSON_THROW_ON_ERROR);
