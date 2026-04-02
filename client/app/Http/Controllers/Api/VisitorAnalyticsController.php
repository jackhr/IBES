<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Services\VisitorAnalyticsService;
use Illuminate\Http\Request;
use Throwable;

final class VisitorAnalyticsController extends ApiController
{
    public function __construct(private VisitorAnalyticsService $visitorAnalyticsService)
    {
    }

    public function store(Request $request)
    {
        try {
            $result = $this->visitorAnalyticsService->track($request->all());

            return $this->success($result, 202, 'accepted');
        } catch (Throwable $exception) {
            return $this->error($exception->getMessage(), 500);
        }
    }
}
