<?php

namespace App\Services;

use App\Models\AnalyticsDailyMetric;
use App\Models\OrderRequest;
use App\Models\VisitorPageView;
use Carbon\CarbonImmutable;
use Illuminate\Database\QueryException;

class AdminAnalyticsService
{
    private const RANGE_DAYS = [
        '7d' => 7,
        '30d' => 30,
        '90d' => 90,
    ];

    public function build(string $range): array
    {
        $days = $this->resolveRangeDays($range);
        $now = CarbonImmutable::now('UTC');
        $endDate = $now->startOfDay();
        $startDate = $endDate->subDays($days - 1);

        $dailyRows = $this->buildDailyRows($startDate, $endDate);

        $metrics = $this->buildMetricCards($dailyRows, $days, $now);
        $this->persistDailySnapshots($dailyRows, $now);

        return [
            'range' => $range,
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'generated_at' => $now->toIso8601String(),
            'cards' => $metrics,
            'chart' => $dailyRows,
            'table' => array_values(array_reverse($dailyRows)),
        ];
    }

    private function resolveRangeDays(string $range): int
    {
        return self::RANGE_DAYS[$range] ?? self::RANGE_DAYS['90d'];
    }

    /** @return array<int, array<string, int|float|string>> */
    private function buildDailyRows(CarbonImmutable $startDate, CarbonImmutable $endDate): array
    {
        $rowsByDate = [];
        $cursor = $startDate;

        while ($cursor->lessThanOrEqualTo($endDate)) {
            $date = $cursor->toDateString();
            $rowsByDate[$date] = [
                'date' => $date,
                'label' => $cursor->format('M j'),
                'revenue_usd' => 0.0,
                'order_requests' => 0,
                'new_customers' => 0,
                'active_vehicles' => 0,
                'unique_visitors' => 0,
                'mobile_visitors' => 0,
                'desktop_visitors' => 0,
                'page_views' => 0,
                'growth_rate_pct' => 0.0,
            ];
            $cursor = $cursor->addDay();
        }

        $orderAggregates = OrderRequest::query()
            ->selectRaw('DATE(created_at) AS metric_date')
            ->selectRaw('COUNT(*) AS order_requests')
            ->selectRaw('COALESCE(SUM(sub_total), 0) AS revenue_usd')
            ->whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->groupBy('metric_date')
            ->orderBy('metric_date')
            ->get();

        foreach ($orderAggregates as $aggregate) {
            $metricDate = (string) $aggregate->metric_date;

            if (! isset($rowsByDate[$metricDate])) {
                continue;
            }

            $orders = (int) $aggregate->order_requests;
            $rowsByDate[$metricDate]['order_requests'] = $orders;
            $rowsByDate[$metricDate]['new_customers'] = $orders;
            $rowsByDate[$metricDate]['revenue_usd'] = round((float) $aggregate->revenue_usd, 2);
        }

        try {
            $visitorAggregates = VisitorPageView::query()
                ->selectRaw('DATE(visited_at) AS metric_date')
                ->selectRaw('COUNT(*) AS page_views')
                ->selectRaw('COUNT(DISTINCT visitor_id) AS unique_visitors')
                ->selectRaw("COUNT(DISTINCT CASE WHEN device_type = 'mobile' THEN visitor_id END) AS mobile_visitors")
                ->selectRaw("COUNT(DISTINCT CASE WHEN device_type = 'desktop' THEN visitor_id END) AS desktop_visitors")
                ->whereBetween('visited_at', [$startDate->startOfDay(), $endDate->endOfDay()])
                ->groupBy('metric_date')
                ->orderBy('metric_date')
                ->get();

            foreach ($visitorAggregates as $aggregate) {
                $metricDate = (string) $aggregate->metric_date;

                if (! isset($rowsByDate[$metricDate])) {
                    continue;
                }

                $rowsByDate[$metricDate]['unique_visitors'] = (int) ($aggregate->unique_visitors ?? 0);
                $rowsByDate[$metricDate]['mobile_visitors'] = (int) ($aggregate->mobile_visitors ?? 0);
                $rowsByDate[$metricDate]['desktop_visitors'] = (int) ($aggregate->desktop_visitors ?? 0);
                $rowsByDate[$metricDate]['page_views'] = (int) ($aggregate->page_views ?? 0);
            }
        } catch (QueryException) {
            // Visitor tables may not exist yet; keep analytics payload resilient.
        }

        $activeVehicleSets = [];
        $overlappingOrders = OrderRequest::query()
            ->select(['car_id', 'pick_up', 'drop_off'])
            ->where('pick_up', '<=', $endDate->endOfDay())
            ->where('drop_off', '>=', $startDate->startOfDay())
            ->whereNotNull('car_id')
            ->get();

        foreach ($overlappingOrders as $order) {
            $bookingStart = CarbonImmutable::parse((string) $order->pick_up, 'UTC')->startOfDay();
            $bookingEnd = CarbonImmutable::parse((string) $order->drop_off, 'UTC')->startOfDay();

            if ($bookingEnd->lessThan($startDate) || $bookingStart->greaterThan($endDate)) {
                continue;
            }

            $windowStart = $bookingStart->greaterThan($startDate) ? $bookingStart : $startDate;
            $windowEnd = $bookingEnd->lessThan($endDate) ? $bookingEnd : $endDate;
            $day = $windowStart;

            while ($day->lessThanOrEqualTo($windowEnd)) {
                $date = $day->toDateString();

                if (! isset($activeVehicleSets[$date])) {
                    $activeVehicleSets[$date] = [];
                }

                $activeVehicleSets[$date][(int) $order->car_id] = true;
                $day = $day->addDay();
            }
        }

        foreach ($activeVehicleSets as $date => $vehicles) {
            if (isset($rowsByDate[$date])) {
                $rowsByDate[$date]['active_vehicles'] = count($vehicles);
            }
        }

        $previousRevenue = 0.0;

        foreach ($rowsByDate as &$row) {
            $currentRevenue = (float) $row['revenue_usd'];
            $row['growth_rate_pct'] = round($this->percentageChange($currentRevenue, $previousRevenue), 2);
            $previousRevenue = $currentRevenue;
        }
        unset($row);

        return array_values($rowsByDate);
    }

    /**
     * @param  array<int, array<string, int|float|string>>  $dailyRows
     * @return array<string, array<string, float>>
     */
    private function buildMetricCards(array $dailyRows, int $days, CarbonImmutable $now): array
    {
        $currentRevenue = array_reduce($dailyRows, static function (float $carry, array $row): float {
            return $carry + (float) $row['revenue_usd'];
        }, 0.0);

        $currentActiveVehicles = $this->countActiveVehiclesAt($now);
        $newCustomerWindow = $this->buildTrailingWindowFromNow($now, 30);
        $currentOrders = $this->countOrderRequestsBetween($newCustomerWindow['start'], $newCustomerWindow['end']);
        $previousNewCustomerWindow = [
            'start' => $newCustomerWindow['start']->subDays(30),
            'end' => $newCustomerWindow['start']->subDay(),
        ];
        $previousOrders = $this->countOrderRequestsBetween(
            $previousNewCustomerWindow['start'],
            $previousNewCustomerWindow['end']
        );

        $previousEnd = $now->subDays($days)->startOfDay();
        $previousStart = $previousEnd->subDays($days - 1);
        $previousRows = $this->buildDailyRows($previousStart, $previousEnd);

        $previousRevenue = array_reduce($previousRows, static function (float $carry, array $row): float {
            return $carry + (float) $row['revenue_usd'];
        }, 0.0);
        $previousActiveVehicles = $this->countActiveVehiclesAt($now->subDays($days));
        $currentGrowthRate = $this->percentageChange($currentRevenue, $previousRevenue);

        $prePreviousEnd = $previousStart->subDay();
        $prePreviousStart = $prePreviousEnd->subDays($days - 1);
        $prePreviousRows = $this->buildDailyRows($prePreviousStart, $prePreviousEnd);
        $prePreviousRevenue = array_reduce($prePreviousRows, static function (float $carry, array $row): float {
            return $carry + (float) $row['revenue_usd'];
        }, 0.0);

        $previousGrowthRate = $this->percentageChange($previousRevenue, $prePreviousRevenue);

        return [
            'total_revenue' => [
                'value' => round($currentRevenue, 2),
                'change_pct' => round($this->percentageChange($currentRevenue, $previousRevenue), 2),
            ],
            'new_customers' => [
                'value' => (float) $currentOrders,
                'change_pct' => round($this->percentageChange((float) $currentOrders, (float) $previousOrders), 2),
            ],
            'current_vehicles' => [
                'value' => (float) $currentActiveVehicles,
                'change_pct' => round($this->percentageChange((float) $currentActiveVehicles, (float) $previousActiveVehicles), 2),
            ],
            'growth_rate' => [
                'value' => round($currentGrowthRate, 2),
                'change_pct' => round($currentGrowthRate - $previousGrowthRate, 2),
            ],
        ];
    }

    /** @return array{start: CarbonImmutable, end: CarbonImmutable} */
    private function buildTrailingWindowFromNow(CarbonImmutable $now, int $days): array
    {
        $end = $now->startOfDay();
        $start = $end->subDays(max(1, $days) - 1);

        return [
            'start' => $start,
            'end' => $end,
        ];
    }

    private function countOrderRequestsBetween(CarbonImmutable $startDate, CarbonImmutable $endDate): int
    {
        return OrderRequest::query()
            ->whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->count();
    }

    private function countActiveVehiclesAt(CarbonImmutable $moment): int
    {
        return OrderRequest::query()
            ->where('pick_up', '<=', $moment)
            ->where('drop_off', '>=', $moment)
            ->distinct('car_id')
            ->count('car_id');
    }

    private function percentageChange(float $current, float $baseline): float
    {
        if ($baseline <= 0) {
            if ($current <= 0) {
                return 0.0;
            }

            return 100.0;
        }

        return (($current - $baseline) / $baseline) * 100;
    }

    /** @param array<int, array<string, int|float|string>> $dailyRows */
    private function persistDailySnapshots(array $dailyRows, CarbonImmutable $capturedAt): void
    {
        $payload = [];
        $capturedAtString = $capturedAt->toDateTimeString();

        foreach ($dailyRows as $row) {
            $payload[] = [
                'snapshot_date' => (string) $row['date'],
                'order_requests_count' => (int) $row['order_requests'],
                'new_customers_count' => (int) $row['new_customers'],
                'active_vehicles_count' => (int) $row['active_vehicles'],
                'revenue_usd' => (float) $row['revenue_usd'],
                'growth_rate_pct' => (float) $row['growth_rate_pct'],
                'unique_visitors_count' => (int) ($row['unique_visitors'] ?? 0),
                'mobile_visitors_count' => (int) ($row['mobile_visitors'] ?? 0),
                'desktop_visitors_count' => (int) ($row['desktop_visitors'] ?? 0),
                'page_views_count' => (int) ($row['page_views'] ?? 0),
                'metadata' => json_encode([
                    'label' => (string) $row['label'],
                    'source' => 'order_requests_live',
                ]) ?: '{}',
                'captured_at' => $capturedAtString,
                'updated_at' => $capturedAtString,
                'created_at' => $capturedAtString,
            ];
        }

        if ($payload === []) {
            return;
        }

        try {
            AnalyticsDailyMetric::query()->upsert(
                $payload,
                ['snapshot_date'],
                [
                    'order_requests_count',
                    'new_customers_count',
                    'active_vehicles_count',
                    'revenue_usd',
                    'growth_rate_pct',
                    'unique_visitors_count',
                    'mobile_visitors_count',
                    'desktop_visitors_count',
                    'page_views_count',
                    'metadata',
                    'captured_at',
                    'updated_at',
                ]
            );
        } catch (QueryException) {
            // Snapshot persistence is best-effort; analytics payload should still resolve.
        }
    }
}
