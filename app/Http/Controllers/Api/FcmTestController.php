<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\FirebaseFcmService;
use Illuminate\Http\Request;
use Throwable;

class FcmTestController extends Controller
{
    public function send(Request $request, FirebaseFcmService $fcm)
    {
        $data = $request->validate([
            'token' => ['nullable', 'string'],
            'fcm_token' => ['nullable', 'string'],
            'title' => ['required', 'string'],
            'body' => ['required', 'string'],
        ]);

        $token = $data['token'] ?? $data['fcm_token'] ?? null;

        if (! $token) {
            return response()->json([
                'status' => 'error',
                'message' => 'FCM device token is required.',
            ], 422);
        }

        try {
            $result = $fcm->sendToToken(
                $token,
                $data['title'],
                $data['body'],
                ['screen' => 'home']
            );
        } catch (Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'status' => 'sent',
            'fcm_response' => $result,
        ]);
    }
}
