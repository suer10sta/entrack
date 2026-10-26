<?php
require __DIR__.'/../vendor/autoload.php';
$app=require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$c=App\Models\SearchConsoleConnection::firstOrFail();
$token=app(App\Services\SearchConsoleService::class)->token($c->manager_id);
$body=['dateRanges'=>[['startDate'=>'today','endDate'=>'today']],'dimensions'=>[['name'=>'streamId'],['name'=>'hostName']],'metrics'=>[['name'=>'activeUsers']]];
try {
 $response=Illuminate\Support\Facades\Http::withToken($token)->timeout(20)->post('https://analyticsdata.googleapis.com/v1beta/properties/'.config('analytics.property_id').':runReport',$body);
 $result=['request'=>$body,'http'=>$response->status(),'raw'=>$response->json()];
 $file=__DIR__.'/ga4-diagnostic-2026-10-26.json';$all=json_decode(file_get_contents($file),true);$all['stream_with_data_today']=$result;file_put_contents($file,json_encode($all,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));
 echo json_encode($result,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES).PHP_EOL;
} catch(Throwable $e){echo 'Stream diagnostic unavailable'.PHP_EOL;}
