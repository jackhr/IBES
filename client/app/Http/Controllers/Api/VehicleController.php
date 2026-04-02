<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Services\VehicleService;
use Illuminate\Http\Request;
use Throwable;

final class VehicleController extends ApiController
{
    public function __construct(private VehicleService $vehicleService)
    {
    }

    public function index(Request $request)
    {
        try {
            $showingOnly = false;

            if ($request->has('showing')) {
                $showingOnly = filter_var($request->query('showing'), FILTER_VALIDATE_BOOL) ?? false;
            }

            return $this->success([
                'vehicles' => $this->vehicleService->list($showingOnly),
            ]);
        } catch (Throwable $exception) {
            return $this->error($exception->getMessage(), 500);
        }
    }

    public function landing()
    {
        try {
            return $this->success([
                'vehicles' => $this->vehicleService->landing(),
            ]);
        } catch (Throwable $exception) {
            return $this->error($exception->getMessage(), 500);
        }
    }

    public function show(int $id)
    {
        try {
            $vehicle = $this->vehicleService->find($id);

            if ($vehicle === null) {
                return $this->error('Vehicle not found.', 404);
            }

            return $this->success([
                'vehicle' => $vehicle,
            ]);
        } catch (Throwable $exception) {
            return $this->error($exception->getMessage(), 500);
        }
    }
}
