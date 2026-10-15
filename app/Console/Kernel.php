<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('system:heartbeat')
            ->everyFiveMinutes()
            ->withoutOverlapping(10)
            ->when(fn () => ! app()->environment('testing'));

        $schedule->command('admin:exports-cleanup')
            ->hourly()
            ->withoutOverlapping(10)
            ->when(fn () => ! app()->environment('testing'));

        $schedule->command('activity:purge')
            ->dailyAt('03:20')
            ->withoutOverlapping(10)
            ->when(fn () => ! app()->environment('testing'));

        $schedule->command('email:dispatch-scheduled')->everyMinute()->withoutOverlapping(10)->when(fn () => ! app()->environment('testing'));
        $schedule->command('email:cleanup')->dailyAt('03:40')->withoutOverlapping(10)->when(fn () => ! app()->environment('testing'));
        $schedule->command('automations:dispatch')->everyFiveMinutes()->withoutOverlapping(10)->when(fn () => ! app()->environment('testing'));
        $schedule->command('automations:cleanup')->dailyAt('04:00')->withoutOverlapping(10)->when(fn () => ! app()->environment('testing'));
        $schedule->command('tickets:cleanup')->dailyAt('04:20')->withoutOverlapping(10)->when(fn () => ! app()->environment('testing'));
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
