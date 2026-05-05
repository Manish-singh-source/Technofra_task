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
            return response((string) $challenge, 200);
        }

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

            foreach ($entries as $entry) {
                $changes = $entry['changes'] ?? [];

                foreach ($changes as $change) {
                    if (($change['field'] ?? null) !== 'leadgen') {
                        continue;
                    }

                    $leadgenId = $change['value']['leadgen_id'] ?? null;
                    if ($leadgenId) {
                        $metaLeadService->fetchAndStoreLead($leadgenId);
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::error('Error processing Meta webhook', [
                'message' => $e->getMessage(),
            ]);
        }

        return response('EVENT_RECEIVED', 200);
    }
}
