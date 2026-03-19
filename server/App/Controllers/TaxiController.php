<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\JsonResponse;
use App\Core\Request;
use App\Services\TaxiService;

final class TaxiController
{
    public function __construct(private TaxiService $taxiService)
    {
    }

    public function __invoke(): void
    {
        try {
            $result = $this->taxiService->submit(Request::json());
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
