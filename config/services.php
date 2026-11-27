<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    // Free ExchangeRate-API (https://www.exchangerate-api.com — free tier: 1500 req/month)
    'exchangerate' => [
        'key' => env('EXCHANGERATE_API_KEY', ''),
        'base_url' => 'https://v6.exchangerate-api.com/v6',
    ],

    // Optional Cloudflare Turnstile CAPTCHA (enforced only when both keys are non-empty)
    'turnstile' => [
        'site_key' => env('TURNSTILE_SITE_KEY', ''),
        'secret_key' => env('TURNSTILE_SECRET_KEY', ''),
        'hostnames' => array_values(array_filter(array_map('trim', explode(',', (string) env('TURNSTILE_HOSTNAMES', ''))))),
    ],

    // Groq Cloud AI (OpenAI-compatible fast inference for Chat + Vision Receipt Scanner)
    'groq' => [
        'key' => env('GROQ_API_KEY', ''),
        'chat_model' => env('GROQ_CHAT_MODEL', 'openai/gpt-oss-20b'),
        'chat_fallback_model' => env('GROQ_CHAT_FALLBACK_MODEL', 'openai/gpt-oss-120b'),
        'vision_model' => env('GROQ_VISION_MODEL', 'qwen/qwen3.8-27b'),
        'vision_fallback_model' => env('GROQ_VISION_FALLBACK_MODEL', ''),
        'base_url' => rtrim((string) env('GROQ_BASE_URL', 'https://api.groq.com/openai/v1'), '/'),
        'timeout' => (int) env('GROQ_TIMEOUT', 25),
    ],

];
