<?php

namespace App\Console\Commands;

use App\Models\CalendarEvent;
use App\Jobs\SendCalendarReminderNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendCalendarEventNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calendar:send-notifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and send calendar reminder notifications';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Checking for calendar reminders...');

        try {
            $now = now();
            $windowCounts = [
                CalendarEvent::REMINDER_WINDOW_DAY_BEFORE => 0,
                CalendarEvent::REMINDER_WINDOW_DAY_OF_6AM => 0,
                CalendarEvent::REMINDER_WINDOW_ONE_HOUR_BEFORE => 0,
            ];
            $failureCount = 0;

            $events = CalendarEvent::active()->get();

            foreach ($events as $event) {
                $dueWindows = $event->getDueReminderWindows($now);

                foreach ($dueWindows as $window) {
                    try {
                        SendCalendarReminderNotification::dispatch($event, $window);
                        $windowCounts[$window]++;
                        $this->info(sprintf(
                            'Dispatched %s reminder for: %s',
                            str_replace('_', ' ', $window),
                            $event->title
                        ));
                    } catch (\Throwable $exception) {
                        $this->error(sprintf(
                            'Failed to dispatch %s reminder for %s: %s',
                            str_replace('_', ' ', $window),
                            $event->title,
                            $exception->getMessage()
                        ));
                        Log::error('Failed to dispatch calendar reminder', [
                            'event_id' => $event->id,
                            'window' => $window,
                            'error' => $exception->getMessage(),
                        ]);
                        $failureCount++;
                    }
                }
            }

            $this->newLine();
            $this->info('=== Summary ===');
            foreach ($windowCounts as $window => $count) {
                $this->info(sprintf('%s reminders dispatched: %d', str_replace('_', ' ', $window), $count));
            }

            if ($failureCount > 0) {
                $this->warn("Failed dispatches: {$failureCount}");
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Error checking calendar events: ' . $e->getMessage());
            Log::error('Error in SendCalendarEventNotifications command: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
