<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class MetaBusinessOAuthService
{
    private const SCOPES = [
        'pages_show_list',
        'pages_read_engagement',
        'pages_manage_metadata',
        'leads_retrieval',
    ];

    public function hasGlobalAppCredentials(): bool
    {
        return $this->appId() !== '' && $this->appSecret() !== '';
    }

    public function authorizationUrl(string $redirectUri, string $state): string
    {
        return 'https://www.facebook.com/'.$this->graphVersion().'/dialog/oauth?'.http_build_query([
            'client_id' => $this->appId(),
            'redirect_uri' => $redirectUri,
            'state' => $state,
            'response_type' => 'code',
            'scope' => implode(',', self::SCOPES),
        ]);
    }

    public function exchangeCodeForShortLivedToken(string $code, string $redirectUri): string
    {
        Log::info('Meta OAuth short-lived token exchange started.', [
            'graph_version' => $this->graphVersion(),
            'redirect_uri' => $redirectUri,
        ]);

        $response = Http::asJson()->get($this->graphUrl('/oauth/access_token'), [
            'client_id' => $this->appId(),
            'redirect_uri' => $redirectUri,
            'client_secret' => $this->appSecret(),
            'code' => $code,
        ]);

        $payload = $this->jsonOrFail($response->json(), 'Unable to exchange Facebook OAuth code.');

        $token = (string) ($payload['access_token'] ?? '');
        if ($token === '') {
            throw new RuntimeException('Facebook did not return a short-lived user token.');
        }

        Log::info('Meta OAuth short-lived token exchange completed.', [
            'graph_version' => $this->graphVersion(),
            'status' => $response->status(),
        ]);

        return $token;
    }

    public function exchangeForLongLivedUserToken(string $shortLivedToken): string
    {
        Log::info('Meta OAuth long-lived token exchange started.', [
            'graph_version' => $this->graphVersion(),
        ]);

        $response = Http::asJson()->get($this->graphUrl('/oauth/access_token'), [
            'grant_type' => 'fb_exchange_token',
            'client_id' => $this->appId(),
            'client_secret' => $this->appSecret(),
            'fb_exchange_token' => $shortLivedToken,
        ]);

        $payload = $this->jsonOrFail($response->json(), 'Unable to create long-lived Facebook user token.');

        $token = (string) ($payload['access_token'] ?? '');
        if ($token === '') {
            throw new RuntimeException('Facebook did not return a long-lived user token.');
        }

        Log::info('Meta OAuth long-lived token exchange completed.', [
            'graph_version' => $this->graphVersion(),
            'status' => $response->status(),
        ]);

        return $token;
    }

    public function discoverPagesAndForms(string $longLivedUserToken): array
    {
        Log::info('Meta page discovery started.', [
            'graph_version' => $this->graphVersion(),
            'fields' => 'id,name,access_token,tasks',
        ]);

        $response = Http::asJson()->get($this->graphUrl('/me/accounts'), [
            'fields' => 'id,name,access_token,tasks',
            'access_token' => $longLivedUserToken,
        ]);

        $payload = $this->jsonOrFail($response->json(), 'Unable to discover Facebook pages.');
        $pages = [];

        foreach (($payload['data'] ?? []) as $page) {
            $pageAccessToken = (string) ($page['access_token'] ?? '');
            $pageId = (string) ($page['id'] ?? '');

            if ($pageId === '' || $pageAccessToken === '') {
                continue;
            }

            $forms = $this->discoverLeadForms($pageId, $pageAccessToken);

            $pages[] = [
                'id' => $pageId,
                'name' => (string) ($page['name'] ?? 'Facebook Page'),
                'access_token' => $pageAccessToken,
                'forms' => $forms,
            ];
        }

        Log::info('Meta page discovery completed.', [
            'graph_version' => $this->graphVersion(),
            'status' => $response->status(),
            'page_count' => count($pages),
            'form_count' => collect($pages)->sum(fn ($page) => count($page['forms'] ?? [])),
        ]);

        return $pages;
    }

    public function graphVersion(): string
    {
        $version = trim((string) config('meta.graph_api_version', 'v25.0'));

        return $version !== '' ? $version : 'v25.0';
    }

    private function discoverLeadForms(string $pageId, string $pageAccessToken): array
    {
        Log::info('Meta lead form discovery started.', [
            'graph_version' => $this->graphVersion(),
            'page_id' => $pageId,
        ]);

        $response = Http::asJson()->get($this->graphUrl('/'.$pageId.'/leadgen_forms'), [
            'fields' => 'id,name,status,leads_count',
            'access_token' => $pageAccessToken,
        ]);

        $payload = $this->jsonOrFail($response->json(), 'Unable to discover Facebook lead forms for page '.$pageId.'.');

        $forms = collect($payload['data'] ?? [])
            ->map(fn ($form) => [
                'id' => (string) ($form['id'] ?? ''),
                'name' => (string) ($form['name'] ?? 'Lead Form'),
                'status' => (string) ($form['status'] ?? ''),
                'leads_count' => $form['leads_count'] ?? null,
            ])
            ->filter(fn ($form) => $form['id'] !== '')
            ->values()
            ->all();

        Log::info('Meta lead form discovery completed.', [
            'graph_version' => $this->graphVersion(),
            'page_id' => $pageId,
            'status' => $response->status(),
            'form_count' => count($forms),
        ]);

        return $forms;
    }

    private function graphUrl(string $path): string
    {
        return 'https://graph.facebook.com/'.$this->graphVersion().$path;
    }

    private function appId(): string
    {
        return trim((string) config('meta.app_id'));
    }

    private function appSecret(): string
    {
        return trim((string) config('meta.app_secret'));
    }

    private function jsonOrFail(?array $payload, string $message): array
    {
        if (! is_array($payload) || isset($payload['error'])) {
            $detail = is_array($payload) ? (string) data_get($payload, 'error.message', '') : '';
            Log::error('Meta Graph API response error.', [
                'message' => trim($message),
                'error_message' => $detail,
                'error_code' => is_array($payload) ? data_get($payload, 'error.code') : null,
                'error_type' => is_array($payload) ? data_get($payload, 'error.type') : null,
            ]);

            throw new RuntimeException(trim($message.' '.$detail));
        }
        return $payload;
    }
}


