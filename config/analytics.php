<?php

return [
    'property_id' => env('GOOGLE_ANALYTICS_PROPERTY_ID') ?: env('GA4_PROPERTY_ID'),
    'cache_hours' => 3,
];
