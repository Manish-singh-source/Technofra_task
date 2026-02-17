<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'k3_whatsapp' => [
        'base_url' => env('K3_WHATSAPP_BASE_URL', 'https://partnersv1.pinbot.ai'),
        'business_id' => env('K3_WHATSAPP_BUSINESS_ID'),
        'api_key' => env('K3_WHATSAPP_API_KEY'),
        'default_country_code' => env('K3_WHATSAPP_DEFAULT_COUNTRY_CODE', '91'),
        'default_language' => env('K3_WHATSAPP_DEFAULT_LANGUAGE', 'en'),
        'reminder_template' => env('K3_WHATSAPP_REMINDER_TEMPLATE', 'calendar_appointment_reminder'),
        'event_time_template' => env('K3_WHATSAPP_EVENT_TIME_TEMPLATE', 'calendar_appointment_reminder'),
    ],

];
