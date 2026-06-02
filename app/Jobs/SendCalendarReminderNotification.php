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

class SendCalendarReminderNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected CalendarEvent $event,
        protected string $window
    ) {
    }

    public function handle(): void
    {
        DB::beginTransaction();

        try {
            if ($this->event->hasReminderWindowBeenSent($this->window)) {
                Log::info("Calendar reminder window already sent", [
                    'event_id' => $this->event->id,
                    'event_title' => $this->event->title,
                    'window' => $this->window,
                ]);

                DB::rollBack();
                return;
            }

            $result = app(CalendarNotificationService::class)->notifyReminder($this->event, $this->window);

            $this->event->markReminderWindowAsSent($this->window, Carbon::now());
            $this->event->save();

            DB::commit();

            if (function_exists('activity')) {
                activity()
                    ->performedOn($this->event)
                    ->log(sprintf('Calendar reminder sent for %s: %s', $this->window, $this->event->title));
            }

            Log::info('Calendar reminder channel summary', [
                'event_id' => $this->event->id,
                'window' => $this->window,
                'summary' => $result,
            ]);
        } catch (\Throwable $exception) {
            DB::rollBack();
            Log::error("Error sending calendar reminder for event {$this->event->title}", [
                'event_id' => $this->event->id,
                'window' => $this->window,
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }
}
