<?php

namespace App\Services\ProjectManagement;

use InvalidArgumentException;

class ProjectLifecycleService
{
    public function stages(): array
    {
        return (array) config('project_management.project_lifecycle', []);
    }

    public function isValidStage(?string $stage): bool
    {
        if ($stage === null || $stage === '') {
            return true;
        }

        return in_array($stage, $this->stages(), true);
    }

    public function ensureValidStage(?string $stage): void
    {
        if (! $this->isValidStage($stage)) {
            throw new InvalidArgumentException('Invalid project lifecycle stage.');
        }
    }
}
