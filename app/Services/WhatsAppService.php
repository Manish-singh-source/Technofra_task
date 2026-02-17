<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected $baseUrl;
    protected $businessId;
    protected $apiKey;
    protected $defaultCountryCode;
    protected $defaultLanguage;
    protected $reminderTemplate;
    protected $eventTimeTemplate;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('services.k3_whatsapp.base_url'), '/');
        $this->businessId = (string) config('services.k3_whatsapp.business_id');
        $this->apiKey = (string) config('services.k3_whatsapp.api_key');
        $this->defaultCountryCode = (string) config('services.k3_whatsapp.default_country_code', '91');
        $this->defaultLanguage = (string) config('services.k3_whatsapp.default_language', 'en');
        $this->reminderTemplate = (string) config('services.k3_whatsapp.reminder_template', 'calendar_appointment_reminder');
        $this->eventTimeTemplate = (string) config('services.k3_whatsapp.event_time_template', 'calendar_appointment_reminder');
    }

    /**
     * Send WhatsApp template message via K3 API.
     */
    public function sendTemplateMessage($to, $templateName, array $templateParameters = [], $languageCode = null)
    {
        try {
            if (empty($this->baseUrl) || empty($this->businessId) || empty($this->apiKey)) {
                Log::error('K3 WhatsApp configuration missing in .env file');
                return false;
            }

            $to = $this->formatPhoneNumber($to);
            if (!$to) {
                Log::error('Invalid WhatsApp phone number format');
                return false;
            }

            if (empty($templateName)) {
                Log::error('K3 template name is required');
                return false;
            }

            $components = [];
            if (!empty($templateParameters)) {
                $components[] = [
                    'type' => 'body',
                    'parameters' => array_map(function ($value) {
                        return [
                            'type' => 'text',
                            'text' => (string) $value,
                        ];
                    }, array_values($templateParameters)),
                ];
            }

            $payload = [
                'messaging_product' => 'whatsapp',
                'recipient_type' => 'individual',
                'to' => $to,
                'type' => 'template',
                'template' => [
                    'name' => $templateName,
                    'language' => [
                        'code' => $languageCode ?: $this->defaultLanguage,
                    ],
                    'components' => $components,
                ],
            ];

            $url = "{$this->baseUrl}/v3/{$this->businessId}/messages";

            $response = Http::withHeaders([
                'apikey' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($url, $payload);

            if ($response->successful()) {
                Log::info("K3 WhatsApp template sent successfully to: {$to}");
                return true;
            }

            Log::error("Failed to send K3 WhatsApp template to {$to}: " . $response->body());
            return false;
        } catch (Exception $e) {
            Log::error("K3 WhatsApp send error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Backward compatible alias.
     */
    public function sendMessage($to, $message)
    {
        return $this->sendTemplateMessage(
            $to,
            $this->reminderTemplate,
            [
                (string) $message,
                now()->format('d M Y'),
                now()->format('h:i A'),
            ]
        );
    }

    /**
     * Format phone number in international format without plus.
     */
    protected function formatPhoneNumber($phone)
    {
        $phone = preg_replace('/\D+/', '', (string) $phone);
        if (empty($phone)) {
            return null;
        }

        if (strlen($phone) === 10) {
            $phone = $this->defaultCountryCode . $phone;
        }

        if (preg_match('/^[1-9]\d{7,14}$/', $phone)) {
            return $phone;
        }

        return null;
    }

    /**
     * Send calendar reminder/event notification via configured K3 template.
     */
    public function sendCalendarEventNotification($event, $recipients, $type = 'reminder')
    {
        $successCount = 0;
        $failedCount = 0;

        $templateName = $type === 'event_time'
            ? $this->eventTimeTemplate
            : $this->reminderTemplate;
        $templateParams = $this->buildTemplateParameters($event, $type);

        foreach ($recipients as $phone) {
            if ($this->sendTemplateMessage($phone, $templateName, $templateParams)) {
                $successCount++;
            } else {
                $failedCount++;
            }
        }

        return [
            'success' => $successCount,
            'failed' => $failedCount,
        ];
    }

    /**
     * Build 3 body parameters: event title, date and time.
     */
    protected function buildTemplateParameters($event, $type)
    {
        return [
            (string) $event->title,
            $event->event_date->format('d M Y'),
            $event->event_time->format('h:i A'),
        ];
    }
}
