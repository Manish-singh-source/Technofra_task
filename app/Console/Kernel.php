<?php

namespace App\Console;

use App\Models\Setting;
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
        // Check notifications every minute so selected event time is not missed.
        $schedule->command('calendar:send-notifications')->everyMinute();

        $dailyNotificationTime = '16:00';
        try {
            $configuredTime = (string) Setting::get('renewal_notification_time', '16:00');
            if (preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $configuredTime)) {
                $dailyNotificationTime = $configuredTime;
            }
        } catch (\Throwable $e) {
            // Keep default schedule time if settings table is not yet available.
        }

        $schedule->command('notifications:send-daily')->dailyAt($dailyNotificationTime);

        // $schedule->command('inspire')->hourly();
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
