<?php

namespace App\Services\ProjectManagement;

use App\Models\ProjectMilestone;
use App\Models\Task;

class MilestoneProgressService
{
    public function syncForMilestone(int $milestoneId): void
    {
        $milestone = ProjectMilestone::find($milestoneId);
        if (! $milestone) {
            return;
        }

        $tasks = Task::query()->where('milestone_id', $milestoneId)->get(['id', 'status', 'workflow_status']);
        $total = $tasks->count();

        $completed = $tasks->filter(function (Task $task) {
            return $task->status === 'completed'
                || in_array((string) $task->workflow_status, ['completed', 'archived'], true);
        })->count();

        $inProgress = $tasks->filter(function (Task $task) {
            return $task->status === 'in_progress'
                || in_array((string) $task->workflow_status, ['in_progress', 'review', 'testing', 'deployed'], true);
        })->count();

        $progress = $total > 0 ? (int) round(($completed / $total) * 100) : ($milestone->status === 'completed' ? 100 : 0);

        $status = $milestone->status;
        $completedAt = $milestone->completed_at;

        if ($total > 0 && $completed >= $total) {
            $status = 'completed';
            $completedAt = $completedAt ?? now();
        } elseif ($inProgress > 0 || ($total > 0 && $completed > 0)) {
            $status = 'in_progress';
            $completedAt = null;
        } elseif ($total > 0) {
            $status = 'pending';
            $completedAt = null;
        }

        $milestone->update([
            'progress_percentage' => $progress,
            'status' => $status,
            'completed_at' => $completedAt,
        ]);
    }

    public function syncForProject(int $projectId): void
    {
        ProjectMilestone::query()
            ->where('project_id', $projectId)
            ->pluck('id')
            ->each(fn ($id) => $this->syncForMilestone((int) $id));
    }
}
