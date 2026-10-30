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
    || ! in_array($mode, ['process', 'answer', 'actions', 'resume'], true)) {
    throw new RuntimeException('Refusing unsafe D6 concurrency test target.');
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
    if ($mode === 'resume') {
        Illuminate\Support\Facades\DB::transaction(function () use ($parent) {
            App\Models\Entreprise::whereKey($parent)->lockForUpdate()->firstOrFail();
            Illuminate\Support\Facades\DB::table('entreprises')->where('id', $parent)->update(['survey_resumed_at' => now('UTC')->toIso8601String()]);
            echo "locked\n";
            fflush(STDOUT);
            usleep(2000000);
        });
        $result = 'resumed';
    } elseif ($mode === 'process') {
        $result = $app->make(App\Services\Surveys\D6Lifecycle::class)->process((int) $parent);
    } elseif ($mode === 'actions') {
        $result = $app->make(App\Services\Surveys\D6ActionService::class)->processSafely(App\Models\SurveyResponse::findOrFail($parent));
    } else {
        $survey = App\Models\SurveyResponse::findOrFail($parent);
        usleep(500000);
        $app->make(App\Services\Surveys\D6AnswerService::class)->submit($survey, ['q1' => 'Je rencontre un problème technique', 'q2' => 'Probablement', 'q4' => 'Dans un mois'], false);
        $result = 'answered';
    }
} catch (Symfony\Component\HttpKernel\Exception\HttpException $exception) {
    if (! in_array($exception->getStatusCode(), [409, 410], true)) {
        throw $exception;
    }
    $result = 'closed';
}
echo json_encode(['result' => $result, 'emails' => $fake->sent(App\Mail\D6SurveyMail::class)->count()], JSON_THROW_ON_ERROR);
