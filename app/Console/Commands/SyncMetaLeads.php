<?php

namespace App\Console\Commands;

use App\Services\MetaLeadService;
use Illuminate\Console\Command;

class SyncMetaLeads extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'meta:sync-leads {--form_id= : Optional form ID to sync}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manually sync leads from Meta Lead Ads';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(MetaLeadService $metaLeadService)
    {
        $formId = $this->option('form_id');
        $count = $metaLeadService->syncLeadsFromForm($formId);

        $this->info("Synced {$count} Meta lead(s).");

        return Command::SUCCESS;
    }
}
