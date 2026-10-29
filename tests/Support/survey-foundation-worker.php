<?php

// Independent test process. Never target the application database/schema.
require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->loadEnvironmentFrom('.env.testing');
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
[$script, $schema, $database, $start, $mode, $parent] = $argv;
if (! preg_match('/^survey_test_[a-f0-9]{16}$/', $schema)
    || ! preg_match('/(_test|_testing)$/', $database)
    || ! $app->environment('testing')
    || config('database.connections.pgsql.database') !== $database
    || ! in_array($mode, ['survey', 'action'], true)) {
    throw new RuntimeException('Refusing unsafe survey concurrency test target.');
}
config(['database.default' => 'pgsql']);
Illuminate\Support\Facades\DB::statement('SET search_path TO "'.$schema.'"');
while (microtime(true) < (float) $start) {
    usleep(1000);
}
try {
    Illuminate\Support\Facades\DB::transaction(function () use ($mode, $parent) {
        if ($mode === 'survey') {
            App\Models\SurveyResponse::create([
                'entreprise_id' => $parent, 'survey_type' => App\Enums\SurveyType::D1,
                'survey_version' => 1, 'email' => 'parallel@example.test',
            ]);
        } else {
            App\Models\SurveyAction::create([
                'survey_id' => $parent, 'action_type' => App\Enums\SurveyActionType::Ticket,
                'action_key' => 'ticket:technical-block:v1',
            ]);
        }
        // Keep the winning transaction open while the other connection competes.
        usleep(500000);
    });
    echo 'created';
} catch (Illuminate\Database\QueryException $exception) {
    if ($exception->getCode() !== '23505') {
        throw $exception;
    }
    echo 'duplicate';
}
