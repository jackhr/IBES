<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\JsonResponse;
use App\Core\Request;
use App\Services\OrderRequestService;
use App\Support\EndpointGuard;
use InvalidArgumentException;

final class OrderRequestController
{
    public function __construct(private OrderRequestService $orderRequestService)
    {
    }

    public function store(): void
    {
        try {
            $payload = Request::json();

            $blocked = EndpointGuard::protect($payload, 'order_requests', true);

            if (is_array($blocked)) {
                JsonResponse::send($blocked, (int) ($blocked['status'] ?? 400));

                return;
            }

            $orderRequest = $this->orderRequestService->create($payload);

            JsonResponse::send([
                'success' => true,
                'message' => 'success',
                'status' => 201,
                'data' => [
                    'orderRequest' => $orderRequest,
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

    public function showByKey(string $key): void
    {
        try {
            $orderRequest = $this->orderRequestService->findByKey($key);

            if ($orderRequest === null) {
                JsonResponse::send([
                    'success' => false,
                    'message' => 'Order request not found.',
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
                    'orderRequest' => $orderRequest,
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
