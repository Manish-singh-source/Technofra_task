<?php

namespace App\Console\Commands;

use App\Jobs\SendCalendarEventNotification;
use App\Models\CalendarEvent;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TestCalendarNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calendar:test-notification {event_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test calendar event notification by sending immediately for a specific event';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $eventId = $this->argument('event_id');
            
            $event = CalendarEvent::find($eventId);
            
            if (!$event) {
                $this->error("Event with ID {$eventId} not found.");
                return 1;
            }
            
            $this->info("Testing notification for event: {$event->title}");
            $this->info("Event Date: {$event->event_date->format('Y-m-d')}");
            $this->info("Event Time: {$event->event_time->format('H:i')}");
            $this->info("Recipients: {$event->email_recipients}");
            $this->info("Notification Sent: " . ($event->notification_sent ? 'Yes' : 'No'));
            
            if ($event->notification_sent) {
                $this->warn("This event notification has already been sent at {$event->notification_sent_at}");
                
                if ($this->confirm('Do you want to send it again?', false)) {
                    $event->notification_sent = false;
                    $event->notification_sent_at = null;
                    $event->save();
                    $this->info("Reset notification status.");
                } else {
                    return 0;
                }
            }
            
            $this->info("Dispatching notification job...");
            SendCalendarEventNotification::dispatch($event);
            
            $this->info("✓ Notification job dispatched successfully!");
            $this->info("Check your email at: {$event->email_recipients}");
            $this->warn("Make sure queue worker is running: php artisan queue:work");
            
            Log::info("Test notification dispatched for event: {$event->title}");
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
            Log::error("Test notification error: " . $e->getMessage());
            return 1;
        }
    }
}

