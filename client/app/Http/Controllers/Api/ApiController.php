<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

abstract class ApiController extends Controller
{
    /** @param array<string, mixed> $data */
    protected function success(array $data = [], int $status = 200, string $message = 'success'): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'status' => $status,
            'data' => $data,
        ], $status);
    }

    /** @param array<string, mixed> $data */
    protected function error(string $message, int $status, array $data = []): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'status' => $status,
            'data' => $data,
        ], $status);
    }
}
