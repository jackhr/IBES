<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Services\OrderRequestService;
use App\Support\EndpointGuard;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Throwable;

final class OrderRequestController extends ApiController
{
    public function __construct(private OrderRequestService $orderRequestService)
    {
    }

    public function store(Request $request)
    {
        try {
            $payload = $request->all();
            $blocked = EndpointGuard::protect($payload, 'order_requests', true);

            if (is_array($blocked)) {
                return response()->json($blocked, (int) ($blocked['status'] ?? 400));
            }

            $orderRequest = $this->orderRequestService->create($payload);

            return $this->success([
                'orderRequest' => $orderRequest,
            ], 201);
        } catch (InvalidArgumentException $exception) {
            return $this->error($exception->getMessage(), 422);
        } catch (Throwable $exception) {
            return $this->error($exception->getMessage(), 500);
        }
    }

    public function showByKey(string $key)
    {
        try {
            $orderRequest = $this->orderRequestService->findByKey($key);

            if ($orderRequest === null) {
                return $this->error('Order request not found.', 404);
            }

            return $this->success([
                'orderRequest' => $orderRequest,
            ]);
        } catch (Throwable $exception) {
            return $this->error($exception->getMessage(), 500);
        }
    }
}
