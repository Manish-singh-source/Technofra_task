<?php

namespace App\Services;

use App\Models\MetaLead;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MetaLeadService
{
    /**
     * Fetch a lead from Meta by leadgen ID and store it locally.
     */
    public function fetchAndStoreLead(string $leadgenId): ?MetaLead
    {
        try {
            $token = (string) config('meta.page_access_token');
            $version = (string) config('meta.graph_api_version', 'v20.0');

            $response = Http::get("https://graph.facebook.com/{$version}/{$leadgenId}", [
                'fields' => 'id,created_time,field_data,form_id,page_id,ad_id',
                'access_token' => $token,
            ]);

            if (! $response->successful()) {
                Log::error('Failed to fetch Meta lead', [
                    'leadgen_id' => $leadgenId,
                    'status' => $response->status(),
                    'response' => $response->json(),
                ]);

                return null;
            }

            $data = $response->json();

            return $this->storeLeadData($data);
        } catch (\Throwable $e) {
            Log::error('Exception while fetching Meta lead', [
                'leadgen_id' => $leadgenId,
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Sync leads from a lead form and return the count of synced leads.
     */
    public function syncLeadsFromForm(string $formId = null): int
    {
        $syncedCount = 0;
        $formId = $formId ?: (string) config('meta.form_id');

        if (empty($formId)) {
            Log::error('Meta form ID is missing for lead sync');

            return 0;
        }

        try {
            $token = (string) config('meta.page_access_token');
            $version = (string) config('meta.graph_api_version', 'v20.0');

            $nextUrl = "https://graph.facebook.com/{$version}/{$formId}/leads?fields=id,created_time,field_data,form_id,page_id,ad_id&access_token={$token}";

            while ($nextUrl) {
                $response = Http::get($nextUrl);

                if (! $response->successful()) {
                    Log::error('Failed to sync Meta leads from form', [
                        'form_id' => $formId,
                        'status' => $response->status(),
                        'response' => $response->json(),
                    ]);

                    break;
                }

                $payload = $response->json();
                $leads = $payload['data'] ?? [];

                foreach ($leads as $leadData) {
                    $stored = $this->storeLeadData($leadData);
                    if ($stored) {
                        $syncedCount++;
                    }
                }

                $nextUrl = $payload['paging']['next'] ?? null;
            }
        } catch (\Throwable $e) {
            Log::error('Exception while syncing Meta leads from form', [
                'form_id' => $formId,
                'message' => $e->getMessage(),
            ]);
        }

        return $syncedCount;
    }

    /**
     * Normalize and persist a Meta lead payload.
     */
    protected function storeLeadData(array $data): ?MetaLead
    {
        $leadId = $data['id'] ?? null;

        if (! $leadId) {
            return null;
        }

        $fieldData = $data['field_data'] ?? [];
        $mappedFields = $this->mapFieldData($fieldData);

        return MetaLead::updateOrCreate(
            ['lead_id' => $leadId],
            [
                'form_id' => $data['form_id'] ?? null,
                'page_id' => $data['page_id'] ?? null,
                'ad_id' => $data['ad_id'] ?? null,
                'full_name' => $mappedFields['full_name'] ?? null,
                'email' => $mappedFields['email'] ?? null,
                'phone' => $mappedFields['phone'] ?? null,
                'city' => $mappedFields['city'] ?? null,
                'state' => $mappedFields['state'] ?? null,
                'field_data' => $fieldData,
                'created_time' => isset($data['created_time']) ? Carbon::parse($data['created_time']) : null,
            ]
        );
    }

    /**
     * Map Meta field_data entries to known fields.
     */
    protected function mapFieldData(array $fieldData): array
    {
        $mapped = [];

        foreach ($fieldData as $field) {
            $name = $field['name'] ?? null;
            $values = $field['values'] ?? [];
            $value = is_array($values) ? ($values[0] ?? null) : null;

            if (! $name) {
                continue;
            }

            if ($name === 'full_name') {
                $mapped['full_name'] = $value;
            } elseif ($name === 'email') {
                $mapped['email'] = $value;
            } elseif ($name === 'phone_number') {
                $mapped['phone'] = $value;
            } elseif ($name === 'city') {
                $mapped['city'] = $value;
            } elseif ($name === 'state') {
                $mapped['state'] = $value;
            }
        }

        return $mapped;
    }
}
