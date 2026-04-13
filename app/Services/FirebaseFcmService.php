<?php

namespace App\Services;

use Google\Auth\Credentials\ServiceAccountCredentials;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use RuntimeException;

class FirebaseFcmService
{
    protected string $projectId;

    protected string $credentialsPath;

    protected Client $http;

    public function __construct()
    {
        $this->projectId = (string) config('services.firebase.project_id');
        $this->credentialsPath = (string) config('services.firebase.credentials');

        if ($this->projectId === '') {
            throw new RuntimeException('FIREBASE_PROJECT_ID is not configured.');
        }

        if ($this->credentialsPath === '') {
            throw new RuntimeException('FIREBASE_CREDENTIALS is not configured.');
        }

        if (! file_exists($this->credentialsPath)) {
            throw new RuntimeException('Firebase credentials file not found at: '.$this->credentialsPath);
        }

        $this->http = new Client([
            'base_uri' => 'https://fcm.googleapis.com/v1/',
            'timeout' => 5.0,
        ]);
    }

    protected function getAccessToken(): string
    {
        $scopes = ['https://www.googleapis.com/auth/firebase.messaging'];

        $creds = new ServiceAccountCredentials(
            $scopes,
            $this->credentialsPath
        );

        $token = $creds->fetchAuthToken();
        $accessToken = $token['access_token'] ?? '';

        if ($accessToken === '') {
            throw new RuntimeException('Unable to fetch Firebase access token.');
        }

        return $accessToken;
    }

    public function sendToToken(string $token, string $title, string $body, array $data = []): array
    {
        $accessToken = $this->getAccessToken();

        $url = sprintf('projects/%s/messages:send', $this->projectId);

        $payload = [
            'message' => [
                'token' => $token,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ],
                // 'data' => $data,
            ],
        ];

        try {
            $response = $this->http->post($url, [
                'headers' => [
                    'Authorization' => 'Bearer '.$accessToken,
                    'Content-Type' => 'application/json; charset=UTF-8',
                ],
                'json' => $payload,
            ]);
        } catch (GuzzleException $e) {
            $message = $e->getMessage();

            if (method_exists($e, 'getResponse') && $e->getResponse()) {
                $firebaseResponse = json_decode((string) $e->getResponse()->getBody(), true);
                $firebaseMessage = $firebaseResponse['error']['message'] ?? null;

                if ($firebaseMessage) {
                    $message = 'Firebase FCM error: '.$firebaseMessage;
                }
            }

            throw new RuntimeException($message, (int) $e->getCode(), $e);
        }

        return json_decode((string) $response->getBody(), true);
    }
}
