<?php

declare(strict_types=1);

namespace App\Support;

final class EndpointGuard
{
    private const HONEYPOT_FIELD = 'h826r2whj4fi_cjz8jxs2zuwahhhk6';

    /** @param array<string, mixed> $payload
     *  @return array<string, mixed>|null
     */
    public static function protect(array $payload, string $scope, bool $requireCaptcha = true): ?array
    {
        if (self::honeypotTriggered($payload)) {
            return self::errorResponse('error', 400);
        }

        $rateLimit = self::checkRateLimit($scope);

        if (!$rateLimit['allowed']) {
            return [
                'success' => false,
                'message' => 'Too many requests. Please try again later.',
                'status' => 429,
                'data' => [
                    'retryAfterSeconds' => $rateLimit['retryAfter'],
                ],
            ];
        }

        if ($requireCaptcha && CaptchaVerifier::provider() !== 'none') {
            $token = self::captchaToken($payload);
            $captchaResult = CaptchaVerifier::verify($token);

            if (!$captchaResult['success']) {
                return [
                    'success' => false,
                    'message' => $captchaResult['message'],
                    'status' => 403,
                    'data' => [],
                ];
            }
        }

        return null;
    }

    /** @param array<string, mixed> $payload */
    public static function captchaToken(array $payload): string
    {
        $rawToken = $payload['captchaToken']
            ?? $payload['captcha_token']
            ?? $payload['h-captcha-response']
            ?? $payload['g-recaptcha-response']
            ?? '';

        return trim((string) $rawToken);
    }

    /** @param array<string, mixed> $payload */
    private static function honeypotTriggered(array $payload): bool
    {
        $value = $payload[self::HONEYPOT_FIELD] ?? '';

        return trim((string) $value) !== '';
    }

    /** @return array{allowed: bool, remaining: int, retryAfter: int} */
    private static function checkRateLimit(string $scope): array
    {
        $defaultMax = self::positiveInt((string) config('client.rate_limits.default.max', 15), 15);
        $defaultWindow = self::positiveInt((string) config('client.rate_limits.default.window', 900), 900);
        $max = self::positiveInt((string) config("client.rate_limits.{$scope}.max", $defaultMax), $defaultMax);
        $window = self::positiveInt((string) config("client.rate_limits.{$scope}.window", $defaultWindow), $defaultWindow);

        return RateLimiter::consume($scope, $max, $window);
    }

    /** @return array<string, mixed> */
    private static function errorResponse(string $message, int $status): array
    {
        return [
            'success' => false,
            'message' => $message,
            'status' => $status,
            'data' => [],
        ];
    }

    private static function positiveInt(string $value, int $fallback): int
    {
        $parsed = (int) $value;

        return $parsed > 0 ? $parsed : $fallback;
    }
}
