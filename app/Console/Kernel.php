<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Models\Setting;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $settings = Setting::first();
        if (now() >= $settings->deadline && !in_array($settings->status, ['ended', 'approved'])) {
            $schedule->command('app:process-deadline')->everyMinute()->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/process_deadline_output.log'));
        }

        // Run scheduler: php artisan schedule:work
        // Run individually in console: php artisan app:process-deadline
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
