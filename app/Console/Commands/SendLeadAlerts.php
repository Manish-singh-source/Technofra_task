<?php

namespace App\Console\Commands;

use App\Models\Lead;
use App\Services\LeadManagement\LeadAlertNotificationService;
use Illuminate\Console\Command;

class SendLeadAlerts extends Command
{
    protected $signature = 'leads:send-alerts';
    protected $description = 'Send email, WhatsApp and Firebase alerts for overdue, due-today and high-priority leads';

    public function handle(LeadAlertNotificationService $alerts): int
    {
        $leads = Lead::query()->whereNotIn('status', ['converted', 'lost', 'junk'])->get();
        $count = 0;
        foreach ($leads as $lead) {
            if ($lead->next_followup_at?->isBefore(today())) {
                $count += $alerts->send($lead, 'overdue_followup', 'Overdue follow-up');
            } elseif ($lead->next_followup_at?->isToday()) {
                $count += $alerts->send($lead, 'due_today_followup', 'Follow-up due today');
            }
            if (($lead->priority ?: 'medium') === 'high') {
                $count += $alerts->send($lead, 'high_priority', 'High priority lead');
            }
        }
        $this->info("Lead alerts sent: {$count}");
        return self::SUCCESS;
    }
}
