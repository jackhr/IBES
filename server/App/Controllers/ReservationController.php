<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\JsonResponse;
use App\Core\Request;
use App\Services\ReservationService;
use App\Support\EndpointGuard;

final class ReservationController
{
    public function __construct(private ReservationService $reservationService)
    {
    }

    public function __invoke(): void
    {
        try {
            $payload = Request::json();

            $blocked = EndpointGuard::protect($payload, 'reservation_api', false);

            if (is_array($blocked)) {
                JsonResponse::send($blocked, (int) ($blocked['status'] ?? 400));

                return;
            }

            $result = $this->reservationService->handle($payload);
            JsonResponse::send($result, 200);
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
                'data' => [],
            ], 500);
        }
    }
}
