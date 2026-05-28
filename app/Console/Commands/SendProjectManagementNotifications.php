<?php

namespace App\Console\Commands;

use App\Services\ProjectManagement\ProjectNotificationService;
use Illuminate\Console\Command;

class SendProjectManagementNotifications extends Command
{
    protected $signature = 'project-management:send-notifications {--days=2 : Days window for milestone reminders}';

    protected $description = 'Send overdue task and milestone deadline notifications for project management';

    public function handle(ProjectNotificationService $notificationService): int
    {
        $days = (int) $this->option('days');
        $overdueCount = $notificationService->notifyOverdueTasks();
        $milestoneCount = $notificationService->notifyMilestoneDeadlines($days);

        $this->info(sprintf(
            'Project management notifications sent. Overdue: %d, Milestone: %d',
            $overdueCount,
            $milestoneCount
        ));

        return self::SUCCESS;
    }
}
