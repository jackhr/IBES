<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\JsonResponse;
use App\Core\Request;
use App\Services\VehicleRequestService;

final class VehicleRequestController
{
    public function __construct(private VehicleRequestService $vehicleRequestService)
    {
    }

    public function __invoke(): void
    {
        try {
            $result = $this->vehicleRequestService->submit(Request::json());
            JsonResponse::send($result, (int) ($result['status'] ?? 200));
        } catch (\Throwable $exception) {
            JsonResponse::send([
                'success' => false,
                'message' => $exception->getMessage(),
                'status' => 500,
                'data' => [$exception->getMessage()],
            ], 500);
        }
    }
}
