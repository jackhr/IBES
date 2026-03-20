<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\JsonResponse;
use App\Core\Request;
use App\Services\TaxiRequestService;
use App\Support\EndpointGuard;
use InvalidArgumentException;

final class TaxiRequestController
{
    public function __construct(private TaxiRequestService $taxiRequestService)
    {
    }

    public function store(): void
    {
        try {
            $payload = Request::json();

            $blocked = EndpointGuard::protect($payload, 'taxi_requests', true);

            if (is_array($blocked)) {
                JsonResponse::send($blocked, (int) ($blocked['status'] ?? 400));

                return;
            }

            $taxiRequest = $this->taxiRequestService->create($payload);

            JsonResponse::send([
                'success' => true,
                'message' => 'success',
                'status' => 201,
                'data' => [
                    'taxiRequest' => $taxiRequest,
                ],
            ], 201);
        } catch (InvalidArgumentException $exception) {
            JsonResponse::send([
                'success' => false,
                'message' => $exception->getMessage(),
                'status' => 422,
                'data' => [],
            ], 422);
        } catch (\Throwable $exception) {
            $this->sendServerError($exception);
        }
    }

    public function show(int $requestId): void
    {
        try {
            $taxiRequest = $this->taxiRequestService->find($requestId);

            if ($taxiRequest === null) {
                JsonResponse::send([
                    'success' => false,
                    'message' => 'Taxi request not found.',
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
                    'taxiRequest' => $taxiRequest,
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
