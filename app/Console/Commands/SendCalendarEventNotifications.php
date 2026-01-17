<?php

namespace App\Console\Commands;

use App\Jobs\SendCalendarEventNotification;
use App\Models\CalendarEvent;
use Carbon\Carbon;
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
    protected $description = 'Check and send calendar event notifications for scheduled events';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Checking for calendar events that need notifications...');

        try {
            $now = Carbon::now();

            // Get all active events that haven't sent notifications yet
            $events = CalendarEvent::pendingNotification()
                ->get()
                ->filter(function ($event) {
                    return $event->shouldSendNotification();
                });

            if ($events->isEmpty()) {
                $this->info('No events found that need notifications.');
                return Command::SUCCESS;
            }

            $this->info("Found {$events->count()} event(s) that need notifications.");

            $successCount = 0;
            $failureCount = 0;

            foreach ($events as $event) {
                try {
                    // Dispatch the job to send notification
                    SendCalendarEventNotification::dispatch($event);

                    $this->info("Dispatched notification job for event: {$event->title}");
                    $successCount++;
                } catch (\Exception $e) {
                    $this->error("Failed to dispatch notification for event {$event->title}: " . $e->getMessage());
                    Log::error("Failed to dispatch calendar event notification: " . $e->getMessage());
                    $failureCount++;
                }
            }

            $this->info("Successfully dispatched {$successCount} notification(s).");

            if ($failureCount > 0) {
                $this->warn("Failed to dispatch {$failureCount} notification(s).");
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Error checking calendar events: ' . $e->getMessage());
            Log::error('Error in SendCalendarEventNotifications command: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
