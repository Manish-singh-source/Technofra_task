<?php

namespace App\Services\LeadManagement;

use App\Models\AssignedLead;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\LeadAssignment;
use App\Models\LeadReminder;
use App\Models\LeadStatusHistory;
use Illuminate\Support\Carbon;

class LeadPipelineService
{
    public function logActivity(int $leadId, ?int $userId, string $type, string $description, array $metadata = []): LeadActivity
    {
        return LeadActivity::query()->create([
            'lead_id' => $leadId,
            'user_id' => $userId,
            'activity_type' => $type,
            'description' => $description,
            'metadata' => $metadata ?: null,
        ]);
    }

    public function assignLead(Lead $lead, int $assignedTo, ?int $assignedBy = null, ?string $note = null): LeadAssignment
    {
        LeadAssignment::query()
            ->where('lead_id', $lead->id)
            ->where('active', true)
            ->update(['active' => false]);

        // Current assignees are stored in assigned_leads.staff_ids (multi-assign).
        // Keep this in sync for the pipeline lead record.
        $existing = AssignedLead::query()
            ->where('lead_model', 'lead')
            ->where('lead_id', (int) $lead->id)
            ->first();

        $staffIds = collect($existing?->staff_ids ?? [])
            ->map(fn ($v) => (int) $v)
            ->push((int) $assignedTo)
            ->filter(fn ($v) => $v > 0)
            ->unique()
            ->values()
            ->all();

        AssignedLead::updateOrCreate(
            ['lead_model' => 'lead', 'lead_id' => (int) $lead->id],
            ['staff_ids' => $staffIds]
        );

        $assignment = LeadAssignment::query()->create([
            'lead_id' => $lead->id,
            'assigned_to' => $assignedTo,
            'assigned_by' => $assignedBy,
            'assignment_note' => $note,
            'active' => true,
            'assigned_at' => Carbon::now(),
        ]);

        $this->logActivity($lead->id, $assignedBy, 'lead_assigned', 'Lead assignment updated.', [
            'assigned_to' => $assignedTo,
            'note' => $note,
        ]);

        return $assignment;
    }

    public function logStatusChange(Lead $lead, ?string $oldStatus, string $newStatus, ?int $changedBy = null, ?string $remarks = null): LeadStatusHistory
    {
        $history = LeadStatusHistory::query()->create([
            'lead_id' => $lead->id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'changed_by' => $changedBy,
            'remarks' => $remarks,
            'changed_at' => now(),
        ]);

        $this->logActivity($lead->id, $changedBy, 'status_changed', "Lead status changed from {$oldStatus} to {$newStatus}", [
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
        ]);

        return $history;
    }

    public function createReminder(int $leadId, ?int $userId, Carbon $remindAt, string $type = 'dashboard'): LeadReminder
    {
        $reminder = LeadReminder::query()->create([
            'lead_id' => $leadId,
            'user_id' => $userId,
            'remind_at' => $remindAt,
            'reminder_type' => $type,
            'status' => 'pending',
        ]);

        $this->logActivity($leadId, $userId, 'reminder_created', 'Reminder created for lead followup.', [
            'remind_at' => $remindAt->toDateTimeString(),
            'reminder_type' => $type,
        ]);

        return $reminder;
    }
}
