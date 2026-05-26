<?php

namespace App\Console\Commands;

use App\Models\Lead;
use App\Models\LeadEscalation;
use App\Models\User;
use App\Services\LeadManagement\LeadPipelineService;
use Illuminate\Console\Command;

class EscalateOverdueLeads extends Command
{
    protected $signature = 'leads:escalate-overdue';
    protected $description = 'Escalate leads with overdue followups or no followup for 48 hours';

    public function handle(LeadPipelineService $pipelineService): int
    {
        $manager = User::query()
            ->whereIn('role', ['admin', 'super_admin', 'super-admin'])
            ->orderBy('id')
            ->first();

        if (! $manager) {
            $this->warn('No manager/admin found.');
            return self::SUCCESS;
        }

        $leads = Lead::query()
            ->where(function ($query) {
                $query->where(function ($q) {
                    $q->whereNotNull('next_followup_at')->where('next_followup_at', '<', now());
                })->orWhere(function ($q) {
                    $q->whereNull('next_followup_at')->where('created_at', '<', now()->subHours(48));
                });
            })
            ->whereNotIn('status', ['won', 'lost', 'junk'])
            ->get();

        foreach ($leads as $lead) {
            $exists = LeadEscalation::query()
                ->where('lead_id', $lead->id)
                ->whereDate('created_at', now()->toDateString())
                ->exists();

            if ($exists) {
                continue;
            }

            $escalation = LeadEscalation::query()->create([
                'lead_id' => $lead->id,
                'escalated_from' => $lead->assigned_to,
                'escalated_to' => $manager->id,
                'reason' => 'Auto escalation due to overdue/no followup.',
                'escalated_at' => now(),
            ]);

            $pipelineService->logActivity($lead->id, $manager->id, 'lead_escalated', 'Lead auto-escalated by scheduler.', [
                'escalation_id' => $escalation->id,
                'auto' => true,
            ]);
        }

        $this->info('Overdue lead escalation completed.');

        return self::SUCCESS;
    }
}
