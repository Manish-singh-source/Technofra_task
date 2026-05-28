<?php

namespace App\Services\ProjectManagement;

use App\Models\TaskDependency;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class TaskDependencyService
{
    public function createDependency(int $taskId, int $dependsOnTaskId, string $dependencyType, ?int $createdBy = null): TaskDependency
    {
        $this->validateDependencyType($dependencyType);
        $this->guardSelfDependency($taskId, $dependsOnTaskId);
        $this->guardCircularDependency($taskId, $dependsOnTaskId);

        return TaskDependency::firstOrCreate(
            [
                'task_id' => $taskId,
                'depends_on_task_id' => $dependsOnTaskId,
                'dependency_type' => $dependencyType,
            ],
            [
                'created_by' => $createdBy,
            ]
        );
    }

    public function dependencyTree(int $taskId): Collection
    {
        return TaskDependency::query()
            ->where('task_id', $taskId)
            ->with('dependsOnTask')
            ->get();
    }

    public function removeDependency(int $taskId, int $dependsOnTaskId, ?string $dependencyType = null): int
    {
        return TaskDependency::query()
            ->where('task_id', $taskId)
            ->where('depends_on_task_id', $dependsOnTaskId)
            ->when($dependencyType, fn ($q) => $q->where('dependency_type', $dependencyType))
            ->delete();
    }

    private function validateDependencyType(string $dependencyType): void
    {
        $types = (array) config('project_management.task_dependency_types', []);

        if (! in_array($dependencyType, $types, true)) {
            throw new InvalidArgumentException('Invalid dependency type.');
        }
    }

    private function guardSelfDependency(int $taskId, int $dependsOnTaskId): void
    {
        if ($taskId === $dependsOnTaskId) {
            throw new InvalidArgumentException('A task cannot depend on itself.');
        }
    }

    private function guardCircularDependency(int $taskId, int $dependsOnTaskId): void
    {
        if ($this->isReachable($dependsOnTaskId, $taskId)) {
            throw new InvalidArgumentException('Circular dependency detected.');
        }
    }

    private function isReachable(int $startTaskId, int $targetTaskId): bool
    {
        $visited = [];
        $stack = [$startTaskId];

        while (! empty($stack)) {
            $current = array_pop($stack);
            if ($current === $targetTaskId) {
                return true;
            }

            if (isset($visited[$current])) {
                continue;
            }
            $visited[$current] = true;

            $nextIds = TaskDependency::query()
                ->where('task_id', $current)
                ->pluck('depends_on_task_id')
                ->map(fn ($id) => (int) $id)
                ->all();

            foreach ($nextIds as $nextId) {
                if (! isset($visited[$nextId])) {
                    $stack[] = $nextId;
                }
            }
        }

        return false;
    }
}
