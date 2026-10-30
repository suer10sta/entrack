<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

app()->afterResolving(Schedule::class, static function (Schedule $schedule) {
    $schedule->command('surveys:sync-first-payments')->hourly()->withoutOverlapping(55)
        ->appendOutputTo(storage_path('logs/scheduler.log'));
    $schedule->command('surveys:process-d6')->hourly()->withoutOverlapping(55)
        ->appendOutputTo(storage_path('logs/scheduler.log'));
    $schedule->command('surveys:process-d5')->hourly()->withoutOverlapping(55)
        ->appendOutputTo(storage_path('logs/scheduler.log'));
    $schedule->command('surveys:process-d4')->hourly()->withoutOverlapping(55)
        ->appendOutputTo(storage_path('logs/scheduler.log'));
    $schedule->command('surveys:process-d3')->hourly()->withoutOverlapping(55)
        ->appendOutputTo(storage_path('logs/scheduler.log'));
    $schedule->command('surveys:process-d2')->hourly()->withoutOverlapping(55)
        ->appendOutputTo(storage_path('logs/scheduler.log'));
    $schedule->command('surveys:process-d1')->hourly()->withoutOverlapping(55)
        ->appendOutputTo(storage_path('logs/scheduler.log'));
});
