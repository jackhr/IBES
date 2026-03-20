<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\JsonResponse;
use App\Services\VehicleService;

final class VehicleController
{
    public function __construct(private VehicleService $vehicleService)
    {
    }

    public function index(): void
    {
        try {
            $showingOnly = false;

            if (isset($_GET['showing'])) {
                $showingOnly = filter_var($_GET['showing'], FILTER_VALIDATE_BOOL) ?? false;
            }

            JsonResponse::send([
                'success' => true,
                'message' => 'success',
                'status' => 200,
                'data' => [
                    'vehicles' => $this->vehicleService->list($showingOnly),
                ],
            ], 200);
        } catch (\Throwable $exception) {
            $this->sendServerError($exception);
        }
    }

    public function landing(): void
    {
        try {
            JsonResponse::send([
                'success' => true,
                'message' => 'success',
                'status' => 200,
                'data' => [
                    'vehicles' => $this->vehicleService->landing(),
                ],
            ], 200);
        } catch (\Throwable $exception) {
            $this->sendServerError($exception);
        }
    }

    public function show(int $id): void
    {
        try {
            $vehicle = $this->vehicleService->find($id);

            if ($vehicle === null) {
                JsonResponse::send([
                    'success' => false,
                    'message' => 'Vehicle not found.',
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
                    'vehicle' => $vehicle,
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
