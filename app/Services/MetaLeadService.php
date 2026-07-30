<?php

namespace App\Services;

use App\Jobs\SendMetaLeadWelcomeWhatsappJob;
use App\Models\MetaLead;
use App\Models\MetaLeadWhatsappMessageLog;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MetaLeadService
{
    public const CREDENTIAL_KEYS = [
        'FACEBOOK_APP_ID',
        'FACEBOOK_APP_SECRET',
        'FACEBOOK_PAGE_ID',
        'FACEBOOK_PAGE_ACCESS_TOKEN',
        'FACEBOOK_WEBHOOK_VERIFY_TOKEN',
        'FACEBOOK_FORM_ID',
        'FACEBOOK_GRAPH_API_VERSION',
    ];

    /**
     * Fetch a lead from Meta by leadgen ID and store it locally.
     */
    public function fetchAndStoreLead(string $leadgenId, ?array $credentials = null, ?int $userId = null): ?MetaLead
    {
        try {
            $credentials = $this->normalizeCredentials($credentials ?: $this->globalCredentials());
            $token = (string) ($credentials['FACEBOOK_PAGE_ACCESS_TOKEN'] ?? '');
            $version = (string) ($credentials['FACEBOOK_GRAPH_API_VERSION'] ?? 'v20.0');

            Log::info('Meta lead fetch started.', [
                'leadgen_id' => $leadgenId,
                'graph_version' => $version,
                'user_id' => $userId,
            ]);

            if ($token === '') {
                Log::error('Meta page access token is missing for lead fetch', [
                    'leadgen_id' => $leadgenId,
                    'user_id' => $userId,
                ]);

                return null;
            }

            $response = Http::get("https://graph.facebook.com/{$version}/{$leadgenId}", [
                'fields' => 'id,created_time,field_data,form_id,page_id,ad_id',
                'access_token' => $token,
            ]);

            if (! $response->successful()) {
                Log::error('Failed to fetch Meta lead', [
                    'leadgen_id' => $leadgenId,
                    'status' => $response->status(),
                    'response' => $this->safeGraphErrorPayload($response->json()),
                    'user_id' => $userId,
                ]);

                return null;
            }

            $data = $response->json();

            $lead = $this->storeLeadData($data, $userId);

            Log::info('Meta lead fetch completed.', [
                'leadgen_id' => $leadgenId,
                'meta_lead_id' => $lead?->id,
                'stored' => $lead !== null,
                'user_id' => $userId,
            ]);

            return $lead;
        } catch (\Throwable $e) {
            Log::error('Exception while fetching Meta lead', [
                'leadgen_id' => $leadgenId,
                'message' => $e->getMessage(),
                'exception' => get_class($e),
                'user_id' => $userId,
            ]);

            return null;
        }
    }

    /**
     * Sync leads from a lead form and return the count of synced leads.
     */
    public function syncLeadsFromForm(string $formId = null, ?array $credentials = null, ?int $userId = null): int
    {
        $credentials = $this->normalizeCredentials($credentials ?: $this->globalCredentials());
        $formIds = $formId !== null && trim($formId) !== ''
            ? [$formId]
            : $this->formIdsFromCredentials($credentials);

        $formIds = array_values(array_unique(array_filter(array_map(
            fn ($id) => trim((string) $id),
            $formIds
        ))));

        if (count($formIds) === 0) {
            Log::warning('Meta form ID is missing for lead sync.', ['user_id' => $userId]);

            return 0;
        }

        $totalSyncedCount = 0;

        Log::info('Meta lead multi-form sync started.', [
            'form_ids' => $formIds,
            'form_count' => count($formIds),
            'page_id' => $credentials['FACEBOOK_PAGE_ID'] ?: null,
            'graph_version' => $credentials['FACEBOOK_GRAPH_API_VERSION'] ?? null,
            'user_id' => $userId,
        ]);

        foreach ($formIds as $currentFormId) {
            $totalSyncedCount += $this->syncSingleLeadForm($currentFormId, $credentials, $userId);
        }

        Log::info('Meta lead multi-form sync completed.', [
            'form_ids' => $formIds,
            'synced_count' => $totalSyncedCount,
            'user_id' => $userId,
        ]);

        return $totalSyncedCount;
    }

    protected function syncSingleLeadForm(string $formId, array $credentials, ?int $userId = null): int
    {
        $syncedCount = 0;

        Log::info('Meta lead form sync started.', [
            'form_id' => $formId ?: null,
            'page_id' => $credentials['FACEBOOK_PAGE_ID'] ?: null,
            'graph_version' => $credentials['FACEBOOK_GRAPH_API_VERSION'] ?? null,
            'user_id' => $userId,
        ]);

        try {
            $token = (string) ($credentials['FACEBOOK_PAGE_ACCESS_TOKEN'] ?? '');
            $version = (string) ($credentials['FACEBOOK_GRAPH_API_VERSION'] ?? 'v20.0');

            if ($token === '') {
                Log::warning('Meta page access token is missing for lead sync.', [
                    'form_id' => $formId,
                    'user_id' => $userId,
                ]);

                return 0;
            }

            $nextUrl = "https://graph.facebook.com/{$version}/{$formId}/leads?fields=id,created_time,field_data,form_id,page_id,ad_id&access_token={$token}";

            while ($nextUrl) {
                $response = Http::get($nextUrl);

                if (! $response->successful()) {
                    Log::error('Failed to sync Meta leads from form', [
                        'form_id' => $formId,
                        'status' => $response->status(),
                        'response' => $this->safeGraphErrorPayload($response->json()),
                        'user_id' => $userId,
                    ]);

                    break;
                }

                $payload = $response->json();
                $leads = $payload['data'] ?? [];

                Log::info('Meta lead sync page received.', [
                    'form_id' => $formId,
                    'batch_count' => count($leads),
                    'has_next_page' => isset($payload['paging']['next']),
                    'user_id' => $userId,
                ]);

                foreach ($leads as $leadData) {
                    $stored = $this->storeLeadData($leadData, $userId);
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
                'exception' => get_class($e),
                'user_id' => $userId,
            ]);
        }

        Log::info('Meta lead form sync completed.', [
            'form_id' => $formId,
            'synced_count' => $syncedCount,
            'user_id' => $userId,
        ]);

        return $syncedCount;
    }

    public function credentialsForUser(int $userId): array
    {
        $settings = Setting::getAllForUser($userId, 'meta');
        $credentials = [];

        foreach (self::CREDENTIAL_KEYS as $key) {
            $credentials[$key] = (string) ($settings[$key] ?? '');
        }

        if ($credentials['FACEBOOK_GRAPH_API_VERSION'] === '') {
            $credentials['FACEBOOK_GRAPH_API_VERSION'] = 'v25.0';
        }

        return $credentials;
    }

    public function hasRequiredCredentials(array $credentials): bool
    {
        $credentials = $this->normalizeCredentials($credentials);

        return (string) ($credentials['FACEBOOK_PAGE_ACCESS_TOKEN'] ?? '') !== ''
            && count($this->formIdsFromCredentials($credentials)) > 0;
    }

    public function formIdsFromCredentials(array $credentials): array
    {
        $rawFormIds = $credentials['FACEBOOK_FORM_ID'] ?? '';

        if (is_array($rawFormIds)) {
            $candidates = $rawFormIds;
        } else {
            $rawFormIds = trim((string) $rawFormIds);
            if ($rawFormIds !== '' && str_starts_with($rawFormIds, '[')) {
                $decoded = json_decode($rawFormIds, true);
                $candidates = is_array($decoded) ? $decoded : [$rawFormIds];
            } else {
                $candidates = preg_split('/[\r\n,]+/', $rawFormIds) ?: [];
            }
        }

        return array_values(array_unique(array_filter(array_map(
            fn ($id) => trim((string) $id),
            $candidates
        ))));
    }

    /**
     * Normalize and persist a Meta lead payload.
     */
    protected function storeLeadData(array $data, ?int $userId = null): ?MetaLead
    {
        $leadId = $data['id'] ?? null;

        if (! $leadId) {
            Log::warning('Meta lead payload skipped because lead ID is missing.', [
                'user_id' => $userId,
                'form_id' => $data['form_id'] ?? null,
                'page_id' => $data['page_id'] ?? null,
            ]);

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

        if ($userId) {
            $this->attachLeadToUser($lead, $userId);
        }

        Log::info('Meta lead stored.', [
            'leadgen_id' => $leadId,
            'meta_lead_id' => $lead->id,
            'was_recently_created' => $lead->wasRecentlyCreated,
            'form_id' => $lead->form_id,
            'page_id' => $lead->page_id,
            'user_id' => $userId,
        ]);

        if ($lead->wasRecentlyCreated) {
            $this->queueWelcomeWhatsapp($lead);
        }

        return $lead;
    }

    protected function attachLeadToUser(MetaLead $lead, int $userId): void
    {
        $lead->users()->syncWithoutDetaching([
            $userId => [
                'page_id' => $lead->page_id,
                'form_id' => $lead->form_id,
            ],
        ]);

        Log::info('Meta lead attached to user.', [
            'meta_lead_id' => $lead->id,
            'user_id' => $userId,
            'page_id' => $lead->page_id,
            'form_id' => $lead->form_id,
        ]);
    }

    protected function globalCredentials(): array
    {
        return [
            'FACEBOOK_APP_ID' => config('meta.app_id'),
            'FACEBOOK_APP_SECRET' => config('meta.app_secret'),
            'FACEBOOK_PAGE_ID' => config('meta.page_id'),
            'FACEBOOK_PAGE_ACCESS_TOKEN' => config('meta.page_access_token'),
            'FACEBOOK_WEBHOOK_VERIFY_TOKEN' => config('meta.webhook_verify_token'),
            'FACEBOOK_FORM_ID' => config('meta.form_id'),
            'FACEBOOK_GRAPH_API_VERSION' => config('meta.graph_api_version', 'v20.0'),
        ];
    }

    protected function normalizeCredentials(array $credentials): array
    {
        $normalized = [];

        foreach (self::CREDENTIAL_KEYS as $key) {
            $value = $credentials[$key] ?? '';
            if (is_array($value)) {
                $value = implode(',', array_filter(array_map(
                    fn ($item) => trim((string) $item),
                    $value
                )));
            }
            $normalized[$key] = trim((string) $value);
        }

        if ($normalized['FACEBOOK_GRAPH_API_VERSION'] === '') {
            $normalized['FACEBOOK_GRAPH_API_VERSION'] = 'v20.0';
        }

        return $normalized;
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

            Log::warning('Meta lead welcome WhatsApp skipped because phone is missing.', [
                'meta_lead_id' => $lead->id,
                'log_id' => $log->id,
            ]);

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

        Log::info('Meta lead welcome WhatsApp queued.', [
            'meta_lead_id' => $lead->id,
            'log_id' => $log->id,
        ]);
    }

    private function safeGraphErrorPayload(?array $payload): array
    {
        if (! is_array($payload)) {
            return [];
        }

        return [
            'error_message' => data_get($payload, 'error.message'),
            'error_code' => data_get($payload, 'error.code'),
            'error_type' => data_get($payload, 'error.type'),
            'error_subcode' => data_get($payload, 'error.error_subcode'),
        ];
    }

    private function normalizeFieldName(string $name): string
    {
        $name = strtolower(trim($name));
        $name = preg_replace('/[^a-z0-9]+/', '_', $name) ?? $name;

        return trim($name, '_');
    }
}
