<?php
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$c=App\Models\SearchConsoleConnection::first();
$token=app(App\Services\SearchConsoleService::class)->token($c->manager_id);
$response=Illuminate\Support\Facades\Http::withToken($token)->timeout(20)->post('https://analyticsdata.googleapis.com/v1beta/properties/'.config('analytics.property_id').':runReport',['dateRanges'=>[['startDate'=>'28daysAgo','endDate'=>'yesterday']],'metrics'=>[['name'=>'activeUsers']]]);
echo json_encode(['status'=>$response->status(),'message'=>$response->json('error.message'),'error_status'=>$response->json('error.status'),'reasons'=>array_column($response->json('error.details')??[],'reason')]).PHP_EOL;
