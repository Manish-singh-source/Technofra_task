<?php

return [
    'app_id' => env('FACEBOOK_APP_ID'),
    'app_secret' => env('FACEBOOK_APP_SECRET'),
    'page_id' => env('FACEBOOK_PAGE_ID'),
    'page_access_token' => env('FACEBOOK_PAGE_ACCESS_TOKEN'),
    'webhook_verify_token' => env('FACEBOOK_WEBHOOK_VERIFY_TOKEN'),
    'form_id' => env('FACEBOOK_FORM_ID'),
    'graph_api_version' => env('FACEBOOK_GRAPH_API_VERSION', 'v20.0'),
];
