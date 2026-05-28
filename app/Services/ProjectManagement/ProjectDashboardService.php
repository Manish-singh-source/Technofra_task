<?php

namespace App\Services\ProjectManagement;

use App\Models\Project;
use App\Models\Task;
use App\Repositories\ProjectDashboardRepository;
use App\Support\ProjectKanbanStatus;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ProjectDashboardService
{
    public function __construct(private ProjectDashboardRepository $repository) {}

    /**
     * @return array<string, mixed>
     */
    public function kanbanBoard(int $projectId): array
    {
        $tasks = $this->repository->projectTasks($projectId);

        $columns = collect(ProjectKanbanStatus::columns())
            ->map(function (array $column) use ($tasks) {
                $items = $tasks->filter(function ($task) use ($column) {
                    $workflow = ProjectKanbanStatus::normalize((string) ($task->workflow_status ?: $task->status));

                    return $workflow === $column['key'];
                })->values();

                return [
                    'key' => $column['key'],
                    'label' => $column['label'],
                    'count' => $items->count(),
                    'tasks' => $items->map(fn ($task) => [
                        'id' => (int) $task->id,
                        'title' => (string) $task->title,
                        'priority' => (string) ($task->priority ?? 'medium'),
                        'status' => (string) ($task->status ?? 'pending'),
                        'workflow_status' => (string) ($task->workflow_status ?? 'backlog'),
                        'deadline' => optional($task->deadline)?->toDateString(),
                        'assignees' => is_array($task->assignees) ? $task->assignees : [],
                    ])->values()->all(),
                ];
            })
            ->values()
            ->all();

        return ['columns' => $columns];
    }

    /**
     * @return array<string, mixed>
     */
    public function charts(int $projectId): array
    {
        $tasks = $this->repository->projectTasks($projectId);
        $milestones = $this->repository->milestones($projectId);

        $statusCounts = $tasks->countBy(fn ($task) => (string) ($task->status ?? 'pending'));
        $statusLabels = ['not_started', 'pending', 'in_progress', 'completed', 'on_hold', 'cancelled'];

        $chartSeries = collect($statusLabels)
            ->map(fn ($label) => (int) ($statusCounts[$label] ?? 0))
            ->values()
            ->all();

        $overdueCount = $tasks->filter(fn ($task) => $task->deadline && $task->deadline->isPast() && ($task->status !== 'completed'))->count();
        $milestoneDone = $milestones->where('status', 'completed')->count();
        $milestoneTotal = max($milestones->count(), 1);

        return [
            'task_status' => [
                'labels' => $statusLabels,
                'series' => $chartSeries,
            ],
            'summary' => [
                'total_tasks' => $tasks->count(),
                'overdue_tasks' => $overdueCount,
                'milestone_completion_percent' => round(($milestoneDone / $milestoneTotal) * 100, 2),
            ],
        ];
    }

    public function activityFeed(int $projectId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->activityFeed($projectId, $perPage);
    }

    /**
     * @return array<string, mixed>
     */
    public function milestoneProgress(int $projectId): array
    {
        $milestones = $this->repository->milestones($projectId);

        return [
            'total' => $milestones->count(),
            'completed' => $milestones->where('status', 'completed')->count(),
            'in_progress' => $milestones->where('status', 'in_progress')->count(),
            'pending' => $milestones->where('status', 'pending')->count(),
            'rows' => $milestones->map(fn ($m) => [
                'id' => (int) $m->id,
                'title' => (string) $m->title,
                'status' => (string) $m->status,
                'progress_percentage' => (int) ($m->progress_percentage ?? 0),
                'due_date' => optional($m->due_date)?->toDateString(),
                'completed_at' => optional($m->completed_at)?->toISOString(),
            ])->values()->all(),
        ];
    }

    public function moveKanbanTask(Project $project, Task $task, string $column): Task
    {
        $normalized = ProjectKanbanStatus::normalize($column);
        $status = ProjectKanbanStatus::toTaskStatus($normalized);

        $task->workflow_status = $normalized;
        $task->status = $status;
        $task->save();

        return $task;
    }

    /**
     * @return Collection<int, Task>
     */
    public function filteredTasks(int $projectId, array $filters): Collection
    {
        return Task::query()
            ->select(['id', 'project_id', 'title', 'status', 'workflow_status', 'priority', 'assignees', 'deadline', 'created_at'])
            ->where('project_id', $projectId)
            ->when(! empty($filters['status']), fn ($q) => $q->where('status', $filters['status']))
            ->when(! empty($filters['priority']), fn ($q) => $q->where('priority', $filters['priority']))
            ->when(! empty($filters['q']), function ($q) use ($filters) {
                $term = trim((string) $filters['q']);
                $q->where('title', 'like', '%'.$term.'%');
            })
            ->orderByDesc('created_at')
            ->limit((int) ($filters['limit'] ?? 50))
            ->get();
    }
}

