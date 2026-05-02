<?php

namespace App\Helpers;

use App\Models\FcmToken;
use App\Models\User;
use App\Services\FirebaseFcmService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class FcmNotificationHelper
{
    public static function storeTokenForUser(User $user, ?string $token, ?string $deviceId = null, ?string $platform = null): ?FcmToken
    {
        $token = trim((string) $token);

        if ($token === '') {
            return null;
        }

        return FcmToken::updateOrCreate(
            [
                'user_id' => $user->id,
                'token' => $token,
            ],
            [
                'device_id' => $deviceId,
                'platform' => $platform,
                'is_active' => true,
                'last_used_at' => Carbon::now(),
            ]
        );
    }

    public static function sendToLoggedInUser(string $title, string $body, array $data = []): array
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            return [
                'success' => false,
                'sent' => 0,
                'failed' => 0,
                'message' => 'No authenticated user found.',
            ];
        }

        return self::sendToUser($user, $title, $body, $data);
    }

    public static function sendToUser(User $user, string $title, string $body, array $data = []): array
    {
        $tokens = FcmToken::query()
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->pluck('token')
            ->unique()
            ->values();

        if ($tokens->isEmpty()) {
            return [
                'success' => false,
                'sent' => 0,
                'failed' => 0,
                'message' => 'No active FCM tokens found for user.',
            ];
        }

        $sent = 0;
        $failed = 0;
        $errors = [];
        $fcmService = app(FirebaseFcmService::class);

        foreach ($tokens as $token) {
            try {
                $fcmService->sendToToken((string) $token, $title, $body, $data);
                $sent++;
            } catch (\Throwable $exception) {
                $failed++;
                $errors[] = $exception->getMessage();

                // Deactivate invalid/unregistered tokens to avoid repeated failures.
                if (
                    str_contains($exception->getMessage(), 'UNREGISTERED')
                    || str_contains($exception->getMessage(), 'registration token is not a valid FCM registration token')
                ) {
                    FcmToken::query()
                        ->where('user_id', $user->id)
                        ->where('token', $token)
                        ->update(['is_active' => false]);
                }

                Log::warning('FCM send failed', [
                    'user_id' => $user->id,
                    'token' => $token,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        return [
            'success' => $sent > 0,
            'sent' => $sent,
            'failed' => $failed,
            'errors' => $errors,
        ];
    }
}

