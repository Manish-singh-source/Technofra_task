<?php

namespace App\Repositories;

use App\Models\ProjectActivity;
use App\Models\ProjectMilestone;
use App\Models\Task;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ProjectDashboardRepository
{
    public function projectTasks(int $projectId): Collection
    {
        return Task::query()
            ->select([
                'id',
                'project_id',
                'title',
                'status',
                'workflow_status',
                'priority',
                'assignees',
                'deadline',
                'created_at',
            ])
            ->where('project_id', $projectId)
            ->orderByDesc('created_at')
            ->get();
    }

    public function activityFeed(int $projectId, int $perPage = 15): LengthAwarePaginator
    {
        return ProjectActivity::query()
            ->select([
                'id',
                'project_id',
                'task_id',
                'user_id',
                'activity_type',
                'title',
                'description',
                'activity_at',
                'created_at',
            ])
            ->where('project_id', $projectId)
            ->with(['user:id,first_name,last_name,email', 'task:id,title'])
            ->orderByDesc('activity_at')
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    public function milestones(int $projectId): Collection
    {
        return ProjectMilestone::query()
            ->select(['id', 'project_id', 'title', 'status', 'progress_percentage', 'due_date', 'completed_at'])
            ->where('project_id', $projectId)
            ->orderBy('sort_order')
            ->orderBy('due_date')
            ->orderBy('id')
            ->get();
    }
}

