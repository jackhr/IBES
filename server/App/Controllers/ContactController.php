<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\JsonResponse;
use App\Core\Request;
use App\Services\ContactService;
use App\Support\EndpointGuard;

final class ContactController
{
    public function __construct(private ContactService $contactService)
    {
    }

    public function __invoke(): void
    {
        try {
            $payload = Request::json();

            $blocked = EndpointGuard::protect($payload, 'contact', true);

            if (is_array($blocked)) {
                JsonResponse::send($blocked, (int) ($blocked['status'] ?? 400));

                return;
            }

            $result = $this->contactService->send($payload);
            JsonResponse::send($result, (int) ($result['status'] ?? 200));
        } catch (\InvalidArgumentException $exception) {
            JsonResponse::send([
                'success' => false,
                'message' => $exception->getMessage(),
                'status' => 422,
                'data' => [],
            ], 422);
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
