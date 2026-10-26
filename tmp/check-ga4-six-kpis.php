<?php
require __DIR__.'/../vendor/autoload.php';
$app=require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$c=App\Models\SearchConsoleConnection::firstOrFail();
foreach([7,28,90,180] as $days) {
 try {$r=app(App\Services\GoogleAnalyticsService::class)->report($c->manager_id,'kpis',$days,true);echo json_encode(['days'=>$days,'totals'=>$r['totals'],'timezone'=>$r['meta']['timezone'],'cache_hit'=>$r['meta']['cache_hit']]).PHP_EOL;}
 catch(Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $e){echo json_encode(['days'=>$days,'status'=>$e->getStatusCode(),'message'=>$e->getMessage()]).PHP_EOL;}
}
