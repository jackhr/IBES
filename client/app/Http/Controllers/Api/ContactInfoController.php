<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Services\ContactInfoService;
use App\Support\EndpointGuard;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Throwable;

final class ContactInfoController extends ApiController
{
    public function __construct(private ContactInfoService $contactInfoService)
    {
    }

    public function store(Request $request)
    {
        try {
            $payload = $request->all();
            $blocked = EndpointGuard::protect($payload, 'contact_info', false);

            if (is_array($blocked)) {
                return response()->json($blocked, (int) ($blocked['status'] ?? 400));
            }

            $contactInfo = $this->contactInfoService->create($payload);

            return $this->success([
                'contactInfo' => $contactInfo,
            ], 201);
        } catch (InvalidArgumentException $exception) {
            return $this->error($exception->getMessage(), 422);
        } catch (Throwable $exception) {
            return $this->error($exception->getMessage(), 500);
        }
    }

    public function show(int $id)
    {
        try {
            $contactInfo = $this->contactInfoService->find($id);

            if ($contactInfo === null) {
                return $this->error('Contact info not found.', 404);
            }

            return $this->success([
                'contactInfo' => $contactInfo,
            ]);
        } catch (Throwable $exception) {
            return $this->error($exception->getMessage(), 500);
        }
    }
}
