<?php

namespace App\Services\LeadManagement;

use App\Jobs\SendLeadDashboardReminderJob;
use App\Models\Lead;
use App\Models\LeadReminder;
use App\Models\User;
use App\Services\UnifiedNotificationService;
use Illuminate\Support\Carbon;

class LeadMobileNotificationService
{
    public function __construct(
        private readonly UnifiedNotificationService $notificationService
    ) {
    }

    public function notifyLeadCreatedToAdmins(Lead $lead): void
    {
        $title = 'New Lead Created';
        $body = sprintf('A new lead "%s" has been created.', (string) ($lead->name ?: 'Unnamed Lead'));

        foreach ($this->adminUsers() as $admin) {
            $this->notificationService->sendToUser(
                $admin,
                $title,
                $body,
                'lead_created',
                [
                    'lead_id' => $lead->id,
                    'source' => $lead->source,
                ]
            );
        }
    }

    /**
     * @param  array<int>  $staffIds
     */
    public function notifyLeadAssignedToStaff(Lead $lead, array $staffIds): void
    {
        $staff = User::staffMembers()->whereIn('id', collect($staffIds)->map(fn ($id) => (int) $id)->filter()->unique()->values()->all())->get();

        foreach ($staff as $member) {
            $this->notificationService->sendToUser(
                $member,
                'Lead Assigned',
                sprintf('Lead "%s" has been assigned to you.', (string) ($lead->name ?: 'Unnamed Lead')),
                'lead_assigned',
                [
                    'lead_id' => $lead->id,
                    'source' => $lead->source,
                ]
            );
        }
    }

    /**
     * @param  array<int>  $staffIds
     */
    public function notifyFollowupCreatedToStaff(Lead $lead, array $staffIds, Carbon $followupDate): void
    {
        $staff = User::staffMembers()->whereIn('id', collect($staffIds)->map(fn ($id) => (int) $id)->filter()->unique()->values()->all())->get();

        foreach ($staff as $member) {
            $this->notificationService->sendToUser(
                $member,
                'Lead Followup Created',
                sprintf(
                    'A followup for lead "%s" is scheduled on %s.',
                    (string) ($lead->name ?: 'Unnamed Lead'),
                    $followupDate->format('d M Y h:i A')
                ),
                'lead_followup_created',
                [
                    'lead_id' => $lead->id,
                    'followup_at' => $followupDate->toDateTimeString(),
                ]
            );
        }
    }

    /**
     * @param  array<int>  $staffIds
     */
    public function scheduleFollowupReminderPushes(Lead $lead, array $staffIds, Carbon $nextFollowupDate): void
    {
        $uniqueStaffIds = collect($staffIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values();

        if ($uniqueStaffIds->isEmpty()) {
            return;
        }

        $triggers = [
            'followup_reminder_day_before' => $nextFollowupDate->copy()->subDay(),
            'followup_reminder_15_min_before' => $nextFollowupDate->copy()->subMinutes(15),
        ];

        foreach ($uniqueStaffIds as $staffId) {
            foreach ($triggers as $type => $triggerAt) {
                if ($triggerAt->lessThanOrEqualTo(now())) {
                    continue;
                }

                $reminder = LeadReminder::query()->create([
                    'lead_id' => $lead->id,
                    'user_id' => (int) $staffId,
                    'remind_at' => $triggerAt,
                    'reminder_type' => $type,
                    'status' => 'pending',
                ]);

                SendLeadDashboardReminderJob::dispatch($reminder->id)->delay($triggerAt);
            }
        }
    }

    /**
     * @return \Illuminate\Support\Collection<int, User>
     */
    private function adminUsers()
    {
        return User::query()
            ->where(function ($query) {
                $query->where('role', 'admin')
                    ->orWhere('role', 'super_admin')
                    ->orWhere('role', 'super-admin')
                    ->orWhere('role', 'super_admin2');
            })
            ->get();
    }
}
