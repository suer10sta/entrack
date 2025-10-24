<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\admin\tasksController;

class CheckRunningTasks extends Command
{
    protected $signature = 'tasks:check-running';
    protected $description = 'Check and update running tasks every 15 minutes';

    public function handle()
    {
        $tasksController = new tasksController();
        $tasksController->checkTasksAndTime();
        $this->info('Running tasks check completed');
    }
} 
