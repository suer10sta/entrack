<?php

require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->loadEnvironmentFrom('.env.testing');
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
[$script, $schema, $database, $start, $surveyId] = $argv;
if (! preg_match('/^survey_test_[a-f0-9]{16}$/', $schema)
    || ! preg_match('/(_test|_testing)$/', $database)
    || ! $app->environment('testing')
    || config('database.connections.pgsql.database') !== $database) {
    throw new RuntimeException('Refusing unsafe D1 action concurrency target.');
}
config(['database.default' => 'pgsql', 'mail.default' => 'array']);
Illuminate\Support\Facades\DB::statement('SET search_path TO "'.$schema.'"');
Illuminate\Support\Facades\Mail::fake();
while (microtime(true) < (float) $start) {
    usleep(1000);
}
$survey = App\Models\SurveyResponse::findOrFail($surveyId);
$ok = $app->make(App\Services\Surveys\D1ActionService::class)->processSafely($survey);
echo json_encode(['ok' => $ok, 'emails' => Illuminate\Support\Facades\Mail::sent(App\Mail\D1AccountWaitingMail::class)->count()], JSON_THROW_ON_ERROR);
