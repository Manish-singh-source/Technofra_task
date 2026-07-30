<?php

namespace App\Console\Commands;

use App\Services\MetaLeadService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

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

        Log::info('Meta lead console sync started.', [
            'form_id' => $formId,
        ]);

        $count = $metaLeadService->syncLeadsFromForm($formId);

        Log::info('Meta lead console sync completed.', [
            'form_id' => $formId,
            'synced_count' => $count,
        ]);

        $this->info("Synced {$count} Meta lead(s).");

        return Command::SUCCESS;
    }
}
