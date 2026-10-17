<?php

return [
    /*
    |--------------------------------------------------------------------------
    | HTTP Security Headers Configuration
    |--------------------------------------------------------------------------
    |
    | Configures baseline HTTP security headers across responses to defend
    | against MIME sniffing, clickjacking, referrer leakage, and unauthorized
    | browser features.
    |
    */
    'headers' => [
        'x_content_type_options' => env('SECURITY_X_CONTENT_TYPE_OPTIONS', 'nosniff'),
        'x_frame_options' => env('SECURITY_X_FRAME_OPTIONS', 'SAMEORIGIN'),
        'referrer_policy' => env('SECURITY_REFERRER_POLICY', 'strict-origin-when-cross-origin'),
        'x_xss_protection' => env('SECURITY_X_XSS_PROTECTION', '0'),
        'permissions_policy' => env('SECURITY_PERMISSIONS_POLICY', 'camera=(), microphone=(), geolocation=(), payment=()'),
        'hsts' => [
            'enabled' => env('HSTS_ENABLED', null),
            'max_age' => (int) env('HSTS_MAX_AGE', 31536000),
            'include_subdomains' => (bool) env('HSTS_INCLUDE_SUBDOMAINS', true),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Content Security Policy (CSP)
    |--------------------------------------------------------------------------
    |
    | Directives define content sources allowed by modern browsers.
    | Configured to accommodate Vite, Bootstrap, ApexCharts, and CDN assets.
    |
    */
    'csp' => [
        'enabled' => (bool) env('CSP_ENABLED', true),
        'report_only' => (bool) env('CSP_REPORT_ONLY', false),
        'directives' => [
            'default-src' => ["'self'"],
            'script-src' => [
                "'self'",
                "'unsafe-inline'",
                "'unsafe-eval'",
                'https://cdn.jsdelivr.net',
            ],
            'style-src' => [
                "'self'",
                "'unsafe-inline'",
                'https://cdn.jsdelivr.net',
                'https://fonts.googleapis.com',
            ],
            'font-src' => [
                "'self'",
                'https://fonts.gstatic.com',
                'data:',
            ],
            'img-src' => [
                "'self'",
                'data:',
                'blob:',
                'https:',
            ],
            'connect-src' => [
                "'self'",
                'ws:',
                'wss:',
            ],
            'media-src' => ["'self'"],
            'object-src' => ["'none'"],
            'frame-ancestors' => ["'self'"],
            'base-uri' => ["'self'"],
            'form-action' => ["'self'"],
        ],
    ],
];
