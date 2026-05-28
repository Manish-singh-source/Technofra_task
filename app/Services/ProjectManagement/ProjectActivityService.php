<?php

namespace App\Services\ProjectManagement;

use App\Models\ProjectActivity;

class ProjectActivityService
{
    public function log(
        int $projectId,
        string $activityType,
        ?string $title = null,
        ?string $description = null,
        ?int $taskId = null,
        ?int $userId = null,
        array $meta = [],
        ?string $activityAt = null
    ): void {
        if (! \Illuminate\Support\Facades\Schema::hasTable('project_activities')) {
            return;
        }

        ProjectActivity::create([
            'project_id' => $projectId,
            'task_id' => $taskId,
            'user_id' => $userId,
            'activity_type' => $activityType,
            'title' => $title,
            'description' => $description,
            'meta' => empty($meta) ? null : $meta,
            'activity_at' => $activityAt ?? now(),
        ]);
    }
}
