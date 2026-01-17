<?php

namespace App\Jobs;

use App\Mail\CalendarEventMail;
use App\Models\CalendarEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\{Mail, Log, DB};
use Carbon\Carbon;

class SendCalendarEventNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The calendar event instance.
     *
     * @var \App\Models\CalendarEvent
     */
    protected $event;

    /**
     * Create a new job instance.
     *
     * @param \App\Models\CalendarEvent $event
     * @return void
     */
    public function __construct(CalendarEvent $event)
    {
        $this->event = $event;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        DB::beginTransaction();
        try {
            // Check if notification already sent
            if ($this->event->notification_sent) {
                Log::info("Notification already sent for event: {$this->event->title}");
                return;
            }

            // Get email recipients
            $recipients = $this->event->email_recipients_array;

            if (empty($recipients)) {
                Log::warning("No recipients found for event: {$this->event->title}");
                return;
            }

            // Send email to all recipients
            foreach ($recipients as $recipient) {
                try {
                    Mail::to($recipient)->send(new CalendarEventMail($this->event));
                    Log::info("Calendar event notification sent to: {$recipient} for event: {$this->event->title}");
                } catch (\Exception $e) {
                    Log::error("Failed to send email to {$recipient}: " . $e->getMessage());
                }
            }

            // Mark notification as sent
            $this->event->notification_sent = true;
            $this->event->notification_sent_at = Carbon::now();
            $this->event->save();

            DB::commit();

            activity()
                ->performedOn($this->event)
                ->log('Calendar event notification sent: ' . $this->event->title);

            Log::info("Successfully sent notifications for event: {$this->event->title}");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error sending calendar event notification: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     *
     * @param \Throwable $exception
     * @return void
     */
    public function failed(\Throwable $exception)
    {
        Log::error("Failed to send calendar event notification for event ID {$this->event->id}: " . $exception->getMessage());
    }
}
