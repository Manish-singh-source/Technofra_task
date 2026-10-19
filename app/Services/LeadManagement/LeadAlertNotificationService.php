<?php

namespace App\Services\LeadManagement;

use App\Helpers\FcmNotificationHelper;
use App\Mail\LeadAlertMail;
use App\Models\Lead;
use App\Models\User;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class LeadAlertNotificationService
{
    public function __construct(private readonly WhatsAppService $whatsApp) {}

    public function send(Lead $lead, string $alertType, string $alertLabel): int
    {
        $staff = User::staffMembers()->whereIn('id', $lead->assigned ?? [])->get();
        $admins = User::query()->whereIn('role', ['admin', 'super_admin', 'super-admin', 'super_admin2'])->get();
        $recipients = $staff->merge($admins)->unique('id');
        $sent = 0;

        foreach ($recipients as $recipient) {
            $dedupeKey = "lead-alert:{$lead->id}:{$recipient->id}:{$alertType}:" . now()->toDateString();
            if (! Cache::add($dedupeKey, true, now()->endOfDay())) {
                continue;
            }

            $url = route('lead-management.show', ['source' => 'lead', 'id' => $lead->id]);
            $title = "Lead {$alertLabel}";
            $body = sprintf('%s needs attention. Priority: %s.', $lead->name ?: 'Unnamed lead', ucfirst($lead->priority ?: 'medium'));

            try {
                $recipient->notify(new \App\Notifications\InAppPushNotification($title, $body, 'lead_alert', ['lead_id' => $lead->id, 'alert_type' => $alertType]));
                if (filled($recipient->email)) {
                    Mail::to($recipient->email)->send(new LeadAlertMail($alertType, $alertLabel, $lead, $url));
                }
                if (filled($recipient->phone)) {
                    $this->whatsApp->sendTemplateMessage(
                        $recipient->phone,
                        config('services.k3_whatsapp.lead_alert_template', 'lead_alert'),
                        [$lead->name ?: 'Unnamed lead', $alertLabel, ucfirst($lead->priority ?: 'medium'), $lead->next_followup_at?->format('d M Y, h:i A') ?: 'No next action', $url]
                    );
                }
                FcmNotificationHelper::sendToUser($recipient, $title, $body, ['lead_id' => (string) $lead->id, 'alert_type' => $alertType]);
                $sent++;
            } catch (\Throwable $exception) {
                Cache::forget($dedupeKey);
                Log::warning('Lead alert delivery failed', ['lead_id' => $lead->id, 'user_id' => $recipient->id, 'error' => $exception->getMessage()]);
            }
        }

        return $sent;
    }
}
