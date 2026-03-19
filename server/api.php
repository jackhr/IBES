<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

App\Core\ApiKernel::handle(
    $_SERVER['REQUEST_METHOD'] ?? 'GET',
    $_SERVER['REQUEST_URI'] ?? '/api'
);
