<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

if (Config::bool('APP_UNDER_CONSTRUCTION', false)) {
    App\Core\JsonResponse::send([
        'success' => false,
        'message' => 'Service temporarily unavailable.',
        'status' => 503,
        'data' => [],
    ], 503);

    return;
}

App\Core\ApiKernel::handle(
    $_SERVER['REQUEST_METHOD'] ?? 'GET',
    $_SERVER['REQUEST_URI'] ?? '/api'
);
