<?php

namespace App\Jobs;

use App\Mail\CalendarEventMail;
use App\Models\CalendarEvent;
use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\{Mail, Log, DB, Auth};
use Carbon\Carbon;

class SendEventTimeNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
            // Check if event-time notification already sent
            if ($this->event->event_time_notification_sent) {
                Log::info("Event-time notification already sent for event: {$this->event->title}");
                return;
            }

            $emailSent = false;
            $whatsappSent = false;

            // Send Email Notifications
            $emailRecipients = $this->event->email_recipients_array;
            if (!empty($emailRecipients)) {
                foreach ($emailRecipients as $recipient) {
                    try {
                        Mail::to($recipient)->send(new CalendarEventMail($this->event));
                        Log::info("Event-time notification email sent to: {$recipient} for event: {$this->event->title}");
                        $emailSent = true;
                    } catch (\Exception $e) {
                        Log::error("Failed to send event-time notification email to {$recipient}: " . $e->getMessage());
                    }
                }
            }

            // Send WhatsApp Notifications
            $whatsappRecipients = $this->event->whatsapp_recipients_array;
            if (!empty($whatsappRecipients)) {
                try {
                    $whatsappService = new WhatsAppService();
                    $result = $whatsappService->sendCalendarEventNotification(
                        $this->event,
                        $whatsappRecipients,
                        'event_time'
                    );
                    
                    Log::info("Event-time notification WhatsApp sent: {$result['success']} success, {$result['failed']} failed for event: {$this->event->title}");
                    
                    if ($result['success'] > 0) {
                        $whatsappSent = true;
                    }
                } catch (\Exception $e) {
                    Log::error("Failed to send event-time notification WhatsApp: " . $e->getMessage());
                }
            }

            // Mark event-time notification as sent
            $this->event->event_time_notification_sent = true;
            $this->event->event_time_notification_sent_at = Carbon::now();
            
            // Also mark the old notification_sent field for backward compatibility
            $this->event->notification_sent = true;
            $this->event->notification_sent_at = Carbon::now();
            
            $this->event->save();

            DB::commit();

            activity()
                ->performedOn($this->event)
                ->log('Event-time notification sent for event: ' . $this->event->title);

            Log::info("Event-time notification completed for event: {$this->event->title} (Email: " . ($emailSent ? 'Yes' : 'No') . ", WhatsApp: " . ($whatsappSent ? 'Yes' : 'No') . ")");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error sending event-time notification for event {$this->event->title}: " . $e->getMessage());
            throw $e;
        }
    }
}

