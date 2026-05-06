<?php

namespace App\Http\Controllers;

use App\Models\GoogleLead;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GoogleAdsLeadController extends Controller
{
    public function receive(Request $request): JsonResponse
    {
        try {
            $payload = $request->json()->all();

            $request->validate([
                'lead_id' => ['required', 'string'],
                'google_key' => ['required', 'string'],
                'user_column_data' => ['nullable', 'array'],
                'user_column_data.*.column_id' => ['required_with:user_column_data', 'string'],
                'user_column_data.*.string_value' => ['nullable', 'string'],
                'form_id' => ['nullable', 'integer'],
                'campaign_id' => ['nullable', 'integer'],
                'gcl_id' => ['nullable', 'string'],
                'is_test' => ['nullable', 'boolean'],
                'lead_stage' => ['nullable', 'string'],
                'lead_submit_time' => ['nullable', 'date'],
            ]);

            if (($payload['google_key'] ?? null) !== config('services.google_ads.webhook_key')) {
                Log::warning('Google Ads webhook key mismatch.', [
                    'lead_id' => $payload['lead_id'] ?? null,
                    'ip' => $request->ip(),
                ]);

                return response()->json([], 401);
            }

            if (GoogleLead::where('lead_id', $payload['lead_id'])->exists()) {
                return response()->json([], 200);
            }

            $mappedData = [
                'full_name' => null,
                'email' => null,
                'phone' => null,
                'company' => null,
            ];

            foreach (($payload['user_column_data'] ?? []) as $item) {
                $columnId = $item['column_id'] ?? null;
                $value = $item['string_value'] ?? null;

                if ($columnId === 'FULL_NAME') {
                    $mappedData['full_name'] = $value;
                } elseif ($columnId === 'EMAIL') {
                    $mappedData['email'] = $value;
                } elseif ($columnId === 'PHONE_NUMBER') {
                    $mappedData['phone'] = $value;
                } elseif ($columnId === 'COMPANY_NAME') {
                    $mappedData['company'] = $value;
                }
            }

            GoogleLead::create([
                'lead_id' => $payload['lead_id'],
                'full_name' => $mappedData['full_name'],
                'email' => $mappedData['email'],
                'phone' => $mappedData['phone'],
                'company' => $mappedData['company'],
                'form_id' => $payload['form_id'] ?? null,
                'campaign_id' => $payload['campaign_id'] ?? null,
                'gcl_id' => $payload['gcl_id'] ?? null,
                'is_test' => (bool) ($payload['is_test'] ?? false),
                'lead_stage' => $payload['lead_stage'] ?? null,
                'lead_submit_time' => $payload['lead_submit_time'] ?? null,
                'raw_payload' => $payload,
            ]);

            Log::info('Google Ads lead stored successfully.', [
                'lead_id' => $payload['lead_id'],
            ]);

            return response()->json([], 200);
        } catch (\Throwable $e) {
            Log::error('Google Ads webhook processing failed.', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([], 500);
        }
    }
}
