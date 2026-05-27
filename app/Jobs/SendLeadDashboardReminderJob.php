<?php

namespace App\Jobs;

use App\Models\LeadReminder;
use App\Services\UnifiedNotificationService;
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
        $reminder = LeadReminder::query()->with(['user', 'lead'])->find($this->reminderId);
        if (! $reminder || ! $reminder->user) {
            return;
        }

        [$title, $body] = $this->messageForReminder($reminder);

        app(UnifiedNotificationService::class)->sendToUser(
            $reminder->user,
            $title,
            $body,
            'lead_reminder',
            [
                'lead_id' => $reminder->lead_id,
                'reminder_type' => $reminder->reminder_type,
                'remind_at' => optional($reminder->remind_at)?->toDateTimeString(),
            ]
        );

        $reminder->update(['status' => 'sent', 'sent_at' => now()]);
    }

    private function messageForReminder(LeadReminder $reminder): array
    {
        $leadName = (string) ($reminder->lead?->name ?: 'Lead');

        return match ((string) $reminder->reminder_type) {
            'followup_reminder_day_before' => [
                'Followup Reminder (1 Day Left)',
                "Followup for {$leadName} is tomorrow.",
            ],
            'followup_reminder_15_min_before' => [
                'Followup Reminder (15 Minutes Left)',
                "Followup for {$leadName} starts in 15 minutes.",
            ],
            default => [
                'Lead Reminder',
                "You have a pending lead reminder for {$leadName}.",
            ],
        };
    }
}
