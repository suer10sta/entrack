<?php
require __DIR__.'/../vendor/autoload.php';
$app=require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$secret=config('analytics.api_secret');
config(['analytics.api_secret'=>null]);
Illuminate\Support\Facades\DB::beginTransaction();
try {
 $service=app(App\Services\AnalyticsBusinessEvents::class);
 $ctx=['granted'=>true,'client_id'=>'123456.789012','session_id'=>(string)time(),'captured_at'=>time()];
 $service->registration(987654321,987654321,$ctx);
 $service->checkout(987654321,$ctx,(object)['id'=>'cs_validation_only','amount_total'=>1900,'currency'=>'eur'],'solo');
 $service->payment(987654321,['id'=>'in_validation_only','subscription'=>'sub_validation_only','billing_reason'=>'subscription_create','paid'=>true,'amount_paid'=>1900,'currency'=>'eur','lines'=>['data'=>[['price'=>['recurring'=>['interval'=>'month']]]]]],'solo');
 $payloads=Illuminate\Support\Facades\DB::table('analytics_business_events')->where('created_at','>=',now()->subMinute())->get()->map(fn($r)=>['name'=>$r->event_name,'payload'=>json_decode(Illuminate\Support\Facades\Crypt::decryptString($r->payload),true)])->all();
} finally { Illuminate\Support\Facades\DB::rollBack(); }
foreach($payloads as $entry) {
 try {
  $body=$entry['payload'];$body['validation_behavior']='ENFORCE_RECOMMENDATIONS';
  $r=Illuminate\Support\Facades\Http::timeout(20)->post('https://www.google-analytics.com/debug/mp/collect?'.http_build_query(['measurement_id'=>config('analytics.measurement_id'),'api_secret'=>$secret]),$body);
  echo json_encode(['event'=>$entry['name'],'http'=>$r->status(),'validationMessages'=>$r->json('validationMessages')]).PHP_EOL;
 } catch(Throwable $e) {echo 'Validation endpoint unavailable; no production event sent.'.PHP_EOL;}
}
