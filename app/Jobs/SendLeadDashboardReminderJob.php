<?php

namespace App\Jobs;

use App\Models\LeadReminder;
use App\Notifications\InAppPushNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendLeadDashboardReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $reminderId)
    {
    }

    public function handle(): void
    {
        $reminder = LeadReminder::query()->with('user')->find($this->reminderId);
        if (! $reminder || ! $reminder->user) {
            return;
        }

        $reminder->user->notify(new InAppPushNotification(
            'Lead Reminder',
            'You have a pending lead reminder.',
            'lead_reminder',
            ['lead_id' => $reminder->lead_id, 'reminder_type' => $reminder->reminder_type]
        ));

        $reminder->update(['status' => 'sent', 'sent_at' => now()]);
    }
}
