<?php

return [
    // Host Nginx reaches the loopback-published web container through the
    // gateway of the 172.20.0.0/16 network defined in compose.yaml.
    // Override with comma-separated proxy IPs/CIDRs if that topology changes.
    'proxies' => array_values(array_filter(array_map(
        'trim',
        explode(',', env('TRUSTED_PROXIES', '127.0.0.1,::1,172.20.0.1'))
    ))),
];
