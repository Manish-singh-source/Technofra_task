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
        $schedule->command('todos:send-reminders')->everyMinute();

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
        $schedule->command('amc:send-visit-reminders')->dailyAt('06:00');
        $schedule->command('project-management:send-notifications --days='.((int) config('project_management.notifications.milestone_deadline_days', 2)))
            ->dailyAt('09:00')
            ->withoutOverlapping();
        $schedule->command('project-management:sync-milestone-progress')
            ->hourly()
            ->withoutOverlapping();
        $schedule->command('meta:sync-leads')->hourly()->withoutOverlapping();
        $schedule->command('leads:escalate-overdue')->hourly()->withoutOverlapping();
        $schedule->command('leads:send-alerts')->hourly()->withoutOverlapping();
        $schedule->command('social-media:send-reminders')->hourly()->withoutOverlapping();
        $schedule->call(function () {
            $today = \Carbon\Carbon::today();
            $slot = now()->hour >= 15 ? 'afternoon' : 'morning';

            \App\Models\Order::query()
                ->where('overall_status', 'Open')
                ->where('is_on_hold', false)
                ->where('purchase_status', '!=', 'Received')
                ->whereDate('order_date', '<=', $today)
                ->get()
                ->each(function ($order) use ($today, $slot) {
                    $type = 'Vendor receipt reminder';
                    $reminder = \App\Models\OrderReminder::firstOrCreate(
                        ['order_id' => $order->id, 'user_id' => $order->assigned_user_id, 'reminder_type' => $type, 'sent_on' => $today, 'sent_slot' => $slot],
                        ['reminder_for_date' => $order->vendor_expected_date, 'channel' => 'in_app', 'status' => 'Sent']
                    );

                    if ($reminder->wasRecentlyCreated) {
                        $order->owner?->notify(new \App\Notifications\OrderReminderNotification($order, $type, null));
                    }
                });
        })->twiceDaily(9, 15)->name('order-receipt-reminders')->withoutOverlapping();

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



