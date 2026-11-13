<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Demo / Sandbox Account Configuration
    |--------------------------------------------------------------------------
    |
    | Sandbox sessions store all tester changes strictly in the visitor's
    | HTTP session (never persisted to the database), auto-expire after
    | sandbox_ttl_minutes (default 60 minutes), and block external API calls
    | and outbound emails so those quotas stay reserved for real users.
    |
    */
    'email' => env('DEMO_EMAIL', 'demo@subtracker.test'),

    // Strong password used only if allow_password_login is enabled; otherwise rotated randomly on seed
    'password' => env('DEMO_PASSWORD', ''),

    // Keep false in production so bots cannot brute-force a shared demo login; visitors enter via Turnstile Sandbox
    'allow_password_login' => (bool) env('DEMO_ALLOW_PASSWORD_LOGIN', false),

    // Sandbox session lifetime in minutes (1 hour)
    'sandbox_ttl_minutes' => (int) env('DEMO_SANDBOX_TTL_MINUTES', 60),
];
