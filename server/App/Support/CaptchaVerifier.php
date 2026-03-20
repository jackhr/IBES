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
        $payload = [
            'secret' => $secret,
            'response' => $token,
            'remoteip' => $remoteIp,
        ];

        if ($provider === 'hcaptcha') {
            $siteKey = self::hcaptchaSiteKey();

            if ($siteKey !== null && trim($siteKey) !== '') {
                $payload['sitekey'] = $siteKey;
            }
        }

        $result = self::sendVerificationRequest($endpoint, $payload);

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
        $transport = self::httpTransport();

        if ($transport === 'stream') {
            $streamResult = self::sendVerificationRequestWithStream($url, $encodedPayload);

            if ($streamResult !== null) {
                return $streamResult;
            }

            return self::sendVerificationRequestWithSocket($url, $encodedPayload);
        }

        if ($transport === 'curl') {
            $curlResult = self::sendVerificationRequestWithCurl($url, $encodedPayload);

            if ($curlResult !== null) {
                return $curlResult;
            }

            return self::sendVerificationRequestWithSocket($url, $encodedPayload);
        }

        if ($transport === 'socket') {
            return self::sendVerificationRequestWithSocket($url, $encodedPayload);
        }

        $curlResult = self::sendVerificationRequestWithCurl($url, $encodedPayload);

        if ($curlResult !== null) {
            return $curlResult;
        }

        $streamResult = self::sendVerificationRequestWithStream($url, $encodedPayload);

        if ($streamResult !== null) {
            return $streamResult;
        }

        return self::sendVerificationRequestWithSocket($url, $encodedPayload);
    }

    private static function hcaptchaSiteKey(): ?string
    {
        $explicit = \Config::get('HCAPTCHA_SITE_KEY');

        if (is_string($explicit) && trim($explicit) !== '') {
            return $explicit;
        }

        $viteSiteKey = \Config::get('VITE_HCAPTCHA_SITE_KEY');

        return is_string($viteSiteKey) && trim($viteSiteKey) !== '' ? $viteSiteKey : null;
    }

    /** @return array<string, mixed>|null */
    private static function sendVerificationRequestWithStream(string $url, string $encodedPayload): ?array
    {
        if (!self::isUrlFopenEnabled()) {
            return null;
        }

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
                'content' => $encodedPayload,
                'timeout' => 10,
                'ignore_errors' => true,
            ],
        ]);

        $responseBody = @file_get_contents($url, false, $context);

        if (!is_string($responseBody) || trim($responseBody) === '') {
            return null;
        }

        $decoded = json_decode($responseBody, true);

        return is_array($decoded) ? $decoded : null;
    }

    /** @return array<string, mixed>|null */
    private static function sendVerificationRequestWithCurl(string $url, string $encodedPayload): ?array
    {
        if (!function_exists('curl_init')) {
            return null;
        }

        $curl = curl_init($url);

        if ($curl === false) {
            return null;
        }

        curl_setopt_array($curl, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $encodedPayload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/x-www-form-urlencoded',
            ],
        ]);

        $responseBody = curl_exec($curl);
        curl_close($curl);

        if (!is_string($responseBody) || trim($responseBody) === '') {
            return null;
        }

        $decoded = json_decode($responseBody, true);

        return is_array($decoded) ? $decoded : null;
    }

    private static function httpTransport(): string
    {
        $transport = strtolower(trim((string) \Config::get('CAPTCHA_HTTP_TRANSPORT', 'auto')));

        return match ($transport) {
            'curl' => 'curl',
            'stream', 'fopen' => 'stream',
            'socket', 'fsockopen' => 'socket',
            default => 'auto',
        };
    }

    private static function isUrlFopenEnabled(): bool
    {
        $value = ini_get('allow_url_fopen');

        if ($value === false) {
            return false;
        }

        $normalized = strtolower(trim((string) $value));

        return in_array($normalized, ['1', 'on', 'true', 'yes'], true);
    }

    /** @return array<string, mixed>|null */
    private static function sendVerificationRequestWithSocket(string $url, string $encodedPayload): ?array
    {
        if (!function_exists('stream_socket_client')) {
            return null;
        }

        $parts = parse_url($url);

        if (!is_array($parts)) {
            return null;
        }

        $scheme = strtolower((string) ($parts['scheme'] ?? 'https'));
        $host = (string) ($parts['host'] ?? '');

        if ($host === '') {
            return null;
        }

        $port = isset($parts['port'])
            ? (int) $parts['port']
            : ($scheme === 'http' ? 80 : 443);

        $path = (string) ($parts['path'] ?? '/');

        if ($path === '') {
            $path = '/';
        }

        $query = (string) ($parts['query'] ?? '');
        $requestTarget = $query === '' ? $path : $path . '?' . $query;
        $transportScheme = $scheme === 'http' ? 'tcp' : 'ssl';
        $socketUri = sprintf('%s://%s:%d', $transportScheme, $host, $port);

        $errno = 0;
        $errstr = '';
        $socket = @stream_socket_client($socketUri, $errno, $errstr, 10);

        if ($socket === false) {
            return null;
        }

        stream_set_timeout($socket, 10);

        $request = "POST {$requestTarget} HTTP/1.1\r\n";
        $request .= "Host: {$host}\r\n";
        $request .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $request .= 'Content-Length: ' . strlen($encodedPayload) . "\r\n";
        $request .= "Connection: close\r\n\r\n";
        $request .= $encodedPayload;

        $bytesWritten = @fwrite($socket, $request);

        if (!is_int($bytesWritten) || $bytesWritten <= 0) {
            fclose($socket);

            return null;
        }

        $response = '';

        while (!feof($socket)) {
            $chunk = fgets($socket);

            if ($chunk === false) {
                break;
            }

            $response .= $chunk;
        }

        fclose($socket);

        if (trim($response) === '') {
            return null;
        }

        $split = preg_split("/\r\n\r\n/", $response, 2);

        if (!is_array($split) || count($split) < 2) {
            return null;
        }

        $headers = strtolower($split[0]);
        $body = $split[1];

        if (str_contains($headers, 'transfer-encoding: chunked')) {
            $decodedBody = self::decodeChunkedBody($body);

            if ($decodedBody === null) {
                return null;
            }

            $body = $decodedBody;
        }

        $decoded = json_decode(trim($body), true);

        return is_array($decoded) ? $decoded : null;
    }

    private static function decodeChunkedBody(string $body): ?string
    {
        $decoded = '';
        $position = 0;
        $length = strlen($body);

        while ($position < $length) {
            $lineEnd = strpos($body, "\r\n", $position);

            if ($lineEnd === false) {
                return null;
            }

            $line = trim(substr($body, $position, $lineEnd - $position));
            $line = (string) preg_replace('/;.*$/', '', $line);
            $line = strtolower($line);

            if ($line === '' || preg_match('/^[0-9a-f]+$/', $line) !== 1) {
                return null;
            }

            $chunkSize = hexdec($line);
            $position = $lineEnd + 2;

            if ($chunkSize === 0) {
                break;
            }

            if ($position + $chunkSize > $length) {
                return null;
            }

            $decoded .= substr($body, $position, $chunkSize);
            $position += $chunkSize + 2;
        }

        return $decoded;
    }
}
