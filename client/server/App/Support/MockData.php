<?php

declare(strict_types=1);

namespace App\Support;

use RuntimeException;

final class MockData
{
    private const TABLES = [
        'add_ons',
        'admin_api_tokens',
        'admin_users',
        'contact_info',
        'analytics_daily_metrics',
        'migrations',
        'order_requests',
        'order_request_add_ons',
        'taxi_requests',
        'visitor_sessions',
        'visitor_page_views',
        'vehicles',
        'vehicle_discounts',
    ];

    public function __construct(private ?string $basePath = null)
    {
        if ($this->basePath === null) {
            $this->basePath = dirname(__DIR__, 3) . '/mock-data/raw';
        }
    }

    /** @return array<string, mixed> */
    public function summary(): array
    {
        $summary = $this->readJson('_summary');

        return is_array($summary) ? $summary : [];
    }

    /** @return array<int, array<string, mixed>> */
    public function table(string $table): array
    {
        if (!in_array($table, self::TABLES, true)) {
            throw new RuntimeException("Unsupported mock table: {$table}");
        }

        $data = $this->readJson($table);

        if (!is_array($data)) {
            return [];
        }

        /** @var array<int, array<string, mixed>> $data */
        return $data;
    }

    /** @return array<int, array<string, mixed>> */
    public function addOns(): array
    {
        return $this->table('add_ons');
    }

    /** @return array<int, array<string, mixed>> */
    public function adminUsers(): array
    {
        return $this->table('admin_users');
    }

    /** @return array<int, array<string, mixed>> */
    public function adminApiTokens(): array
    {
        return $this->table('admin_api_tokens');
    }

    /** @return array<int, array<string, mixed>> */
    public function contactInfo(): array
    {
        return $this->table('contact_info');
    }

    /** @return array<int, array<string, mixed>> */
    public function analyticsDailyMetrics(): array
    {
        return $this->table('analytics_daily_metrics');
    }

    /** @return array<int, array<string, mixed>> */
    public function migrations(): array
    {
        return $this->table('migrations');
    }

    /** @return array<int, array<string, mixed>> */
    public function orderRequests(): array
    {
        return $this->table('order_requests');
    }

    /** @return array<int, array<string, mixed>> */
    public function orderRequestAddOns(): array
    {
        return $this->table('order_request_add_ons');
    }

    /** @return array<int, array<string, mixed>> */
    public function taxiRequests(): array
    {
        return $this->table('taxi_requests');
    }

    /** @return array<int, array<string, mixed>> */
    public function visitorSessions(): array
    {
        return $this->table('visitor_sessions');
    }

    /** @return array<int, array<string, mixed>> */
    public function visitorPageViews(): array
    {
        return $this->table('visitor_page_views');
    }

    /** @return array<int, array<string, mixed>> */
    public function vehicles(): array
    {
        return $this->table('vehicles');
    }

    /** @return array<int, array<string, mixed>> */
    public function vehicleDiscounts(): array
    {
        return $this->table('vehicle_discounts');
    }

    /** @return array<int, array<string, mixed>>|array<string, mixed> */
    private function readJson(string $filename): array
    {
        $path = rtrim((string) $this->basePath, '/\\') . '/' . $filename . '.json';

        if (!is_file($path)) {
            throw new RuntimeException("Mock data file not found: {$path}");
        }

        $contents = file_get_contents($path);

        if ($contents === false) {
            throw new RuntimeException("Unable to read mock data file: {$path}");
        }

        $decoded = json_decode($contents, true);

        if (!is_array($decoded)) {
            throw new RuntimeException("Invalid JSON in mock data file: {$path}");
        }

        return $decoded;
    }
}
