<?php

namespace App\Services;

use Illuminate\Support\Facades\{Log, Http};
use Exception;

class WhatsAppService
{
    protected $accountSid;
    protected $authToken;
    protected $fromNumber;

    public function __construct()
    {
        $this->accountSid = env('TWILIO_ACCOUNT_SID');
        $this->authToken = env('TWILIO_AUTH_TOKEN');
        $this->fromNumber = env('TWILIO_WHATSAPP_FROM');
    }

    /**
     * Send WhatsApp message via Twilio API
     *
     * @param string $to Phone number in format: +919876543210
     * @param string $message Message content
     * @return bool
     */
    public function sendMessage($to, $message)
    {
        try {
            // Validate configuration
            if (empty($this->accountSid) || empty($this->authToken) || empty($this->fromNumber)) {
                Log::error('WhatsApp configuration missing in .env file');
                return false;
            }

            // Format phone number
            $to = $this->formatPhoneNumber($to);
            
            if (!$to) {
                Log::error('Invalid phone number format: ' . $to);
                return false;
            }

            // Twilio API endpoint
            $url = "https://api.twilio.com/2010-04-01/Accounts/{$this->accountSid}/Messages.json";

            // Send request to Twilio
            $response = Http::withBasicAuth($this->accountSid, $this->authToken)
                ->asForm()
                ->post($url, [
                    'From' => 'whatsapp:' . $this->fromNumber,
                    'To' => 'whatsapp:' . $to,
                    'Body' => $message,
                ]);

            if ($response->successful()) {
                Log::info("WhatsApp message sent successfully to: {$to}");
                return true;
            } else {
                Log::error("Failed to send WhatsApp message to {$to}: " . $response->body());
                return false;
            }
        } catch (Exception $e) {
            Log::error("WhatsApp send error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Format phone number to international format
     *
     * @param string $phone
     * @return string|null
     */
    protected function formatPhoneNumber($phone)
    {
        // Remove all non-numeric characters except +
        $phone = preg_replace('/[^0-9+]/', '', $phone);

        // If doesn't start with +, add +91 for India (you can change this)
        if (!str_starts_with($phone, '+')) {
            $phone = '+91' . $phone;
        }

        // Validate format
        if (preg_match('/^\+[1-9]\d{1,14}$/', $phone)) {
            return $phone;
        }

        return null;
    }

    /**
     * Send calendar event reminder via WhatsApp
     *
     * @param object $event Calendar event object
     * @param array $recipients Array of phone numbers
     * @param string $type 'reminder' or 'event_time'
     * @return array ['success' => count, 'failed' => count]
     */
    public function sendCalendarEventNotification($event, $recipients, $type = 'reminder')
    {
        $successCount = 0;
        $failedCount = 0;

        foreach ($recipients as $phone) {
            $message = $this->buildEventMessage($event, $type);
            
            if ($this->sendMessage($phone, $message)) {
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
     * Build WhatsApp message for calendar event
     *
     * @param object $event
     * @param string $type
     * @return string
     */
    protected function buildEventMessage($event, $type)
    {
        $emoji = $type === 'reminder' ? '⏰' : '📅';
        $title = $type === 'reminder' ? '*Reminder: Event in 10 Minutes!*' : '*Event Starting Now!*';

        $message = "{$emoji} {$title}\n\n";
        $message .= "📌 *{$event->title}*\n\n";
        
        if ($event->description) {
            $message .= "📝 {$event->description}\n\n";
        }
        
        $message .= "📅 Date: {$event->event_date->format('l, F d, Y')}\n";
        $message .= "🕐 Time: {$event->event_time->format('h:i A')}\n\n";
        
        if ($type === 'reminder') {
            $message .= "⚠️ This event will start in 10 minutes. Please be prepared!\n\n";
        } else {
            $message .= "✅ This event is starting now!\n\n";
        }
        
        $message .= "---\n";
        $message .= "_Technofra Renewal Master_";

        return $message;
    }
}

