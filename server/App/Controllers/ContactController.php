<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\JsonResponse;
use App\Core\Request;
use App\Services\ContactService;

final class ContactController
{
    public function __construct(private ContactService $contactService)
    {
    }

    public function __invoke(): void
    {
        try {
            $result = $this->contactService->send(Request::json());
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
