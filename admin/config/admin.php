<?php

return [
    'token_ttl_hours' => (int) env('ADMIN_TOKEN_TTL_HOURS', 12),
    'log_auth_failures' => filter_var(
        (string) env('ADMIN_LOG_AUTH_FAILURES', 'true'),
        FILTER_VALIDATE_BOOL,
        FILTER_NULL_ON_FAILURE
    ) ?? true,
];
