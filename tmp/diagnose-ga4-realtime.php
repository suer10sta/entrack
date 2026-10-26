<?php
require __DIR__.'/../vendor/autoload.php';
$app=require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Models\SearchConsoleConnection;
$c=SearchConsoleConnection::firstOrFail();
$property=(string)config('analytics.property_id');
$result=['checked_at_utc'=>now('UTC')->toIso8601String(),'property_id'=>$property,'measurement_id'=>config('analytics.measurement_id'),'manager_id'=>$c->manager_id,'connection_count'=>SearchConsoleConnection::count(),'cache_driver'=>config('cache.default'),'cache_hours'=>config('analytics.cache_hours')];
$metrics=['activeUsers','newUsers','sessions','screenPageViews','engagementRate','userEngagementDuration','engagedSessions','eventCount','keyEvents'];
$defs=['overview'=>[[],$metrics],'traffic'=>[['date'],['activeUsers','newUsers','sessions','screenPageViews']],'pages'=>[['pagePath'],['screenPageViews','activeUsers','engagementRate']],'sources'=>[['sessionDefaultChannelGroup','sessionSourceMedium'],['sessions','activeUsers','newUsers']],'campaigns'=>[['sessionCampaignName','sessionSourceMedium','sessionManualAdContent','sessionManualTerm'],['sessions','activeUsers','keyEvents']],'events'=>[['eventName'],['eventCount','totalUsers','keyEvents']],'countries'=>[['country'],['activeUsers','sessions','screenPageViews']],'devices'=>[['deviceCategory'],['activeUsers','sessions','screenPageViews']]];
$keys=[];
foreach($defs as $name=>[$dims,$mets]) {
 $body=['dateRanges'=>[['startDate'=>'28daysAgo','endDate'=>'yesterday']],'dimensions'=>array_map(fn($n)=>['name'=>$n],$dims),'metrics'=>array_map(fn($n)=>['name'=>$n],$mets),'limit'=>$name==='overview'?1:1000];
 if($dims)$body['orderBys']=$name==='traffic'?[['dimension'=>['dimensionName'=>'date']]]:[['metric'=>['metricName'=>$mets[0]],'desc'=>true]];
 $key='ga4:v1:'.hash('sha256',json_encode([$c->manager_id,$c->getRawOriginal('access_token'),$property,$body,now('UTC')->toDateString()]));
 $keys[]=$key;
 $cache=Cache::get($key);
 $result['cache_before'][$name]=['found'=>$cache!==null,'totals'=>$cache['totals']??null,'row_count'=>$cache['meta']['row_count']??null,'fetched_at'=>$cache['meta']['fetched_at']??null];
 if($name==='overview')$overviewBody=$body;
}
$token=app(App\Services\SearchConsoleService::class)->token($c->manager_id);
try {
 $info=Http::timeout(20)->get('https://oauth2.googleapis.com/tokeninfo',['access_token'=>$token]);
 $scopes=explode(' ',(string)$info->json('scope'));
 $result['oauth']=['http'=>$info->status(),'analytics_readonly_present'=>in_array('https://www.googleapis.com/auth/analytics.readonly',$scopes,true),'search_console_readonly_present'=>in_array('https://www.googleapis.com/auth/webmasters.readonly',$scopes,true)];
} catch(Throwable $e){$result['oauth']=['check'=>'unavailable'];}
function callGoogle($url,$body,$token,$get=false) {
 try {$client=Http::withToken($token)->acceptJson()->connectTimeout(5)->timeout(30);$r=$get?$client->get($url):$client->post($url,$body);return ['request'=>$body,'http'=>$r->status(),'raw'=>$r->json()];}
 catch(Throwable $e){return ['error'=>'Connection unavailable'];}
}
$base='https://analyticsdata.googleapis.com/v1beta/properties/'.$property;
$minimal=['dateRanges'=>[['startDate'=>'28daysAgo','endDate'=>'yesterday']],'metrics'=>array_map(fn($n)=>['name'=>$n],array_slice($metrics,0,4))];
$result['realtime']=callGoogle($base.':runRealtimeReport',['metrics'=>[['name'=>'activeUsers']]],$token);
$result['standard_28_days']=callGoogle($base.':runReport',$minimal,$token);
$result['standard_exact_dalytrac']=callGoogle($base.':runReport',$overviewBody,$token);
$inclusive=$minimal;$inclusive['dateRanges'][0]['endDate']='today';
$result['standard_including_today']=callGoogle($base.':runReport',$inclusive,$token);
$today=$minimal;$today['dateRanges']=[['startDate'=>'today','endDate'=>'today']];
$result['standard_today_only']=callGoogle($base.':runReport',$today,$token);
$result['property_details']=callGoogle('https://analyticsadmin.googleapis.com/v1beta/properties/'.$property,[],$token,true);
$result['property_streams']=callGoogle('https://analyticsadmin.googleapis.com/v1beta/properties/'.$property.'/dataStreams',[],$token,true);
$zone=$result['standard_exact_dalytrac']['raw']['metadata']['timeZone']??$result['property_details']['raw']['timeZone']??null;
if($zone)$result['resolved_dates']=['timezone'=>$zone,'today'=>now($zone)->toDateString(),'startDate'=>now($zone)->subDays(28)->toDateString(),'endDate'=>now($zone)->subDay()->toDateString()];
foreach($keys as $key)Cache::forget($key);
$result['cache_entries_targeted']=count($keys);
try {
 $service=app(App\Services\GoogleAnalyticsService::class);
 $result['backend_fresh']=$service->report($c->manager_id,'overview',28,true);
 $result['backend_second_read']=$service->report($c->manager_id,'overview',28,false);
} catch(Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $e){$result['backend_error']=['http'=>$e->getStatusCode(),'message'=>$e->getMessage()];}
file_put_contents(__DIR__.'/ga4-diagnostic-2026-10-26.json',json_encode($result,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));
echo json_encode($result,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE).PHP_EOL;
