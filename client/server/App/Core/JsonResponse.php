<?php

declare(strict_types=1);

namespace App\Core;

final class JsonResponse
{
    /** @param array<string, mixed> $payload */
    public static function send(array $payload, int $status = 200): void
    {
        if (!headers_sent()) {
            http_response_code($status);
            header('Content-Type: application/json; charset=UTF-8');
        }

        echo json_encode($payload);
    }
}
