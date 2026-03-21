<?php

declare(strict_types=1);

namespace App\Support;

final class RateLimiter
{
    /** @return array{allowed: bool, remaining: int, retryAfter: int} */
    public static function consume(string $scope, int $maxAttempts, int $windowSeconds): array
    {
        $maxAttempts = max($maxAttempts, 1);
        $windowSeconds = max($windowSeconds, 1);

        $clientIp = ClientIpResolver::resolve();
        $key = $scope . '|' . $clientIp;
        $filePath = self::filePath($key);

        self::ensureDirectory(dirname($filePath));

        $handle = fopen($filePath, 'c+');

        if (!is_resource($handle)) {
            return [
                'allowed' => true,
                'remaining' => $maxAttempts - 1,
                'retryAfter' => 0,
            ];
        }

        try {
            if (!flock($handle, LOCK_EX)) {
                return [
                    'allowed' => true,
                    'remaining' => $maxAttempts - 1,
                    'retryAfter' => 0,
                ];
            }

            rewind($handle);
            $contents = stream_get_contents($handle);
            $payload = is_string($contents) && trim($contents) !== '' ? json_decode($contents, true) : null;

            $attempts = [];

            if (is_array($payload['attempts'] ?? null)) {
                foreach ($payload['attempts'] as $timestamp) {
                    if (is_int($timestamp)) {
                        $attempts[] = $timestamp;
                    } elseif (is_numeric($timestamp)) {
                        $attempts[] = (int) $timestamp;
                    }
                }
            }

            $now = time();
            $windowStart = $now - $windowSeconds;

            $attempts = array_values(
                array_filter(
                    $attempts,
                    static fn(int $timestamp): bool => $timestamp >= $windowStart
                )
            );

            $count = count($attempts);

            if ($count >= $maxAttempts) {
                $retryAfter = max(1, ($attempts[0] + $windowSeconds) - $now);

                self::writeAttempts($handle, $attempts);

                flock($handle, LOCK_UN);

                return [
                    'allowed' => false,
                    'remaining' => 0,
                    'retryAfter' => $retryAfter,
                ];
            }

            $attempts[] = $now;
            $remaining = max(0, $maxAttempts - count($attempts));

            self::writeAttempts($handle, $attempts);
            flock($handle, LOCK_UN);

            return [
                'allowed' => true,
                'remaining' => $remaining,
                'retryAfter' => 0,
            ];
        } finally {
            fclose($handle);
        }
    }

    private static function filePath(string $key): string
    {
        $hashed = sha1($key);
        $directory = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'ibes-rate-limits';

        return $directory . DIRECTORY_SEPARATOR . $hashed . '.json';
    }

    private static function ensureDirectory(string $directory): void
    {
        if (is_dir($directory)) {
            return;
        }

        @mkdir($directory, 0775, true);
    }

    /** @param array<int, int> $attempts */
    private static function writeAttempts($handle, array $attempts): void
    {
        rewind($handle);
        ftruncate($handle, 0);
        $encoded = json_encode(['attempts' => $attempts]);

        if (is_string($encoded)) {
            fwrite($handle, $encoded);
        }

        fflush($handle);
    }
}
