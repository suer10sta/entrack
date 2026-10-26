<?php
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$connection=App\Models\SearchConsoleConnection::first();
try {
 $report=app(App\Services\GoogleAnalyticsService::class)->report($connection->manager_id,'overview',28,true);
 echo json_encode(['totals'=>$report['totals'],'meta'=>$report['meta']],JSON_PRETTY_PRINT).PHP_EOL;
} catch (Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $e) {echo 'GA4 status '.$e->getStatusCode().': '.$e->getMessage().PHP_EOL;}
