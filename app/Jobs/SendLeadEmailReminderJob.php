<?php

namespace App\Jobs;

use App\Models\LeadReminder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendLeadEmailReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $reminderId)
    {
    }

    public function handle(): void
    {
        $reminder = LeadReminder::query()->find($this->reminderId);
        if (! $reminder) {
            return;
        }

        // Provider adapter placeholder: SMTP/Mail provider should be injected here.
        $reminder->update(['status' => 'sent', 'sent_at' => now()]);
    }
}
