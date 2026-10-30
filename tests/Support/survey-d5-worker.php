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
    || ! in_array($mode, ['process', 'answer', 'actions', 'payment'], true)) {
    throw new RuntimeException('Refusing unsafe D5 concurrency test target.');
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
    if ($mode === 'payment') {
        $company = App\Models\Entreprise::findOrFail($parent);
        $invoice = ['object' => 'invoice', 'id' => 'in_concurrent_first', 'customer' => $company->stripe_id, 'subscription' => 'sub_first', 'status' => 'paid', 'amount_paid' => 2900, 'status_transitions' => ['paid_at' => now('UTC')->subDays(7)->timestamp]];
        $invoices = Mockery::mock();
        $invoices->shouldReceive('all')->andReturnUsing(function () use ($invoice) {
            usleep(400000);

            return Stripe\Collection::constructFrom(['object' => 'list', 'data' => [$invoice], 'has_more' => false]);
        });
        $client = Mockery::mock(Stripe\StripeClient::class);
        $client->shouldReceive('getService')->with('invoices')->andReturn($invoices);
        $app->bind(Stripe\StripeClient::class, fn () => $client);
        $app->make(App\Services\Surveys\FirstSubscriptionPayment::class)->fromInvoice($company, $invoice);
        $result = $company->fresh()->first_paid_invoice_id;
    } elseif ($mode === 'process') {
        $result = $app->make(App\Services\Surveys\D5Lifecycle::class)->process((int) $parent);
    } elseif ($mode === 'actions') {
        $result = $app->make(App\Services\Surveys\D5ActionService::class)->processSafely(App\Models\SurveyResponse::findOrFail($parent));
    } else {
        Illuminate\Support\Facades\DB::transaction(function () use ($app, $parent) {
            $survey = App\Models\SurveyResponse::lockForUpdate()->findOrFail($parent);
            usleep(500000);
            $app->make(App\Services\Surveys\D5AnswerService::class)->submit($survey, ['q1' => 'Le gain de temps', 'q2' => 'Non, à cause d’un problème technique', 'q3' => 'Il me manque des possibilités'], false);
        });
        $result = 'answered';
    }
} catch (Symfony\Component\HttpKernel\Exception\HttpException $exception) {
    if ($exception->getStatusCode() !== 409) {
        throw $exception;
    }
    $result = 'closed';
}
echo json_encode(['result' => $result, 'emails' => $fake->sent(App\Mail\D5SurveyMail::class)->count()], JSON_THROW_ON_ERROR);
