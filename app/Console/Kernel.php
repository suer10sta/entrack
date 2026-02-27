<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use App\Http\Controllers\admin\tasksController;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    /**
     * The recipient email for failure notifications.
     */
    //private const RECIPIENT_EMAIL = 'dessin@bureaumercier.com';

    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\SendDailyTasksReport::class,
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        try {
            // Ensure storage directory exists and is writable
            if (!file_exists(storage_path('app/public'))) {
                mkdir(storage_path('app/public'), 0755, true);
            }

            $schedule->command('reports:send-daily-tasks')
                    ->dailyAt('20:00') // daily at 22:00 Brussels time
                    //->everyMinute()  
                    //->timezone('Europe/Brussels')
                    ->timezone('Africa/Casablanca')
                    ->before(function () {
                        Log::info('Starting daily reports task', [
                            'time' => now()->format('Y-m-d H:i:s'),
                            'timezone' => config('app.timezone'),
                        ]);
                    })
                    ->after(function () {
                        // Cleanup any leftover files
                        $pattern = storage_path('app/public/*_report_*.pdf');
                        array_map('unlink', glob($pattern));
                        
                        Log::info('Completed daily reports task', [
                            'time' => now()->format('Y-m-d H:i:s'),
                        ]);
                    })
                    ->onFailure(function ($command, $exitCode) {
                        Log::error('Daily reports task failed', [
                            'command' => $command,
                            'exitCode' => $exitCode,
                            'time' => now()->format('Y-m-d H:i:s'),
                            'storage_path' => storage_path(),
                        ]);
                    })
                    ->appendOutputTo(storage_path('logs/scheduler.log'));

                    // Check running tasks every 1 minutes
                    $schedule->command('tasks:check-running')
                    ->everyMinute()
                    ->withoutOverlapping();

        } catch (\Exception $e) {
            Log::error('Error in schedule configuration', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
        }
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
