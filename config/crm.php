<?php

return [
    // Global SMTP attempt budget shared by every campaign, per rolling minute.
    'campaign_batch_size' => (int) env('CRM_CAMPAIGN_BATCH_SIZE', 10),
    'campaign_interval_ms' => (int) env('CRM_CAMPAIGN_INTERVAL_MS', 1000),
    'campaign_stale_minutes' => (int) env('CRM_CAMPAIGN_STALE_MINUTES', 15),
    'campaign_max_recipients' => 5000,
];
