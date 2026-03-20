<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\JsonResponse;
use App\Services\AddOnService;

final class AddOnController
{
    public function __construct(private AddOnService $addOnService)
    {
    }

    public function index(): void
    {
        try {
            JsonResponse::send([
                'success' => true,
                'message' => 'success',
                'status' => 200,
                'data' => [
                    'addOns' => $this->addOnService->list(),
                ],
            ], 200);
        } catch (\Throwable $exception) {
            $this->sendServerError($exception);
        }
    }

    public function show(int $id): void
    {
        try {
            $addOn = $this->addOnService->find($id);

            if ($addOn === null) {
                JsonResponse::send([
                    'success' => false,
                    'message' => 'Add-on not found.',
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
                    'addOn' => $addOn,
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
