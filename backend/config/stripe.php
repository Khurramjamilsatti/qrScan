<?php

return [
    'key' => env('STRIPE_KEY'),
    'secret' => env('STRIPE_SECRET'),
    'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),

    // Bypass HTTP_PROXY / HTTPS_PROXY for Stripe API calls (fixes CONNECT tunnel 403).
    'disable_proxy' => env('STRIPE_DISABLE_PROXY', true),

    // Some corporate proxies break HTTP/2 CONNECT to api.stripe.com.
    'disable_http2' => env('STRIPE_DISABLE_HTTP2', true),

    'plan_rank' => [
        'free' => 0,
        'starter' => 1,
        'pro' => 2,
        'business' => 3,
    ],
];
