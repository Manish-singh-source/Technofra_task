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

class Send10MinReminderNotification implements ShouldQueue
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
            // Check if 10-min reminder already sent
            if ($this->event->reminder_10min_sent) {
                Log::info("10-min reminder already sent for event: {$this->event->title}");
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
                        Log::info("10-min reminder email sent to: {$recipient} for event: {$this->event->title}");
                        $emailSent = true;
                    } catch (\Exception $e) {
                        Log::error("Failed to send 10-min reminder email to {$recipient}: " . $e->getMessage());
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
                        'reminder'
                    );
                    
                    Log::info("10-min reminder WhatsApp sent: {$result['success']} success, {$result['failed']} failed for event: {$this->event->title}");
                    
                    if ($result['success'] > 0) {
                        $whatsappSent = true;
                    }
                } catch (\Exception $e) {
                    Log::error("Failed to send 10-min reminder WhatsApp: " . $e->getMessage());
                }
            }

            // Mark 10-min reminder as sent
            $this->event->reminder_10min_sent = true;
            $this->event->reminder_10min_sent_at = Carbon::now();
            $this->event->save();

            DB::commit();

            if (function_exists('activity')) {
                activity()
                    ->performedOn($this->event)
                    ->log('10-minute reminder sent for event: ' . $this->event->title);
            }

            Log::info("10-min reminder completed for event: {$this->event->title} (Email: " . ($emailSent ? 'Yes' : 'No') . ", WhatsApp: " . ($whatsappSent ? 'Yes' : 'No') . ")");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error sending 10-min reminder for event {$this->event->title}: " . $e->getMessage());
            throw $e;
        }
    }
}

