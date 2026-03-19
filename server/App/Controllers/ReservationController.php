<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\JsonResponse;
use App\Core\Request;
use App\Services\ReservationService;

final class ReservationController
{
    public function __construct(private ReservationService $reservationService)
    {
    }

    public function __invoke(): void
    {
        try {
            $result = $this->reservationService->handle(Request::json());
            JsonResponse::send($result, 200);
        } catch (\RuntimeException $exception) {
            JsonResponse::send(['error' => $exception->getMessage()], 400);
        } catch (\Throwable $exception) {
            JsonResponse::send(['error' => $exception->getMessage()], 500);
        }
    }
}
