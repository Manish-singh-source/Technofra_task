<?php

namespace App\Services\ProjectManagement;

use App\Models\Project;
use App\Models\ProjectMilestone;
use App\Models\Task;
use App\Models\User;
use App\Notifications\ProjectManagement\EnterpriseProjectNotification;
use Illuminate\Support\Collection;

class ProjectNotificationService
{
    public function notifyTaskAssigned(Task $task, array $assigneeIds, ?int $actorId = null): void
    {
        $users = $this->resolveUsers($assigneeIds, true);
        if ($users->isEmpty()) {
            return;
        }

        $projectName = $task->project?->project_name ?? 'Project';
        $title = 'Task Assigned';
        $body = sprintf('You were assigned to task "%s" in %s.', $task->title, $projectName);

        $this->dispatch(
            $users,
            $title,
            $body,
            'task_assignment',
            [
                'task_id' => $task->id,
                'project_id' => $task->project_id,
                'actor_id' => $actorId,
            ]
        );
    }

    public function notifyQaReviewRequested(Task $task, ?int $actorId = null): void
    {
        $project = $task->project;
        $recipientIds = collect($project->members ?? [])
            ->merge($task->followers ?? [])
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->all();

        $users = $this->resolveUsers($recipientIds, true);
        if ($users->isEmpty()) {
            return;
        }

        $title = 'QA Review Requested';
        $body = sprintf('Task "%s" is ready for QA review.', $task->title);

        $this->dispatch(
            $users,
            $title,
            $body,
            'qa_review_requested',
            [
                'task_id' => $task->id,
                'project_id' => $task->project_id,
                'actor_id' => $actorId,
            ]
        );
    }

    public function notifyDeploymentCompleted(Task $task, ?int $actorId = null): void
    {
        $project = $task->project;
        if (! $project) {
            return;
        }

        $recipientIds = collect($project->members ?? [])
            ->push($project->project_manager_id)
            ->push($project->customer_id)
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->all();

        $users = $this->resolveUsers($recipientIds, false);
        if ($users->isEmpty()) {
            return;
        }

        $title = 'Deployment Completed';
        $body = sprintf('Task "%s" has been deployed for project "%s".', $task->title, $project->project_name);

        $this->dispatch(
            $users,
            $title,
            $body,
            'deployment_completed',
            [
                'task_id' => $task->id,
                'project_id' => $task->project_id,
                'actor_id' => $actorId,
                'deployed_at' => optional($task->deployed_at)?->toISOString(),
            ]
        );
    }

    public function notifyOverdueTasks(): int
    {
        $tasks = Task::query()
            ->with('project')
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->whereDate('deadline', '<', now()->toDateString())
            ->get();

        $sent = 0;
        foreach ($tasks as $task) {
            $recipientIds = collect($task->assignees ?? [])
                ->merge($task->followers ?? [])
                ->push($task->project?->project_manager_id)
                ->filter()
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->all();

            $users = $this->resolveUsers($recipientIds, true);
            if ($users->isEmpty()) {
                continue;
            }

            $title = 'Overdue Task Alert';
            $body = sprintf('Task "%s" is overdue.', $task->title);
            $this->dispatch($users, $title, $body, 'overdue_task', [
                'task_id' => $task->id,
                'project_id' => $task->project_id,
                'deadline' => optional($task->deadline)?->toDateString(),
            ]);

            $sent += $users->count();
        }

        return $sent;
    }

    public function notifyMilestoneDeadlines(int $withinDays = 2): int
    {
        $start = now()->toDateString();
        $end = now()->addDays(max($withinDays, 0))->toDateString();

        $milestones = ProjectMilestone::query()
            ->with('project')
            ->whereIn('status', ['pending', 'in_progress'])
            ->whereBetween('due_date', [$start, $end])
            ->get();

        $sent = 0;
        foreach ($milestones as $milestone) {
            $project = $milestone->project;
            if (! $project) {
                continue;
            }

            $recipientIds = collect($project->members ?? [])
                ->push($project->project_manager_id)
                ->push($project->customer_id)
                ->filter()
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->all();

            $users = $this->resolveUsers($recipientIds, false);
            if ($users->isEmpty()) {
                continue;
            }

            $title = 'Milestone Deadline Reminder';
            $body = sprintf(
                'Milestone "%s" is due on %s.',
                $milestone->title,
                optional($milestone->due_date)?->format('Y-m-d')
            );
            $this->dispatch($users, $title, $body, 'milestone_deadline', [
                'project_id' => $project->id,
                'milestone_id' => $milestone->id,
                'due_date' => optional($milestone->due_date)?->toDateString(),
            ]);

            $sent += $users->count();
        }

        return $sent;
    }

    private function dispatch(Collection $users, string $title, string $body, string $type, array $data = []): void
    {
        foreach ($users as $user) {
            $user->notify(new EnterpriseProjectNotification(
                $title,
                $body,
                $type,
                $data,
                (bool) config('project_management.notifications.channels.email', true),
                (bool) config('project_management.notifications.channels.whatsapp_ready', true)
            ));
        }
    }

    private function resolveUsers(array $ids, bool $staffOnly): Collection
    {
        if (empty($ids)) {
            return collect();
        }

        $query = User::query()->whereIn('id', $ids);
        if ($staffOnly) {
            $query->where('role', 'staff');
        }

        return $query->get();
    }
}
