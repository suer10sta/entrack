<?php

return [
    'property_id' => env('GOOGLE_ANALYTICS_PROPERTY_ID') ?: env('GA4_PROPERTY_ID'),
    'cache_hours' => 3,
    'measurement_id' => env('GOOGLE_ANALYTICS_MEASUREMENT_ID', 'G-MDJNVZY2WF'),
    'api_secret' => env('GOOGLE_ANALYTICS_API_SECRET'),
];
