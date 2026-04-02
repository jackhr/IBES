<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;

class VehicleController extends Controller
{
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
        $this->syncVehicleImage($vehicle, $vehicle->slug, $request->file('image'));

        return response()->json([
            'success' => true,
            'message' => 'Vehicle created.',
            'data' => $this->toPayload($vehicle),
        ], 201);
    }

    public function update(Request $request, Vehicle $vehicle): JsonResponse
    {
        $originalSlug = $vehicle->slug;
        $payload = $this->validateVehicle($request, true);

        $vehicle->fill($payload);
        $vehicle->save();
        $this->syncVehicleImage($vehicle, $originalSlug, $request->file('image'));

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
            'image' => [$partial ? 'sometimes' : 'nullable', 'file', 'mimetypes:image/avif', 'max:10240'],
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
            'image_url' => $this->imageUrl($vehicle->slug),
        ];
    }

    private function syncVehicleImage(Vehicle $vehicle, string $originalSlug, ?UploadedFile $image): void
    {
        $galleryPath = $this->galleryPath();

        if (! File::exists($galleryPath)) {
            File::makeDirectory($galleryPath, 0755, true);
        }

        $targetPath = $this->imagePath($vehicle->slug);
        $originalPath = $this->imagePath($originalSlug);
        $slugChanged = $originalSlug !== $vehicle->slug;
        $originalSlugInUseByOthers = $slugChanged && Vehicle::query()
            ->where('slug', $originalSlug)
            ->where('id', '!=', $vehicle->id)
            ->exists();

        if ($image !== null) {
            $image->move($galleryPath, $this->imageFilename($vehicle->slug));

            if ($slugChanged && ! $originalSlugInUseByOthers && $originalPath !== $targetPath && File::exists($originalPath)) {
                File::delete($originalPath);
            }

            return;
        }

        if (! $slugChanged || ! File::exists($originalPath) || File::exists($targetPath)) {
            return;
        }

        if ($originalSlugInUseByOthers) {
            File::copy($originalPath, $targetPath);
            return;
        }

        File::move($originalPath, $targetPath);
    }

    private function imageUrl(string $slug): string
    {
        $prefix = rtrim((string) config('admin.vehicle_image_url_prefix', '/gallery/'), '/');

        return $prefix.'/'.$this->imageFilename($slug);
    }

    private function galleryPath(): string
    {
        return rtrim((string) config('admin.vehicle_gallery_path', public_path('gallery')), DIRECTORY_SEPARATOR);
    }

    private function imagePath(string $slug): string
    {
        return $this->galleryPath().DIRECTORY_SEPARATOR.$this->imageFilename($slug);
    }

    private function imageFilename(string $slug): string
    {
        return $slug.'.avif';
    }
}
