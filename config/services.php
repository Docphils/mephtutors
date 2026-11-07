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

    'paystack' => [
        'public' => env('PAYSTACK_PUBLIC_KEY'),
        'secret' => env('PAYSTACK_SECRET_KEY'),
    ],

    'groq' => [
        'api_key' => env('GROQ_API_KEY'),
        'model' => env('GROQ_EBOOK_MODEL', 'qwen/qwen3.8-27b'),
        'blog_model' => env('GROQ_BLOG_MODEL', 'qwen/qwen3.8-27b'),
        'fallback_models' => array_values(array_filter(array_map(
            'trim',
            explode(',', (string) env('GROQ_EBOOK_FALLBACK_MODELS', 'qwen/qwen3.8-27b,openai/gpt-oss-20b,openai/gpt-oss-120b'))
        ))),
        'vision_models' => array_values(array_filter(array_map(
            'trim',
            explode(',', (string) env('GROQ_EBOOK_VISION_MODELS', 'qwen/qwen3.8-27b'))
        ))),
    ],

    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
        'blog_model' => env('GEMINI_BLOG_MODEL', 'gemini-2.5-flash'),
    ],

    'tavily' => [
        'api_key' => env('TAVILY_API_KEY'),
    ],

    'facebook' => [
        'graph_api_version' => env('META_GRAPH_API_VERSION', 'v27.0'),
        'page_id' => env('META_PAGE_ID'),
        'page_access_token' => env('META_PAGE_ACCESS_TOKEN'),
    ],

    'solar_sapient' => [
        'webhook_url' => env('SOLAR_SAPIENT_WEBHOOK_URL'),
    ],

    'jitsi' => [
        'domain' => env('JITSI_DOMAIN', 'meet.jit.si'),
        'app_name' => env('JITSI_APP_NAME', env('APP_NAME', 'MephEd')),
        'recording_enabled' => (bool) env('JITSI_RECORDING_ENABLED', true),
        'jaas_app_id' => env('JITSI_JAAS_APP_ID'),
        'jaas_kid' => env('JITSI_JAAS_KID'),
        'jaas_private_key' => env('JITSI_JAAS_PRIVATE_KEY'),
        'jaas_private_key_path' => env('JITSI_JAAS_PRIVATE_KEY_PATH'),
        'jwt_app_id' => env('JITSI_JWT_APP_ID'),
        'jwt_app_secret' => env('JITSI_JWT_APP_SECRET'),
        'jwt_iss' => env('JITSI_JWT_ISS'),
        'jwt_sub' => env('JITSI_JWT_SUB'),
        'jwt_aud' => env('JITSI_JWT_AUD', 'jitsi'),
        'jaas_webhook_token' => env('JITSI_JAAS_WEBHOOK_TOKEN'),
    ],

];
