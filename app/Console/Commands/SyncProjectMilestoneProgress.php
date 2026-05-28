<?php

namespace App\Console\Commands;

use App\Services\ProjectManagement\MilestoneProgressService;
use Illuminate\Console\Command;

class SyncProjectMilestoneProgress extends Command
{
    protected $signature = 'project-management:sync-milestone-progress';
    protected $description = 'Sync progress percentage and status for all project milestones.';

    public function handle(MilestoneProgressService $service): int
    {
        $service->syncForAllMilestones();
        $this->info('Milestone progress synchronized successfully.');

        return self::SUCCESS;
    }
}

