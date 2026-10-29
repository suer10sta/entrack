<?php

require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->loadEnvironmentFrom('.env.testing');
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
[$script, $schema, $database, $start, $mode, $parent, $clock] = $argv;
if (! preg_match('/^survey_test_[a-f0-9]{16}$/', $schema)
    || ! preg_match('/(_test|_testing)$/', $database)
    || ! $app->environment('testing')
    || config('database.connections.pgsql.database') !== $database
    || ! in_array($mode, ['process', 'answer'], true)) {
    throw new RuntimeException('Refusing unsafe D1 concurrency test target.');
}
config(['database.default' => 'pgsql', 'mail.default' => 'array']);
Illuminate\Support\Facades\DB::statement('SET search_path TO "'.$schema.'"');
Carbon\Carbon::setTestNow(Carbon\Carbon::parse($clock));
$fake = new class($app->make('mail.manager')) extends Illuminate\Support\Testing\Fakes\MailFake
{
    public function send($view, array $data = [], $callback = null)
    {
        usleep(500000);

        return parent::send($view, $data, $callback);
    }
};
Illuminate\Support\Facades\Mail::swap($fake);
while (microtime(true) < (float) $start) {
    usleep(1000);
}
try {
    if ($mode === 'process') {
        $result = $app->make(App\Services\Surveys\D1Lifecycle::class)->process((int) $parent);
    } else {
        Illuminate\Support\Facades\DB::transaction(function () use ($app, $parent) {
            $survey = App\Models\SurveyResponse::lockForUpdate()->findOrFail($parent);
            usleep(500000);
            $app->make(App\Services\Surveys\D1AnswerService::class)->submit($survey, ['q1' => 'connexion'], false);
        });
        $result = 'answered';
    }
} catch (Symfony\Component\HttpKernel\Exception\HttpException $exception) {
    if ($exception->getStatusCode() !== 409) {
        throw $exception;
    }
    $result = 'closed';
}
echo json_encode(['result' => $result, 'emails' => $fake->sent(App\Mail\D1SurveyMail::class)->count()], JSON_THROW_ON_ERROR);
