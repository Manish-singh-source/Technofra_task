<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\User;
use App\Notifications\InAppPushNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UnifiedNotificationService
{
    /**
     * Save an in-app database notification.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function sendToUser(User $user, string $title, string $body, ?string $type = null, array $data = []): array
    {
        $mobileEnabled = ! in_array(
            strtolower((string) Setting::get('mobile_notifications_enabled', '1')),
            ['0', 'false', 'off', 'no'],
            true
        );

        if (! $mobileEnabled) {
            return [
                'success' => false,
                'database' => [
                    'stored' => false,
                    'notification_id' => null,
                ],
                'push' => [
                    'success' => false,
                    'sent' => 0,
                    'failed' => 0,
                    'message' => 'Mobile notifications are disabled in settings.',
                ],
            ];
        }

        $storedInDatabase = false;
        $notificationId = null;
        $notification = new InAppPushNotification($title, $body, $type, $data);

        try {
            $user->notify($notification);
            $storedInDatabase = true;
            $notificationId = $notification->id;
        } catch (\Throwable $exception) {
            Log::warning('Failed to store database notification', [
                'user_id' => $user->id,
                'title' => $title,
                'error' => $exception->getMessage(),
            ]);
        }

        return [
            'success' => $storedInDatabase,
            'database' => [
                'stored' => $storedInDatabase,
                'notification_id' => $notificationId,
            ],
            'push' => [
                'success' => false,
                'sent' => 0,
                'failed' => 0,
                'message' => 'Push notifications are disabled; in-app notification was used.',
            ],
        ];
    }

    /**
     * Save in-app database notification and send push to auth user.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function sendToLoggedInUser(string $title, string $body, ?string $type = null, array $data = []): array
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            return [
                'success' => false,
                'database' => [
                    'stored' => false,
                    'notification_id' => null,
                ],
                'push' => [
                    'success' => false,
                    'sent' => 0,
                    'failed' => 0,
                    'message' => 'No authenticated user found.',
                ],
            ];
        }

        return $this->sendToUser($user, $title, $body, $type, $data);
    }

    /**
     * Kept for backward compatibility; external push notifications are disabled.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function sendPushOnlyToUser(User $user, string $title, string $body, array $data = []): array
    {
        return [
            'success' => false,
            'sent' => 0,
            'failed' => 0,
            'message' => 'Push notifications are disabled.',
        ];
    }
}
