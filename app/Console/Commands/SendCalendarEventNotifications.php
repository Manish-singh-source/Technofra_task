<?php

namespace App\Console\Commands;

use App\Jobs\Send10MinReminderNotification;
use App\Jobs\SendEventTimeNotification;
use App\Models\CalendarEvent;
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
    protected $description = 'Check and send calendar event notifications (10-min reminders and event-time notifications)';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Checking for calendar events that need notifications...');

        try {
            $reminder10MinCount = 0;
            $eventTimeCount = 0;
            $failureCount = 0;

            // Check for 10-minute reminders
            $this->info('Checking for 10-minute reminders...');
            $reminderEvents = CalendarEvent::pending10MinReminder()
                ->get()
                ->filter(function ($event) {
                    return $event->shouldSend10MinReminder();
                });

            if ($reminderEvents->isNotEmpty()) {
                $this->info("Found {$reminderEvents->count()} event(s) that need 10-minute reminders.");

                foreach ($reminderEvents as $event) {
                    try {
                        Send10MinReminderNotification::dispatch($event);
                        $this->info("✓ Dispatched 10-min reminder for: {$event->title}");
                        $reminder10MinCount++;
                    } catch (\Exception $e) {
                        $this->error("✗ Failed to dispatch 10-min reminder for {$event->title}: " . $e->getMessage());
                        Log::error("Failed to dispatch 10-min reminder: " . $e->getMessage());
                        $failureCount++;
                    }
                }
            } else {
                $this->info('No events need 10-minute reminders at this time.');
            }

            // Check for event-time notifications
            $this->info('Checking for event-time notifications...');
            $eventTimeEvents = CalendarEvent::pendingEventTimeNotification()
                ->get()
                ->filter(function ($event) {
                    return $event->shouldSendEventTimeNotification();
                });

            if ($eventTimeEvents->isNotEmpty()) {
                $this->info("Found {$eventTimeEvents->count()} event(s) that need event-time notifications.");

                foreach ($eventTimeEvents as $event) {
                    try {
                        SendEventTimeNotification::dispatch($event);
                        $this->info("✓ Dispatched event-time notification for: {$event->title}");
                        $eventTimeCount++;
                    } catch (\Exception $e) {
                        $this->error("✗ Failed to dispatch event-time notification for {$event->title}: " . $e->getMessage());
                        Log::error("Failed to dispatch event-time notification: " . $e->getMessage());
                        $failureCount++;
                    }
                }
            } else {
                $this->info('No events need event-time notifications at this time.');
            }

            // Summary
            $this->newLine();
            $this->info("=== Summary ===");
            $this->info("10-min reminders dispatched: {$reminder10MinCount}");
            $this->info("Event-time notifications dispatched: {$eventTimeCount}");

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
