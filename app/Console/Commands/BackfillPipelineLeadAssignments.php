<?php

namespace App\Console\Commands;

use App\Models\AssignedLead;
use App\Models\Lead;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillPipelineLeadAssignments extends Command
{
    protected $signature = 'leads:backfill-pipeline-assignments {--dry-run : Show what would change without writing}';

    protected $description = 'Backfill assigned_leads rows for pipeline leads (lead_model=lead) based on source-model assignments.';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $sources = [
            'digital_marketing' => ['table' => 'digital_marketing_leads', 'email' => 'email', 'phone' => 'phone'],
            'webapp' => ['table' => 'webapp_leads', 'email' => 'email', 'phone' => 'phone'],
            'meta' => ['table' => 'meta_leads', 'email' => 'email', 'phone' => 'phone'],
            'google' => ['table' => 'google_leads', 'email' => 'email', 'phone' => 'phone'],
        ];

        $totalSeen = 0;
        $totalMatched = 0;
        $totalUpserted = 0;
        $totalSkipped = 0;

        foreach ($sources as $leadModelKey => $meta) {
            if (! DB::getSchemaBuilder()->hasTable($meta['table'])) {
                $this->warn("Skipping {$leadModelKey}: missing table {$meta['table']}");
                continue;
            }

            $this->info("Processing {$leadModelKey}...");

            AssignedLead::query()
                ->where('lead_model', $leadModelKey)
                ->orderBy('id')
                ->chunkById(500, function ($rows) use ($meta, $leadModelKey, $dryRun, &$totalSeen, &$totalMatched, &$totalUpserted, &$totalSkipped) {
                    foreach ($rows as $row) {
                        $totalSeen++;

                        $source = DB::table($meta['table'])->where('id', (int) $row->lead_id)->first([$meta['email'], $meta['phone']]);
                        if (! $source) {
                            $totalSkipped++;
                            continue;
                        }

                        $email = $this->normalizeContact($source->{$meta['email']} ?? null);
                        $phone = $this->normalizeContact($source->{$meta['phone']} ?? null);

                        if ($email === '' && $phone === '') {
                            $totalSkipped++;
                            continue;
                        }

                        $lead = $this->findPipelineLead($email, $phone);
                        if (! $lead) {
                            $totalSkipped++;
                            continue;
                        }

                        $totalMatched++;

                        $staffIds = collect($row->staff_ids ?? [])
                            ->map(fn ($v) => (int) $v)
                            ->filter(fn ($v) => $v > 0)
                            ->unique()
                            ->values()
                            ->all();

                        if ($staffIds === []) {
                            $totalSkipped++;
                            continue;
                        }

                        if ($dryRun) {
                            $this->line("Would upsert pipeline assignment: source={$leadModelKey} source_id={$row->lead_id} -> lead_id={$lead->id} staff_ids=[".implode(',', $staffIds)."]");
                            continue;
                        }

                        AssignedLead::updateOrCreate(
                            ['lead_model' => 'lead', 'lead_id' => (int) $lead->id],
                            ['staff_ids' => $staffIds]
                        );
                        $totalUpserted++;
                    }
                });
        }

        $this->info("Done. seen={$totalSeen}, matched={$totalMatched}, upserted={$totalUpserted}, skipped={$totalSkipped}".($dryRun ? ' (dry-run)' : ''));

        return Command::SUCCESS;
    }

    private function normalizeContact(mixed $value): string
    {
        $value = trim((string) ($value ?? ''));
        if ($value === '' || $value === '-') {
            return '';
        }

        return mb_strtolower($value);
    }

    private function findPipelineLead(string $email, string $phone): ?Lead
    {
        return Lead::query()
            ->when($email !== '', fn ($q) => $q->where('email', $email))
            ->when($phone !== '', fn ($q) => $q->orWhere('phone', $phone))
            ->first();
    }
}

