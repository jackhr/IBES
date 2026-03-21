<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\JsonResponse;
use App\Core\Request;
use App\Services\VehicleRequestService;
use App\Support\EndpointGuard;

final class VehicleRequestController
{
    public function __construct(private VehicleRequestService $vehicleRequestService)
    {
    }

    public function __invoke(): void
    {
        try {
            $payload = Request::json();

            $blocked = EndpointGuard::protect($payload, 'reservation', true);

            if (is_array($blocked)) {
                JsonResponse::send($blocked, (int) ($blocked['status'] ?? 400));

                return;
            }

            $result = $this->vehicleRequestService->submit($payload);
            JsonResponse::send($result, (int) ($result['status'] ?? 200));
        } catch (\InvalidArgumentException $exception) {
            JsonResponse::send([
                'success' => false,
                'message' => $exception->getMessage(),
                'status' => 422,
                'data' => [],
            ], 422);
        } catch (\RuntimeException $exception) {
            JsonResponse::send([
                'success' => false,
                'message' => $exception->getMessage(),
                'status' => 400,
                'data' => [],
            ], 400);
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
