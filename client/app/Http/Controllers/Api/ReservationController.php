<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Services\ReservationService;
use App\Support\EndpointGuard;
use Illuminate\Http\Request;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

final class ReservationController extends ApiController
{
    public function __construct(private ReservationService $reservationService)
    {
    }

    public function __invoke(Request $request)
    {
        try {
            $payload = $request->all();
            $blocked = EndpointGuard::protect($payload, 'reservation_api', false);

            if (is_array($blocked)) {
                return response()->json($blocked, (int) ($blocked['status'] ?? 400));
            }

            $result = $this->reservationService->handle($payload);

            return response()->json($result, 200);
        } catch (InvalidArgumentException $exception) {
            return $this->error($exception->getMessage(), 422);
        } catch (RuntimeException $exception) {
            return $this->error($exception->getMessage(), 400);
        } catch (Throwable $exception) {
            return $this->error($exception->getMessage(), 500);
        }
    }
}
