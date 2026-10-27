<?php

namespace App\Notifications\Channels;

use App\Helpers\FcmNotificationHelper;
use Illuminate\Notifications\Notification;

class FcmChannel
{
    public function send(object $notifiable, Notification $notification): void
    {
        if (! method_exists($notification, 'toFcm')) {
            return;
        }

        $payload = $notification->toFcm($notifiable);
        FcmNotificationHelper::sendToUser(
            $notifiable,
            (string) ($payload['title'] ?? 'MyCRM notification'),
            (string) ($payload['body'] ?? ''),
            (array) ($payload['data'] ?? [])
        );
    }
}
