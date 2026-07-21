<?php

namespace App\Jobs;

use App\Models\MetaLead;
use App\Models\MetaLeadWhatsappMessageLog;
use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendMetaLeadWelcomeWhatsappJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public int $metaLeadId,
        public ?int $logId = null
    ) {
    }

    public function handle(WhatsAppService $whatsAppService): void
    {
        $lead = MetaLead::query()->find($this->metaLeadId);
        if (! $lead) {
            return;
        }

        if ($lead->welcome_whatsapp_status === MetaLead::WELCOME_WHATSAPP_SENT) {
            return;
        }

        $templateName = (string) config('services.k3_whatsapp.meta_lead_welcome_template');
        $templateParameters = $this->templateParameters($lead);
        $phone = $whatsAppService->normalizePhoneNumber($lead->phone);
        $log = $this->resolveLog($lead, $phone, $templateName, $templateParameters);

        if (! $phone) {
            $this->markSkipped($lead, $log, 'Meta lead phone number is missing or invalid.');
            return;
        }

        if ($templateName === '') {
            $this->markSkipped($lead, $log, 'K3 WhatsApp Meta lead welcome template is not configured.');
            return;
        }

        $log->update([
            'phone' => $phone,
            'template_name' => $templateName,
            'template_parameters' => $templateParameters,
            'status' => MetaLeadWhatsappMessageLog::STATUS_QUEUED,
            'attempted_at' => now(),
            'error_message' => null,
        ]);

        $headerMedia = [
            'type' => 'video',
            'link' => config('services.k3_whatsapp.meta_lead_welcome_header_video_url') ?: asset('video/technofra_video.mp4'),
        ];

        $result = $whatsAppService->sendTemplateMessageWithResult(
            $phone,
            $templateName,
            $templateParameters,
            config('services.k3_whatsapp.default_language'),
            $headerMedia
        );

        $lead->increment('welcome_whatsapp_attempts');

        if ((bool) ($result['successful'] ?? false)) {
            $log->update([
                'status' => MetaLeadWhatsappMessageLog::STATUS_SENT,
                'phone' => $result['phone'] ?? $phone,
                'provider_status_code' => $result['provider_status_code'] ?? null,
                'provider_response' => $result['provider_response'] ?? null,
                'error_message' => null,
                'sent_at' => now(),
                'failed_at' => null,
            ]);

            $lead->forceFill([
                'welcome_whatsapp_status' => MetaLead::WELCOME_WHATSAPP_SENT,
                'welcome_whatsapp_sent_at' => now(),
                'welcome_whatsapp_last_log_id' => $log->id,
            ])->save();

            return;
        }

        $log->update([
            'status' => MetaLeadWhatsappMessageLog::STATUS_FAILED,
            'phone' => $result['phone'] ?? $phone,
            'provider_status_code' => $result['provider_status_code'] ?? null,
            'provider_response' => $result['provider_response'] ?? null,
            'error_message' => $result['error_message'] ?? 'WhatsApp provider returned a failure response.',
            'failed_at' => now(),
        ]);

        $lead->forceFill([
            'welcome_whatsapp_status' => MetaLead::WELCOME_WHATSAPP_FAILED,
            'welcome_whatsapp_last_log_id' => $log->id,
        ])->save();
    }

    public function failed(\Throwable $exception): void
    {
        $lead = MetaLead::query()->find($this->metaLeadId);
        $log = $this->logId ? MetaLeadWhatsappMessageLog::query()->find($this->logId) : null;

        if ($log) {
            $log->update([
                'status' => MetaLeadWhatsappMessageLog::STATUS_FAILED,
                'error_message' => $exception->getMessage(),
                'failed_at' => now(),
            ]);
        }

        if ($lead) {
            $lead->forceFill([
                'welcome_whatsapp_status' => MetaLead::WELCOME_WHATSAPP_FAILED,
                'welcome_whatsapp_last_log_id' => $log?->id,
            ])->save();
        }

        Log::error('Failed to send Meta lead welcome WhatsApp job', [
            'meta_lead_id' => $this->metaLeadId,
            'error' => $exception->getMessage(),
        ]);
    }

    private function resolveLog(MetaLead $lead, ?string $phone, string $templateName, array $templateParameters): MetaLeadWhatsappMessageLog
    {
        $log = $this->logId ? MetaLeadWhatsappMessageLog::query()->find($this->logId) : null;

        if ($log) {
            return $log;
        }

        return MetaLeadWhatsappMessageLog::create([
            'meta_lead_id' => $lead->id,
            'phone' => $phone,
            'template_name' => $templateName ?: null,
            'template_parameters' => $templateParameters,
            'status' => MetaLeadWhatsappMessageLog::STATUS_QUEUED,
        ]);
    }

    private function markSkipped(MetaLead $lead, MetaLeadWhatsappMessageLog $log, string $reason): void
    {
        $log->update([
            'status' => MetaLeadWhatsappMessageLog::STATUS_SKIPPED,
            'error_message' => $reason,
            'failed_at' => now(),
        ]);

        $lead->forceFill([
            'welcome_whatsapp_status' => MetaLead::WELCOME_WHATSAPP_SKIPPED,
            'welcome_whatsapp_last_log_id' => $log->id,
        ])->save();
    }

    private function templateParameters(MetaLead $lead): array
    {
        return [
            $lead->full_name ?: 'there',
        ];
    }
}
