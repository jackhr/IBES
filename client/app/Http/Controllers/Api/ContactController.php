<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Services\ContactService;
use App\Support\EndpointGuard;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Throwable;

final class ContactController extends ApiController
{
    public function __construct(private ContactService $contactService)
    {
    }

    public function __invoke(Request $request)
    {
        try {
            $payload = $request->all();
            $blocked = EndpointGuard::protect($payload, 'contact', true);

            if (is_array($blocked)) {
                return response()->json($blocked, (int) ($blocked['status'] ?? 400));
            }

            $result = $this->contactService->send($payload);

            return response()->json($result, (int) ($result['status'] ?? 200));
        } catch (InvalidArgumentException $exception) {
            return $this->error($exception->getMessage(), 422);
        } catch (Throwable $exception) {
            return $this->error($exception->getMessage(), 500, [
                0 => $exception->getMessage(),
            ]);
        }
    }
}
