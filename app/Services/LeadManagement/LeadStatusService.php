<?php

namespace App\Services\LeadManagement;

use App\Jobs\SendLeadDashboardReminderJob;
use App\Models\Lead;
use App\Models\LeadConversion;
use App\Models\LeadFollowup;
use App\Models\LeadReminder;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class LeadStatusService
{
    public function statuses(): array
    {
        return collect(config('lead_statuses', []))->pluck('slug')->all();
    }

    public function validateTransition(string $from, string $to): void
    {
        if ($from === $to) {
            return;
        }

        $ordered = [
            'new', 'attempted_contact', 'contacted', 'qualified', 'demo_scheduled', 'proposal_sent', 'negotiation', 'won',
        ];

        if (in_array($to, ['lost', 'junk'], true)) {
            if ($to === 'junk' && ! in_array($from, ['new', 'attempted_contact', 'contacted'], true)) {
                throw ValidationException::withMessages(['status' => 'Junk is allowed only from early stages.']);
            }
            return;
        }

        if (in_array($from, ['won', 'lost', 'junk'], true)) {
            throw ValidationException::withMessages(['status' => 'Closed leads cannot move back to active pipeline stages.']);
        }

        $fromIndex = array_search($from, $ordered, true);
        $toIndex = array_search($to, $ordered, true);

        if ($fromIndex === false || $toIndex === false || $toIndex < $fromIndex) {
            throw ValidationException::withMessages(['status' => 'Invalid status transition.']);
        }
    }

    public function applyStatusChange(Lead $lead, string $newStatus, ?int $userId, ?string $remarks = null, ?string $lostReason = null, ?float $wonValue = null): void
    {
        $oldStatus = (string) ($lead->status ?? 'new');

        $this->validateTransition($oldStatus, $newStatus);

        $lead->previous_status = $oldStatus;
        $lead->status = $newStatus;
        $lead->status_updated_at = now();
        $lead->status_updated_by = $userId;
        $stage = collect(config('lead_statuses', []))->firstWhere('slug', $newStatus);
        $lead->pipeline_stage_order = (int) ($stage['order'] ?? 0);

        if ($newStatus === 'won') {
            $lead->converted_at = now();
            if ($wonValue !== null) {
                $lead->won_value = $wonValue;
            }
            LeadConversion::query()->firstOrCreate(
                ['lead_id' => $lead->id],
                ['converted_by' => $userId, 'conversion_value' => $wonValue, 'converted_at' => now()]
            );
        }

        if ($newStatus === 'lost') {
            if (! $lostReason) {
                throw ValidationException::withMessages(['lost_reason' => 'Lost reason is required when status is lost.']);
            }
            $lead->lost_at = now();
            $lead->lost_reason = $lostReason;
        }

        $lead->save();

        app(LeadPipelineService::class)->logStatusChange($lead, $oldStatus, $newStatus, $userId, $remarks);

        $this->runAutoActions($lead, $newStatus, $userId);
    }

    private function runAutoActions(Lead $lead, string $status, ?int $userId): void
    {
        if ($status === 'contacted') {
            $reminder = LeadReminder::query()->create([
                'lead_id' => $lead->id,
                'user_id' => $lead->assigned_to ?: $userId,
                'remind_at' => now()->addDay(),
                'reminder_type' => 'dashboard',
                'status' => 'pending',
            ]);
            SendLeadDashboardReminderJob::dispatch($reminder->id)->delay(now()->addMinute());
        }

        if ($status === 'demo_scheduled') {
            LeadFollowup::query()->create([
                'lead_id' => $lead->id,
                'staff_id' => $lead->assigned_to ?: $userId,
                'followup_date' => Carbon::now()->addDay(),
                'followup_type' => 'demo',
                'outcome' => 'meeting_scheduled',
                'discussion_notes' => 'Auto-generated demo followup after status change.',
            ]);
        }

        if ($status === 'proposal_sent') {
            LeadReminder::query()->create([
                'lead_id' => $lead->id,
                'user_id' => $lead->assigned_to ?: $userId,
                'remind_at' => now()->addDays(2),
                'reminder_type' => 'dashboard',
                'status' => 'pending',
            ]);
        }
    }
}
