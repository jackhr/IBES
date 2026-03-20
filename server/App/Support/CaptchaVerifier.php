<?php

declare(strict_types=1);

namespace App\Support;

final class CaptchaVerifier
{
    /** @return array{success: bool, message: string} */
    public static function verify(string $token): array
    {
        $provider = self::provider();

        if ($provider === 'none') {
            return [
                'success' => true,
                'message' => 'Captcha disabled.',
            ];
        }

        if (trim($token) === '') {
            return [
                'success' => false,
                'message' => 'Captcha token missing.',
            ];
        }

        $secret = self::secretForProvider($provider);

        if ($secret === null || trim($secret) === '') {
            return [
                'success' => false,
                'message' => 'Captcha is not configured.',
            ];
        }

        $endpoint = self::verifyEndpoint($provider);
        $remoteIp = ClientIpResolver::resolve();

        $result = self::sendVerificationRequest($endpoint, [
            'secret' => $secret,
            'response' => $token,
            'remoteip' => $remoteIp,
        ]);

        if ($result === null) {
            return [
                'success' => false,
                'message' => 'Captcha verification failed.',
            ];
        }

        $success = (bool) ($result['success'] ?? false);

        if ($success) {
            return [
                'success' => true,
                'message' => 'ok',
            ];
        }

        $errorCodes = $result['error-codes'] ?? $result['error_codes'] ?? [];
        $errorSuffix = '';

        if (is_array($errorCodes) && $errorCodes !== []) {
            $normalized = array_map(static fn(mixed $code): string => (string) $code, $errorCodes);
            $errorSuffix = ': ' . implode(', ', $normalized);
        }

        return [
            'success' => false,
            'message' => 'Captcha verification failed' . $errorSuffix . '.',
        ];
    }

    public static function provider(): string
    {
        $provider = strtolower(trim((string) \Config::get('CAPTCHA_PROVIDER', 'none')));
        $enabled = \Config::bool('CAPTCHA_ENABLED', $provider !== 'none');

        if (!$enabled) {
            return 'none';
        }

        return match ($provider) {
            'hcaptcha' => 'hcaptcha',
            'recaptcha' => 'recaptcha',
            default => 'none',
        };
    }

    private static function secretForProvider(string $provider): ?string
    {
        return match ($provider) {
            'hcaptcha' => \Config::get('HCAPTCHA_SECRET_KEY'),
            'recaptcha' => \Config::get('RECAPTCHA_SECRET_KEY'),
            default => null,
        };
    }

    private static function verifyEndpoint(string $provider): string
    {
        return match ($provider) {
            'hcaptcha' => (string) \Config::get('HCAPTCHA_VERIFY_URL', 'https://api.hcaptcha.com/siteverify'),
            'recaptcha' => (string) \Config::get('RECAPTCHA_VERIFY_URL', 'https://www.google.com/recaptcha/api/siteverify'),
            default => '',
        };
    }

    /** @param array<string, string> $payload
     *  @return array<string, mixed>|null
     */
    private static function sendVerificationRequest(string $url, array $payload): ?array
    {
        if (trim($url) === '') {
            return null;
        }

        $encodedPayload = http_build_query($payload);

        if (function_exists('curl_init')) {
            $curl = curl_init($url);

            if ($curl === false) {
                return null;
            }

            curl_setopt_array($curl, [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $encodedPayload,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/x-www-form-urlencoded',
                ],
            ]);

            $responseBody = curl_exec($curl);
            curl_close($curl);
        } else {
            $context = stream_context_create([
                'http' => [
                    'method' => 'POST',
                    'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
                    'content' => $encodedPayload,
                    'timeout' => 10,
                ],
            ]);

            $responseBody = @file_get_contents($url, false, $context);
        }

        if (!is_string($responseBody) || trim($responseBody) === '') {
            return null;
        }

        $decoded = json_decode($responseBody, true);

        return is_array($decoded) ? $decoded : null;
    }
}
