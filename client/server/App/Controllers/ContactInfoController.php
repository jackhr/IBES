<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\JsonResponse;
use App\Core\Request;
use App\Services\ContactInfoService;
use App\Support\EndpointGuard;
use InvalidArgumentException;

final class ContactInfoController
{
    public function __construct(private ContactInfoService $contactInfoService)
    {
    }

    public function store(): void
    {
        try {
            $payload = Request::json();

            $blocked = EndpointGuard::protect($payload, 'contact_info', false);

            if (is_array($blocked)) {
                JsonResponse::send($blocked, (int) ($blocked['status'] ?? 400));

                return;
            }

            $contactInfo = $this->contactInfoService->create($payload);

            JsonResponse::send([
                'success' => true,
                'message' => 'success',
                'status' => 201,
                'data' => [
                    'contactInfo' => $contactInfo,
                ],
            ], 201);
        } catch (InvalidArgumentException $exception) {
            JsonResponse::send([
                'success' => false,
                'message' => $exception->getMessage(),
                'status' => 422,
                'data' => [],
            ], 422);
        } catch (\Throwable $exception) {
            $this->sendServerError($exception);
        }
    }

    public function show(int $id): void
    {
        try {
            $contactInfo = $this->contactInfoService->find($id);

            if ($contactInfo === null) {
                JsonResponse::send([
                    'success' => false,
                    'message' => 'Contact info not found.',
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
                    'contactInfo' => $contactInfo,
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
