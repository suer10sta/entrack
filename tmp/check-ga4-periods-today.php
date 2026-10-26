<?php
require __DIR__.'/../vendor/autoload.php';
$app=require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\Http;
$trace=[];
Http::globalRequestMiddleware(function($request) use (&$trace){if($request->getUri()->getHost()==='analyticsdata.googleapis.com')$trace[]=['path'=>$request->getUri()->getPath(),'body'=>json_decode((string)$request->getBody(),true)];return $request;});
$c=App\Models\SearchConsoleConnection::firstOrFail();
$service=app(App\Services\GoogleAnalyticsService::class);
$result=['checked_at'=>now('UTC')->toIso8601String(),'property_id'=>config('analytics.property_id'),'periods'=>[]];
foreach([7,28,90,180] as $days){
 foreach(['overview','traffic','pages','sources','campaigns','events','countries','devices'] as $report){
  $before=count($trace);
  try{$data=$service->report($c->manager_id,$report,$days,true);$result['periods'][$days]['reports'][$report]=['requests'=>array_slice($trace,$before),'response'=>$data];}
  catch(Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $e){$result['periods'][$days]['reports'][$report]=['status'=>$e->getStatusCode(),'message'=>$e->getMessage()];}
 }
 $overview=$result['periods'][$days]['reports']['overview']['response']??[];
 $zone=$overview['meta']['timezone']??'UTC';
 $cache=$service->report($c->manager_id,'overview',$days,false);
 echo json_encode(['days'=>$days,'request'=>$result['periods'][$days]['reports']['overview']['requests']??[],'dates'=>['start'=>now($zone)->subDays($days)->toDateString(),'end'=>now($zone)->toDateString(),'timezone'=>$zone], 'totals'=>$overview['totals']??null,'fresh_cache_hit'=>$overview['meta']['cache_hit']??null,'next_read_cache_hit'=>$cache['meta']['cache_hit']??null,'sections'=>array_map(fn($r)=>isset($r['response'])?['rows'=>count($r['response']['rows']),'days'=>$r['response']['meta']['days']]:$r,$result['periods'][$days]['reports'])],JSON_UNESCAPED_SLASHES).PHP_EOL;
}
file_put_contents(__DIR__.'/ga4-periods-including-today.json',json_encode($result,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));
