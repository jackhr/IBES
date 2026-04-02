<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Services\VehicleRequestService;
use App\Support\EndpointGuard;
use Illuminate\Http\Request;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

final class VehicleRequestController extends ApiController
{
    public function __construct(private VehicleRequestService $vehicleRequestService)
    {
    }

    public function __invoke(Request $request)
    {
        try {
            $payload = $request->all();
            $blocked = EndpointGuard::protect($payload, 'reservation', true);

            if (is_array($blocked)) {
                return response()->json($blocked, (int) ($blocked['status'] ?? 400));
            }

            $result = $this->vehicleRequestService->submit($payload);

            return response()->json($result, (int) ($result['status'] ?? 200));
        } catch (InvalidArgumentException $exception) {
            return $this->error($exception->getMessage(), 422);
        } catch (RuntimeException $exception) {
            return $this->error($exception->getMessage(), 400);
        } catch (Throwable $exception) {
            return $this->error($exception->getMessage(), 500, [
                0 => $exception->getMessage(),
            ]);
        }
    }
}
