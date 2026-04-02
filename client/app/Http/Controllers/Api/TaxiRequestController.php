<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Services\TaxiRequestService;
use App\Support\EndpointGuard;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Throwable;

final class TaxiRequestController extends ApiController
{
    public function __construct(private TaxiRequestService $taxiRequestService)
    {
    }

    public function store(Request $request)
    {
        try {
            $payload = $request->all();
            $blocked = EndpointGuard::protect($payload, 'taxi_requests', true);

            if (is_array($blocked)) {
                return response()->json($blocked, (int) ($blocked['status'] ?? 400));
            }

            $taxiRequest = $this->taxiRequestService->create($payload);

            return $this->success([
                'taxiRequest' => $taxiRequest,
            ], 201);
        } catch (InvalidArgumentException $exception) {
            return $this->error($exception->getMessage(), 422);
        } catch (Throwable $exception) {
            return $this->error($exception->getMessage(), 500);
        }
    }

    public function show(int $requestId)
    {
        try {
            $taxiRequest = $this->taxiRequestService->find($requestId);

            if ($taxiRequest === null) {
                return $this->error('Taxi request not found.', 404);
            }

            return $this->success([
                'taxiRequest' => $taxiRequest,
            ]);
        } catch (Throwable $exception) {
            return $this->error($exception->getMessage(), 500);
        }
    }
}
