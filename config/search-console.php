<?php

return [
    'client_id' => env('GOOGLE_SEARCH_CONSOLE_CLIENT_ID') ?: env('GOOGLE_CLIENT_ID'),
    'client_secret' => env('GOOGLE_SEARCH_CONSOLE_CLIENT_SECRET') ?: env('GOOGLE_CLIENT_SECRET'),
    'redirect_uri' => env('GOOGLE_SEARCH_CONSOLE_REDIRECT_URI') ?: (env('GOOGLE_REDIRECT_URI') ?: rtrim(env('APP_URL', 'http://localhost'), '/').'/api/auth/google/callback'),
    'cache_hours' => 3,
    'debug' => env('GOOGLE_SEARCH_CONSOLE_DEBUG', false),
];
