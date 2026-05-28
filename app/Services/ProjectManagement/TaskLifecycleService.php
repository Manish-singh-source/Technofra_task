<?php

namespace App\Services\ProjectManagement;

use InvalidArgumentException;

class TaskLifecycleService
{
    public function statuses(): array
    {
        return (array) config('project_management.task_workflow_statuses', []);
    }

    public function transitions(): array
    {
        return (array) config('project_management.task_workflow_transitions', []);
    }

    public function isValidStatus(?string $status): bool
    {
        if ($status === null || $status === '') {
            return true;
        }

        return in_array($status, $this->statuses(), true);
    }

    public function ensureValidStatus(?string $status): void
    {
        if (! $this->isValidStatus($status)) {
            throw new InvalidArgumentException('Invalid task workflow status.');
        }
    }

    public function canTransition(?string $from, string $to): bool
    {
        $this->ensureValidStatus($to);

        if ($from === null || $from === '') {
            return true;
        }

        $this->ensureValidStatus($from);

        if ($from === $to) {
            return true;
        }

        $allowed = $this->transitions()[$from] ?? [];

        return in_array($to, $allowed, true);
    }

    public function ensureTransition(?string $from, ?string $to): void
    {
        if ($to === null || $to === '') {
            return;
        }

        if (! $this->canTransition($from, $to)) {
            throw new InvalidArgumentException(sprintf('Invalid task workflow transition: %s -> %s', (string) $from, $to));
        }
    }
}
