<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

$maintenanceEnabled = Config::bool('APP_UNDER_CONSTRUCTION', false);

if ($maintenanceEnabled) {
    $maintenanceFile = dirname(__DIR__) . '/under-construction/index.html';

    if (!is_file($maintenanceFile)) {
        if (!headers_sent()) {
            http_response_code(503);
            header('Content-Type: text/plain; charset=UTF-8');
        }

        echo 'Service temporarily unavailable.';

        return;
    }

    if (!headers_sent()) {
        http_response_code(503);
        header('Content-Type: text/html; charset=UTF-8');
        header('Retry-After: 3600');
    }

    readfile($maintenanceFile);

    return;
}

$appIndexFile = dirname(__DIR__) . '/dist/index.html';

if (!is_file($appIndexFile)) {
    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: text/plain; charset=UTF-8');
    }

    echo 'Frontend build not found. Run npm run build.';

    return;
}

if (!headers_sent()) {
    header('Content-Type: text/html; charset=UTF-8');
}

readfile($appIndexFile);
