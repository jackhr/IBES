<?php

declare(strict_types=1);

namespace App\Support;

final class ClientIpResolver
{
    public static function resolve(): string
    {
        $request = app()->bound('request') ? request() : null;

        $candidates = [
            $request?->server('HTTP_CF_CONNECTING_IP'),
            $request?->server('HTTP_X_FORWARDED_FOR'),
            $request?->server('HTTP_X_REAL_IP'),
            $request?->ip(),
            $_SERVER['HTTP_CF_CONNECTING_IP'] ?? null,
            $_SERVER['HTTP_X_FORWARDED_FOR'] ?? null,
            $_SERVER['HTTP_X_REAL_IP'] ?? null,
            $_SERVER['REMOTE_ADDR'] ?? null,
        ];

        foreach ($candidates as $candidate) {
            if (!is_string($candidate) || trim($candidate) === '') {
                continue;
            }

            $parts = explode(',', $candidate);

            foreach ($parts as $part) {
                $ip = trim($part);

                if ($ip !== '' && filter_var($ip, FILTER_VALIDATE_IP) !== false) {
                    return $ip;
                }
            }
        }

        return '0.0.0.0';
    }
}
