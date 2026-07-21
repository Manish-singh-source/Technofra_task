<?php

namespace App\Services;

use App\Jobs\SendMetaLeadWelcomeWhatsappJob;
use App\Models\MetaLead;
use App\Models\MetaLeadWhatsappMessageLog;
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

        $fieldData = $data['field_data'] ?? $data['fieldData'] ?? [];
        $mappedFields = $this->mapFieldData($fieldData);
        $attributes = [
            'form_id' => $data['form_id'] ?? null,
            'page_id' => $data['page_id'] ?? config('meta.page_id'),
            'ad_id' => $data['ad_id'] ?? null,
            'field_data' => $fieldData,
            'created_time' => isset($data['created_time']) ? Carbon::parse($data['created_time']) : null,
        ];

        foreach (['full_name', 'email', 'phone', 'city', 'state'] as $column) {
            if (array_key_exists($column, $mappedFields)) {
                $attributes[$column] = $mappedFields[$column];
            }
        }

        $lead = MetaLead::updateOrCreate(
            ['lead_id' => $leadId],
            $attributes
        );

        if ($lead->wasRecentlyCreated) {
            $this->queueWelcomeWhatsapp($lead);
        }

        return $lead;
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

            $normalizedName = $this->normalizeFieldName((string) $name);

            if (in_array($normalizedName, ['full_name', 'fullname', 'name'], true)) {
                $mapped['full_name'] = $value;
            } elseif (in_array($normalizedName, ['email', 'email_address'], true)) {
                $mapped['email'] = $value;
            } elseif (in_array($normalizedName, ['phone', 'phone_number', 'mobile', 'mobile_number', 'contact_number'], true)) {
                $mapped['phone'] = $value;
            } elseif (in_array($normalizedName, ['city', 'town', 'current_city'], true)) {
                $mapped['city'] = $value;
            } elseif (in_array($normalizedName, ['state', 'province', 'region'], true)) {
                $mapped['state'] = $value;
            }
        }

        return $mapped;
    }

    private function queueWelcomeWhatsapp(MetaLead $lead): void
    {
        $templateName = (string) config('services.k3_whatsapp.meta_lead_welcome_template');
        $templateParameters = [
            $lead->full_name ?: 'there',
        ];

        if (blank($lead->phone)) {
            $log = MetaLeadWhatsappMessageLog::create([
                'meta_lead_id' => $lead->id,
                'phone' => null,
                'template_name' => $templateName ?: null,
                'template_parameters' => $templateParameters,
                'status' => MetaLeadWhatsappMessageLog::STATUS_SKIPPED,
                'error_message' => 'Meta lead phone number is missing.',
                'failed_at' => now(),
            ]);

            $lead->forceFill([
                'welcome_whatsapp_status' => MetaLead::WELCOME_WHATSAPP_SKIPPED,
                'welcome_whatsapp_last_log_id' => $log->id,
            ])->save();

            return;
        }

        $log = MetaLeadWhatsappMessageLog::create([
            'meta_lead_id' => $lead->id,
            'phone' => $lead->phone,
            'template_name' => $templateName ?: null,
            'template_parameters' => $templateParameters,
            'status' => MetaLeadWhatsappMessageLog::STATUS_QUEUED,
        ]);

        $lead->forceFill([
            'welcome_whatsapp_status' => MetaLead::WELCOME_WHATSAPP_QUEUED,
            'welcome_whatsapp_last_log_id' => $log->id,
        ])->save();

        SendMetaLeadWelcomeWhatsappJob::dispatch($lead->id, $log->id);
    }

    private function normalizeFieldName(string $name): string
    {
        $name = strtolower(trim($name));
        $name = preg_replace('/[^a-z0-9]+/', '_', $name) ?? $name;

        return trim($name, '_');
    }
}
