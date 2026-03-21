<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

final class Request
{
    /** @return array<string, mixed> */
    public static function json(): array
    {
        $raw = file_get_contents('php://input');

        if (!is_string($raw) || trim($raw) === '') {
            return [];
        }

        $decoded = json_decode($raw, true);

        if (!is_array($decoded)) {
            throw new RuntimeException('Invalid JSON input');
        }

        return $decoded;
    }
}
