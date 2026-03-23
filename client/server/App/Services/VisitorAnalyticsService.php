<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\VisitorAnalyticsRepository;
use App\Support\ClientIpResolver;
use App\Support\Settings;
use App\Support\UserAgentInspector;
use DateTimeImmutable;
use DateTimeZone;

final class VisitorAnalyticsService
{
    public function __construct(private VisitorAnalyticsRepository $visitorAnalyticsRepository)
    {
    }

    /** @param array<string, mixed> $payload
     *  @return array<string, mixed>
     */
    public function track(array $payload): array
    {
        if (!Settings::visitorTrackingEnabled()) {
            return [
                'trackingEnabled' => false,
            ];
        }

        $nowUtc = new DateTimeImmutable('now', new DateTimeZone('UTC'));
        $eventAt = $this->resolveDateTime($payload, ['occurredAt', 'occurred_at', 'visitedAt', 'visited_at'], $nowUtc);
        $eventAtSql = $eventAt->format('Y-m-d H:i:s');

        $visitorId = $this->resolveIdentifier($this->optionalString($payload, ['visitorId', 'visitor_id'], 64));
        $sessionId = $this->resolveIdentifier($this->optionalString($payload, ['sessionId', 'session_id'], 64));
        $routePath = $this->normalizePath($this->optionalString($payload, ['path', 'pathname', 'routePath', 'route_path'], 255));
        $fullUrl = $this->optionalString($payload, ['fullUrl', 'full_url'], 2000);
        $queryString = $this->optionalString($payload, ['queryString', 'query_string'], 2000);
        $referrer = $this->optionalString($payload, ['referrer', 'referrerUrl', 'referrer_url'], 2000);
        $language = $this->normalizeLanguage($this->optionalString($payload, ['language', 'locale'], 32));
        $timezone = $this->optionalString($payload, ['timezone', 'tz'], 64);
        $eventType = $this->normalizeEventType($this->optionalString($payload, ['eventType', 'event_type'], 40));
        $userAgent = $this->resolveUserAgent($payload);
        $ipAddress = ClientIpResolver::resolve();

        $ua = UserAgentInspector::inspect($userAgent);

        $viewportWidth = $this->optionalInt($payload, ['viewportWidth', 'viewport_width'], 0, 10000);
        $viewportHeight = $this->optionalInt($payload, ['viewportHeight', 'viewport_height'], 0, 10000);
        $screenWidth = $this->optionalInt($payload, ['screenWidth', 'screen_width'], 0, 20000);
        $screenHeight = $this->optionalInt($payload, ['screenHeight', 'screen_height'], 0, 20000);

        $metadata = [
            'source' => 'client_web',
            'query_length' => strlen($queryString),
            'captured_at_utc' => $nowUtc->format(DATE_ATOM),
        ];

        $metadataJson = json_encode($metadata) ?: '{}';

        $visitorSessionId = $this->visitorAnalyticsRepository->upsertSession(
            $visitorId,
            $sessionId,
            $eventAtSql,
            $routePath,
            $referrer,
            $ipAddress,
            $userAgent,
            (string) ($ua['deviceType'] ?? 'other'),
            (bool) ($ua['isBot'] ?? false),
            is_string($ua['osName'] ?? null) ? $ua['osName'] : null,
            is_string($ua['browserName'] ?? null) ? $ua['browserName'] : null,
            $language,
            $timezone
        );

        $pageViewId = $this->visitorAnalyticsRepository->insertPageView(
            $visitorSessionId,
            $visitorId,
            $eventAtSql,
            $routePath,
            $fullUrl,
            $queryString,
            $referrer,
            $userAgent,
            (string) ($ua['deviceType'] ?? 'other'),
            (bool) ($ua['isBot'] ?? false),
            is_string($ua['osName'] ?? null) ? $ua['osName'] : null,
            is_string($ua['browserName'] ?? null) ? $ua['browserName'] : null,
            $language,
            $timezone,
            $ipAddress,
            $viewportWidth,
            $viewportHeight,
            $screenWidth,
            $screenHeight,
            $eventType,
            $metadataJson
        );

        return [
            'eventId' => $pageViewId,
            'visitorId' => $visitorId,
            'sessionId' => $sessionId,
            'deviceType' => (string) ($ua['deviceType'] ?? 'other'),
            'isBot' => (bool) ($ua['isBot'] ?? false),
            'trackedAt' => $eventAt->format(DATE_ATOM),
        ];
    }

    /** @param array<string, mixed> $payload
     *  @param array<int, string> $keys
     */
    private function resolveDateTime(array $payload, array $keys, DateTimeImmutable $fallback): DateTimeImmutable
    {
        foreach ($keys as $key) {
            if (!array_key_exists($key, $payload)) {
                continue;
            }

            $raw = trim((string) $payload[$key]);

            if ($raw === '') {
                continue;
            }

            try {
                return new DateTimeImmutable($raw, new DateTimeZone('UTC'));
            } catch (\Throwable) {
                continue;
            }
        }

        return $fallback;
    }

    /** @param array<string, mixed> $payload
     *  @param array<int, string> $keys
     */
    private function optionalString(array $payload, array $keys, int $maxLength): string
    {
        foreach ($keys as $key) {
            if (!array_key_exists($key, $payload)) {
                continue;
            }

            $value = trim((string) $payload[$key]);

            if ($value === '') {
                return '';
            }

            if (strlen($value) > $maxLength) {
                return substr($value, 0, $maxLength);
            }

            return $value;
        }

        return '';
    }

    /** @param array<string, mixed> $payload
     *  @param array<int, string> $keys
     */
    private function optionalInt(array $payload, array $keys, int $min, int $max): ?int
    {
        foreach ($keys as $key) {
            if (!array_key_exists($key, $payload)) {
                continue;
            }

            $raw = $payload[$key];

            if ($raw === null || $raw === '') {
                return null;
            }

            if (!is_int($raw) && !is_numeric($raw)) {
                return null;
            }

            $value = (int) $raw;

            if ($value < $min || $value > $max) {
                return null;
            }

            return $value;
        }

        return null;
    }

    /** @param array<string, mixed> $payload */
    private function resolveUserAgent(array $payload): string
    {
        $fromPayload = $this->optionalString($payload, ['userAgent', 'user_agent'], 4000);

        if ($fromPayload !== '') {
            return $fromPayload;
        }

        $header = $_SERVER['HTTP_USER_AGENT'] ?? '';

        return trim((string) $header);
    }

    private function resolveIdentifier(string $value): string
    {
        if ($this->isUuid($value)) {
            return strtolower($value);
        }

        return $this->generateUuid();
    }

    private function normalizePath(string $path): string
    {
        if ($path === '') {
            return '/';
        }

        $parsed = parse_url($path, PHP_URL_PATH);
        $value = is_string($parsed) && $parsed !== '' ? $parsed : $path;
        $value = '/' . ltrim($value, '/');

        return substr($value, 0, 255);
    }

    private function normalizeLanguage(string $language): ?string
    {
        if ($language === '') {
            return null;
        }

        $value = substr(str_replace('_', '-', strtolower($language)), 0, 16);

        return $value !== '' ? $value : null;
    }

    private function normalizeEventType(string $eventType): string
    {
        if ($eventType === '') {
            return 'page_view';
        }

        $normalized = strtolower(preg_replace('/[^a-z0-9_]+/', '_', $eventType) ?? 'page_view');
        $normalized = trim($normalized, '_');

        if ($normalized === '') {
            return 'page_view';
        }

        return substr($normalized, 0, 32);
    }

    private function isUuid(string $value): bool
    {
        return preg_match(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $value
        ) === 1;
    }

    private function generateUuid(): string
    {
        $bytes = random_bytes(16);
        $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);
        $hex = bin2hex($bytes);

        return sprintf(
            '%s-%s-%s-%s-%s',
            substr($hex, 0, 8),
            substr($hex, 8, 4),
            substr($hex, 12, 4),
            substr($hex, 16, 4),
            substr($hex, 20, 12)
        );
    }
}
