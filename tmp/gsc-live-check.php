<?php
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$c=App\Models\SearchConsoleConnection::first();
$service=app(App\Services\SearchConsoleService::class);
$site='sc-domain:dalytrac.com'; $start='2026-09-26'; $end='2026-10-26';
$oldKey='gsc:'.hash('sha256',json_encode([$c->manager_id,$c->getRawOriginal('access_token'),'overview',$site,$start,$end]));
$old=Illuminate\Support\Facades\Cache::get($oldKey);
echo json_encode(['previous_cache_found'=>$old!==null,'previous_cached_totals'=>$old['totals']??null]).PHP_EOL;
foreach (['final','all'] as $state) {
 try {
  $r=$service->report($c->manager_id,'overview',$site,$start,$end,['data_state'=>$state,'refresh'=>true]);
  echo json_encode(['state'=>$state,'totals'=>$r['totals'],'trace'=>$r['_meta']], JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES).PHP_EOL;
 } catch (Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $e) { echo 'Google status '.$e->getStatusCode().': '.$e->getMessage().PHP_EOL; }
}
