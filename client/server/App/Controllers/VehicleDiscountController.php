<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\JsonResponse;
use App\Services\VehicleDiscountService;

final class VehicleDiscountController
{
    public function __construct(private VehicleDiscountService $vehicleDiscountService)
    {
    }

    public function index(): void
    {
        try {
            $vehicleId = isset($_GET['vehicleId']) ? (int) $_GET['vehicleId'] : null;
            $days = isset($_GET['days']) ? (int) $_GET['days'] : null;

            if ($vehicleId !== null && $vehicleId > 0 && $days !== null && $days > 0) {
                $discount = $this->vehicleDiscountService->findBestForDays($vehicleId, $days);

                if ($discount === null) {
                    JsonResponse::send([
                        'success' => false,
                        'message' => 'Vehicle discount not found.',
                        'status' => 404,
                        'data' => [],
                    ], 404);

                    return;
                }

                JsonResponse::send([
                    'success' => true,
                    'message' => 'success',
                    'status' => 200,
                    'data' => [
                        'vehicleDiscount' => $discount,
                    ],
                ], 200);

                return;
            }

            $normalizedVehicleId = $vehicleId !== null && $vehicleId > 0 ? $vehicleId : null;

            JsonResponse::send([
                'success' => true,
                'message' => 'success',
                'status' => 200,
                'data' => [
                    'vehicleDiscounts' => $this->vehicleDiscountService->list($normalizedVehicleId),
                ],
            ], 200);
        } catch (\Throwable $exception) {
            $this->sendServerError($exception);
        }
    }

    private function sendServerError(\Throwable $exception): void
    {
        JsonResponse::send([
            'success' => false,
            'message' => $exception->getMessage(),
            'status' => 500,
            'data' => [],
        ], 500);
    }
}
