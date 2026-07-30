<?php

namespace App\Http\Controllers;

use App\Services\MetaLeadService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class FacebookWebhookController extends Controller
{
    /**
     * Verify Meta webhook subscription.
     */
    public function verify(Request $request): Response
    {
        $mode = $request->query('hub_mode') ?? $request->query('hub.mode');
        $token = $request->query('hub_verify_token') ?? $request->query('hub.verify_token');
        $challenge = $request->query('hub_challenge') ?? $request->query('hub.challenge');

        if ($mode === 'subscribe' && $token === (string) config('meta.webhook_verify_token')) {
            Log::info('Meta webhook verification succeeded.', [
                'mode' => $mode,
                'has_challenge' => filled($challenge),
            ]);

            return response((string) $challenge, 200);
        }

        Log::warning('Meta webhook verification failed.', [
            'mode' => $mode,
            'has_token' => filled($token),
            'has_challenge' => filled($challenge),
        ]);

        return response('Forbidden', 403);
    }

    /**
     * Handle Meta leadgen webhook events.
     */
    public function handle(Request $request, MetaLeadService $metaLeadService): Response
    {
        try {
            $payload = $request->all();
            $entries = $payload['entry'] ?? [];
            $processedLeadgenIds = [];

            Log::info('Meta webhook received.', [
                'object' => $payload['object'] ?? null,
                'entry_count' => count($entries),
            ]);

            foreach ($entries as $entry) {
                $changes = $entry['changes'] ?? [];

                foreach ($changes as $change) {
                    if (($change['field'] ?? null) !== 'leadgen') {
                        Log::info('Meta webhook change skipped.', [
                            'field' => $change['field'] ?? null,
                        ]);

                        continue;
                    }

                    $leadgenId = $change['value']['leadgen_id'] ?? null;
                    if ($leadgenId) {
                        $metaLeadService->fetchAndStoreLead($leadgenId);
                        $processedLeadgenIds[] = $leadgenId;
                    } else {
                        Log::warning('Meta webhook leadgen change missing leadgen ID.', [
                            'page_id' => $change['value']['page_id'] ?? null,
                            'form_id' => $change['value']['form_id'] ?? null,
                        ]);
                    }
                }
            }

            Log::info('Meta webhook processing completed.', [
                'processed_count' => count($processedLeadgenIds),
                'leadgen_ids' => $processedLeadgenIds,
            ]);
        } catch (\Throwable $e) {
            Log::error('Error processing Meta webhook', [
                'message' => $e->getMessage(),
                'exception' => get_class($e),
            ]);
        }

        return response('EVENT_RECEIVED', 200);
    }
}
