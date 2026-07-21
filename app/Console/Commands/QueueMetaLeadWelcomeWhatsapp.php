<?php

namespace App\Console\Commands;

use App\Jobs\SendMetaLeadWelcomeWhatsappJob;
use App\Models\MetaLead;
use App\Models\MetaLeadWhatsappMessageLog;
use Illuminate\Console\Command;

class QueueMetaLeadWelcomeWhatsapp extends Command
{
    protected $signature = 'meta:queue-welcome-whatsapp
        {--limit=100 : Maximum number of leads to queue}
        {--retry-failed : Include leads previously marked failed or skipped}';

    protected $description = 'Queue WhatsApp welcome messages for unsent Meta leads';

    public function handle(): int
    {
        $limit = max(1, min((int) $this->option('limit'), 1000));
        $retryFailed = (bool) $this->option('retry-failed');

        $query = MetaLead::query()
            ->where('welcome_whatsapp_status', '!=', MetaLead::WELCOME_WHATSAPP_SENT)
            ->where('welcome_whatsapp_status', '!=', MetaLead::WELCOME_WHATSAPP_QUEUED);

        if (! $retryFailed) {
            $query->whereNotIn('welcome_whatsapp_status', [
                MetaLead::WELCOME_WHATSAPP_FAILED,
                MetaLead::WELCOME_WHATSAPP_SKIPPED,
            ]);
        }

        $leads = $query->orderBy('id')->limit($limit)->get();
        $queued = 0;

        foreach ($leads as $lead) {
            if (blank($lead->phone)) {
                $log = MetaLeadWhatsappMessageLog::create([
                    'meta_lead_id' => $lead->id,
                    'phone' => null,
                    'template_name' => config('services.k3_whatsapp.meta_lead_welcome_template') ?: null,
                    'template_parameters' => $this->templateParameters($lead),
                    'status' => MetaLeadWhatsappMessageLog::STATUS_SKIPPED,
                    'error_message' => 'Meta lead phone number is missing.',
                    'failed_at' => now(),
                ]);

                $lead->forceFill([
                    'welcome_whatsapp_status' => MetaLead::WELCOME_WHATSAPP_SKIPPED,
                    'welcome_whatsapp_last_log_id' => $log->id,
                ])->save();

                continue;
            }

            $log = MetaLeadWhatsappMessageLog::create([
                'meta_lead_id' => $lead->id,
                'phone' => $lead->phone,
                'template_name' => config('services.k3_whatsapp.meta_lead_welcome_template') ?: null,
                'template_parameters' => $this->templateParameters($lead),
                'status' => MetaLeadWhatsappMessageLog::STATUS_QUEUED,
            ]);

            $lead->forceFill([
                'welcome_whatsapp_status' => MetaLead::WELCOME_WHATSAPP_QUEUED,
                'welcome_whatsapp_last_log_id' => $log->id,
            ])->save();

            SendMetaLeadWelcomeWhatsappJob::dispatch($lead->id, $log->id);
            $queued++;
        }

        $this->info("Queued {$queued} Meta lead welcome WhatsApp message(s).");

        return Command::SUCCESS;
    }

    private function templateParameters(MetaLead $lead): array
    {
        return [
            $lead->full_name ?: 'there',
        ];
    }
}
