<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\JsonResponse;
use App\Core\Request;
use App\Services\VisitorAnalyticsService;

final class VisitorAnalyticsController
{
    public function __construct(private VisitorAnalyticsService $visitorAnalyticsService)
    {
    }

    public function store(): void
    {
        try {
            $payload = Request::json();
            $result = $this->visitorAnalyticsService->track($payload);

            JsonResponse::send([
                'success' => true,
                'message' => 'accepted',
                'status' => 202,
                'data' => $result,
            ], 202);
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

