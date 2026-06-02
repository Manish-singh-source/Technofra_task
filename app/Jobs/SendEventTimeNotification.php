<?php

namespace App\Jobs;

use App\Models\CalendarEvent;
use App\Services\CalendarManagement\CalendarNotificationService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SendEventTimeNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $event;

    public function __construct(CalendarEvent $event)
    {
        $this->event = $event;
    }

    public function handle()
    {
        DB::beginTransaction();
        try {
            if ($this->event->event_time_notification_sent) {
                Log::info("Event-time notification already sent for event: {$this->event->title}");
                DB::rollBack();
                return;
            }

            $result = app(CalendarNotificationService::class)->notifyEventTime($this->event);

            $this->event->event_time_notification_sent = true;
            $this->event->event_time_notification_sent_at = Carbon::now();
            $this->event->notification_sent = true;
            $this->event->notification_sent_at = Carbon::now();
            $this->event->save();

            DB::commit();

            if (function_exists('activity')) {
                activity()
                    ->performedOn($this->event)
                    ->log('Event-time notification sent for event: ' . $this->event->title);
            }

            Log::info('Event-time notification channel summary', [
                'event_id' => $this->event->id,
                'summary' => $result,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error sending event-time notification for event {$this->event->title}: " . $e->getMessage());
            throw $e;
        }
    }
}
