<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Services\VehicleDiscountService;
use Illuminate\Http\Request;
use Throwable;

final class VehicleDiscountController extends ApiController
{
    public function __construct(private VehicleDiscountService $vehicleDiscountService)
    {
    }

    public function index(Request $request)
    {
        try {
            $vehicleId = $request->filled('vehicleId') ? (int) $request->query('vehicleId') : null;
            $days = $request->filled('days') ? (int) $request->query('days') : null;

            if ($vehicleId !== null && $vehicleId > 0 && $days !== null && $days > 0) {
                $discount = $this->vehicleDiscountService->findBestForDays($vehicleId, $days);

                if ($discount === null) {
                    return $this->error('Vehicle discount not found.', 404);
                }

                return $this->success([
                    'vehicleDiscount' => $discount,
                ]);
            }

            $normalizedVehicleId = $vehicleId !== null && $vehicleId > 0 ? $vehicleId : null;

            return $this->success([
                'vehicleDiscounts' => $this->vehicleDiscountService->list($normalizedVehicleId),
            ]);
        } catch (Throwable $exception) {
            return $this->error($exception->getMessage(), 500);
        }
    }
}
