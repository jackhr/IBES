<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnalyticsDailyMetric extends Model
{
    protected $table = 'analytics_daily_metrics';

    protected $fillable = [
        'snapshot_date',
        'order_requests_count',
        'new_customers_count',
        'active_vehicles_count',
        'revenue_usd',
        'growth_rate_pct',
        'metadata',
        'captured_at',
    ];

    protected function casts(): array
    {
        return [
            'snapshot_date' => 'date',
            'order_requests_count' => 'integer',
            'new_customers_count' => 'integer',
            'active_vehicles_count' => 'integer',
            'revenue_usd' => 'float',
            'growth_rate_pct' => 'float',
            'metadata' => 'array',
            'captured_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
