<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    private const VEHICLE_IMAGE_PREFIX = '/gallery/';

    public function index(): JsonResponse
    {
        $vehicles = Vehicle::query()
            ->orderByRaw('COALESCE(landing_order, 999999) ASC')
            ->orderBy('id')
            ->get()
            ->map(fn (Vehicle $vehicle): array => $this->toPayload($vehicle))
            ->all();

        return response()->json([
            'success' => true,
            'data' => $vehicles,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $payload = $this->validateVehicle($request);

        $vehicle = Vehicle::query()->create($payload);

        return response()->json([
            'success' => true,
            'message' => 'Vehicle created.',
            'data' => $this->toPayload($vehicle),
        ], 201);
    }

    public function update(Request $request, Vehicle $vehicle): JsonResponse
    {
        $payload = $this->validateVehicle($request, true);

        $vehicle->fill($payload);
        $vehicle->save();

        return response()->json([
            'success' => true,
            'message' => 'Vehicle updated.',
            'data' => $this->toPayload($vehicle),
        ]);
    }

    public function destroy(Vehicle $vehicle): JsonResponse
    {
        try {
            $vehicle->delete();
        } catch (QueryException) {
            return response()->json([
                'success' => false,
                'message' => 'Vehicle cannot be deleted because related records exist.',
            ], 409);
        }

        return response()->json([
            'success' => true,
            'message' => 'Vehicle deleted.',
        ]);
    }

    private function validateVehicle(Request $request, bool $partial = false): array
    {
        $required = $partial ? 'sometimes' : 'required';
        $optional = $partial ? 'sometimes' : 'nullable';

        $validated = $request->validate([
            'name' => [$required, 'string', 'min:2', 'max:60'],
            'type' => [$required, 'string', 'min:2', 'max:30'],
            'slug' => [$required, 'string', 'min:2', 'max:99', 'regex:/^[a-z0-9][a-z0-9_-]*$/'],
            'showing' => [$optional, 'boolean'],
            'landing_order' => [$optional, 'nullable', 'integer', 'min:1', 'max:999999'],
            'base_price_XCD' => [$required, 'numeric', 'min:0'],
            'base_price_USD' => [$required, 'numeric', 'min:0'],
            'insurance' => [$required, 'integer', 'min:0', 'max:100000'],
            'times_requested' => [$optional, 'integer', 'min:0'],
            'people' => [$required, 'integer', 'min:1', 'max:30'],
            'bags' => [$optional, 'nullable', 'integer', 'min:0', 'max:30'],
            'doors' => [$required, 'integer', 'min:1', 'max:10'],
            'four_wd' => [$optional, 'boolean'],
            'ac' => [$optional, 'boolean'],
            'manual' => [$optional, 'boolean'],
            'year' => [$required, 'integer', 'min:1990', 'max:2100'],
            'taxi' => [$optional, 'boolean'],
        ]);

        if (array_key_exists('four_wd', $validated)) {
            $validated['4wd'] = $validated['four_wd'];
            unset($validated['four_wd']);
        }

        return $validated;
    }

    private function toPayload(Vehicle $vehicle): array
    {
        return [
            'id' => $vehicle->id,
            'name' => $vehicle->name,
            'type' => $vehicle->type,
            'slug' => $vehicle->slug,
            'showing' => (bool) $vehicle->showing,
            'landing_order' => $vehicle->landing_order,
            'base_price_XCD' => (float) $vehicle->base_price_XCD,
            'base_price_USD' => (float) $vehicle->base_price_USD,
            'insurance' => (int) $vehicle->insurance,
            'times_requested' => (int) $vehicle->times_requested,
            'people' => (int) $vehicle->people,
            'bags' => $vehicle->bags !== null ? (int) $vehicle->bags : null,
            'doors' => (int) $vehicle->doors,
            'four_wd' => (bool) $vehicle->getAttribute('4wd'),
            'ac' => (bool) $vehicle->ac,
            'manual' => (bool) $vehicle->manual,
            'year' => (int) $vehicle->year,
            'taxi' => (bool) $vehicle->taxi,
            'image_url' => self::VEHICLE_IMAGE_PREFIX.$vehicle->slug.'.avif',
        ];
    }
}
