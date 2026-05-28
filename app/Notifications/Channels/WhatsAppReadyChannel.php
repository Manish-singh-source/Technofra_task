<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class WhatsAppReadyChannel
{
    public function send(object $notifiable, Notification $notification): void
    {
        if (! method_exists($notification, 'toWhatsAppReady')) {
            return;
        }

        $payload = $notification->toWhatsAppReady($notifiable);

        Log::info('WhatsApp-ready notification prepared', [
            'notifiable_id' => $notifiable->id ?? null,
            'notifiable_type' => get_class($notifiable),
            'payload' => $payload,
        ]);
    }
}
