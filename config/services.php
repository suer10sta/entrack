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

    'billit' => [
        'api_key' => env('BILLIT_API_KEY'),
        'base_url' => env('BILLIT_BASE_URL', 'https://api.sandbox.billit.be'),
        'channel' => env('BILLIT_CHANNEL', 'Peppol'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Stripe subscription prices (one per plan, matching the Stripe mode of
    | the configured keys: test prices with test keys, live prices with
    | live keys). No hardcoded fallbacks on purpose: a missing price must
    | fail fast with a clear message instead of a Stripe "No such price" 500.
    |--------------------------------------------------------------------------
    */
    'stripe' => [
        'public_key' => env('STRIPE_KEY'),
        'secret_key' => env('STRIPE_SECRET'),
        'prices' => [
            'solo' => env('STRIPE_PRICE_SOLO'),
            'premium' => env('STRIPE_PRICE_PREMIUM'),
            'pro' => env('STRIPE_PRICE_PRO'),
        ],
    ],

    'turnstile' => [
        'site_key' => env('TURNSTILE_SITE_KEY'),
        'secret_key' => env('TURNSTILE_SECRET_KEY'),
    ],

    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
    ],

];
