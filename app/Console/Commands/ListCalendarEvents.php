<?php

namespace App\Console\Commands;

use App\Models\CalendarEvent;
use Illuminate\Console\Command;

class ListCalendarEvents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calendar:list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all calendar events';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $events = CalendarEvent::with('creator')->orderBy('created_at', 'desc')->get();
        
        if ($events->isEmpty()) {
            $this->warn('No calendar events found.');
            $this->info('Create an event from the dashboard first.');
            return 0;
        }
        
        $this->info("Total Events: " . $events->count());
        $this->line('');
        
        $tableData = [];
        foreach ($events as $event) {
            $tableData[] = [
                'ID' => $event->id,
                'Title' => $event->title,
                'Date' => $event->event_date->format('Y-m-d'),
                'Time' => $event->event_time->format('H:i'),
                'Recipients' => $event->email_recipients,
                'Sent' => $event->notification_sent ? '✓ Yes' : '✗ No',
                'Status' => $event->status ? 'Active' : 'Inactive',
            ];
        }
        
        $this->table(
            ['ID', 'Title', 'Date', 'Time', 'Recipients', 'Sent', 'Status'],
            $tableData
        );
        
        $this->line('');
        $this->info('To test notification for an event, run:');
        $this->comment('php artisan calendar:test-notification {event_id}');
        
        return 0;
    }
}

