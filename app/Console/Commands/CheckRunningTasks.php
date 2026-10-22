<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\EmployeeTimerLimit;

class CheckRunningTasks extends Command
{
    protected $signature = 'tasks:check-running';
    protected $description = 'Stop employee timers that have reached their daily work-hours limit';

    public function handle(EmployeeTimerLimit $limits)
    {
        $limits->synchronizeAll();
        $this->info('Running tasks check completed');
    }
}
