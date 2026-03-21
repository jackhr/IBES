<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\AddOn;
use App\Models\OrderRequest;
use App\Models\TaxiRequest;
use App\Models\Vehicle;
use App\Models\VehicleDiscount;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function summary(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'vehicles_total' => Vehicle::query()->count(),
                'vehicles_showing' => Vehicle::query()->where('showing', 1)->count(),
                'add_ons_total' => AddOn::query()->count(),
                'vehicle_discounts_total' => VehicleDiscount::query()->count(),
                'order_requests_total' => OrderRequest::query()->count(),
                'order_requests_pending' => OrderRequest::query()->where('confirmed', 0)->count(),
                'order_requests_revenue' => (float) (OrderRequest::query()->sum('sub_total') ?? 0),
                'taxi_requests_total' => TaxiRequest::query()->count(),
            ],
        ]);
    }
}
