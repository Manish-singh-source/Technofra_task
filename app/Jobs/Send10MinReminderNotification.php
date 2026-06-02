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

class Send10MinReminderNotification implements ShouldQueue
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
            if ($this->event->reminder_10min_sent) {
                Log::info("10-min reminder already sent for event: {$this->event->title}");
                DB::rollBack();
                return;
            }

            $result = app(CalendarNotificationService::class)->notifyReminder($this->event, 'reminder_10min');

            $this->event->reminder_10min_sent = true;
            $this->event->reminder_10min_sent_at = Carbon::now();
            $this->event->save();

            DB::commit();

            if (function_exists('activity')) {
                activity()
                    ->performedOn($this->event)
                    ->log('10-minute reminder sent for event: ' . $this->event->title);
            }

            Log::info('10-min reminder channel summary', [
                'event_id' => $this->event->id,
                'summary' => $result,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error sending 10-min reminder for event {$this->event->title}: " . $e->getMessage());
            throw $e;
        }
    }
}
