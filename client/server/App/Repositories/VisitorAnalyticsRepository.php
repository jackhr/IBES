<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class VisitorAnalyticsRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function upsertSession(
        string $visitorId,
        string $sessionId,
        string $eventAt,
        string $entryPath,
        string $entryReferrer,
        string $ipAddress,
        string $userAgent,
        string $deviceType,
        bool $isBot,
        ?string $osName,
        ?string $browserName,
        ?string $language,
        ?string $timezone
    ): int {
        $existing = $this->findSessionBySessionId($sessionId);

        if (is_array($existing)) {
            $this->updateSession((int) $existing['id'], $eventAt, $ipAddress, $userAgent, $deviceType, $isBot, $osName, $browserName, $language, $timezone);

            return (int) $existing['id'];
        }

        return $this->insertSession(
            $visitorId,
            $sessionId,
            $eventAt,
            $entryPath,
            $entryReferrer,
            $ipAddress,
            $userAgent,
            $deviceType,
            $isBot,
            $osName,
            $browserName,
            $language,
            $timezone
        );
    }

    public function insertPageView(
        int $visitorSessionId,
        string $visitorId,
        string $visitedAt,
        string $routePath,
        string $fullUrl,
        string $queryString,
        string $referrer,
        string $userAgent,
        string $deviceType,
        bool $isBot,
        ?string $osName,
        ?string $browserName,
        ?string $language,
        ?string $timezone,
        string $ipAddress,
        ?int $viewportWidth,
        ?int $viewportHeight,
        ?int $screenWidth,
        ?int $screenHeight,
        string $eventType,
        string $metadataJson
    ): int {
        $statement = $this->pdo->prepare(
            'INSERT INTO visitor_page_views (
                visitor_session_id,
                visitor_id,
                visited_at,
                route_path,
                full_url,
                query_string,
                referrer,
                user_agent,
                device_type,
                is_bot,
                os_name,
                browser_name,
                language,
                timezone,
                ip_address,
                viewport_width,
                viewport_height,
                screen_width,
                screen_height,
                event_type,
                metadata,
                created_at,
                updated_at
            ) VALUES (
                :visitor_session_id,
                :visitor_id,
                :visited_at,
                :route_path,
                :full_url,
                :query_string,
                :referrer,
                :user_agent,
                :device_type,
                :is_bot,
                :os_name,
                :browser_name,
                :language,
                :timezone,
                :ip_address,
                :viewport_width,
                :viewport_height,
                :screen_width,
                :screen_height,
                :event_type,
                :metadata,
                CURRENT_TIMESTAMP,
                CURRENT_TIMESTAMP
            )'
        );

        $statement->execute([
            'visitor_session_id' => $visitorSessionId,
            'visitor_id' => $visitorId,
            'visited_at' => $visitedAt,
            'route_path' => $routePath,
            'full_url' => $fullUrl !== '' ? $fullUrl : null,
            'query_string' => $queryString !== '' ? $queryString : null,
            'referrer' => $referrer !== '' ? $referrer : null,
            'user_agent' => $userAgent !== '' ? $userAgent : null,
            'device_type' => $deviceType,
            'is_bot' => $isBot ? 1 : 0,
            'os_name' => $osName,
            'browser_name' => $browserName,
            'language' => $language,
            'timezone' => $timezone,
            'ip_address' => $ipAddress !== '' ? $ipAddress : null,
            'viewport_width' => $viewportWidth,
            'viewport_height' => $viewportHeight,
            'screen_width' => $screenWidth,
            'screen_height' => $screenHeight,
            'event_type' => $eventType,
            'metadata' => $metadataJson,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    /** @return array<string, mixed>|null */
    private function findSessionBySessionId(string $sessionId): ?array
    {
        $statement = $this->pdo->prepare('SELECT id FROM visitor_sessions WHERE session_id = :session_id LIMIT 1');
        $statement->execute(['session_id' => $sessionId]);

        $row = $statement->fetch();

        return is_array($row) ? $row : null;
    }

    private function insertSession(
        string $visitorId,
        string $sessionId,
        string $eventAt,
        string $entryPath,
        string $entryReferrer,
        string $ipAddress,
        string $userAgent,
        string $deviceType,
        bool $isBot,
        ?string $osName,
        ?string $browserName,
        ?string $language,
        ?string $timezone
    ): int {
        $statement = $this->pdo->prepare(
            'INSERT INTO visitor_sessions (
                visitor_id,
                session_id,
                first_seen_at,
                last_seen_at,
                entry_path,
                entry_referrer,
                ip_address,
                user_agent,
                device_type,
                is_bot,
                os_name,
                browser_name,
                language,
                timezone,
                created_at,
                updated_at
            ) VALUES (
                :visitor_id,
                :session_id,
                :first_seen_at,
                :last_seen_at,
                :entry_path,
                :entry_referrer,
                :ip_address,
                :user_agent,
                :device_type,
                :is_bot,
                :os_name,
                :browser_name,
                :language,
                :timezone,
                CURRENT_TIMESTAMP,
                CURRENT_TIMESTAMP
            )'
        );

        $statement->execute([
            'visitor_id' => $visitorId,
            'session_id' => $sessionId,
            'first_seen_at' => $eventAt,
            'last_seen_at' => $eventAt,
            'entry_path' => $entryPath,
            'entry_referrer' => $entryReferrer !== '' ? $entryReferrer : null,
            'ip_address' => $ipAddress !== '' ? $ipAddress : null,
            'user_agent' => $userAgent !== '' ? $userAgent : null,
            'device_type' => $deviceType,
            'is_bot' => $isBot ? 1 : 0,
            'os_name' => $osName,
            'browser_name' => $browserName,
            'language' => $language,
            'timezone' => $timezone,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    private function updateSession(
        int $sessionDbId,
        string $eventAt,
        string $ipAddress,
        string $userAgent,
        string $deviceType,
        bool $isBot,
        ?string $osName,
        ?string $browserName,
        ?string $language,
        ?string $timezone
    ): void {
        $statement = $this->pdo->prepare(
            'UPDATE visitor_sessions SET
                last_seen_at = :last_seen_at,
                ip_address = :ip_address,
                user_agent = :user_agent,
                device_type = :device_type,
                is_bot = :is_bot,
                os_name = :os_name,
                browser_name = :browser_name,
                language = :language,
                timezone = :timezone,
                updated_at = CURRENT_TIMESTAMP
             WHERE id = :id'
        );

        $statement->execute([
            'id' => $sessionDbId,
            'last_seen_at' => $eventAt,
            'ip_address' => $ipAddress !== '' ? $ipAddress : null,
            'user_agent' => $userAgent !== '' ? $userAgent : null,
            'device_type' => $deviceType,
            'is_bot' => $isBot ? 1 : 0,
            'os_name' => $osName,
            'browser_name' => $browserName,
            'language' => $language,
            'timezone' => $timezone,
        ]);
    }
}

