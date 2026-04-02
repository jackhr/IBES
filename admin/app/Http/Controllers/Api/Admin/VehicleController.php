<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\ValidationException;

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

        return DB::transaction(function () use ($payload, $request): JsonResponse {
            $vehicle = Vehicle::query()->create($payload);
            $this->syncVehicleImage($vehicle, $vehicle->slug, null, $request->file('image'));

            return response()->json([
                'success' => true,
                'message' => 'Vehicle created.',
                'data' => $this->toPayload($vehicle),
            ], 201);
        });
    }

    public function update(Request $request, Vehicle $vehicle): JsonResponse
    {
        $originalSlug = $vehicle->slug;
        $originalImageFilename = $vehicle->image_filename;
        $payload = $this->validateVehicle($request, true);

        return DB::transaction(function () use ($request, $vehicle, $payload, $originalSlug, $originalImageFilename): JsonResponse {
            $vehicle->fill($payload);
            $vehicle->save();
            $this->syncVehicleImage($vehicle, $originalSlug, $originalImageFilename, $request->file('image'));

            return response()->json([
                'success' => true,
                'message' => 'Vehicle updated.',
                'data' => $this->toPayload($vehicle),
            ]);
        });
    }

    public function destroy(Vehicle $vehicle): JsonResponse
    {
        $vehicleId = $vehicle->id;
        $imageFilename = $vehicle->image_filename;

        try {
            $vehicle->delete();
        } catch (QueryException) {
            return response()->json([
                'success' => false,
                'message' => 'Vehicle cannot be deleted because related records exist.',
            ], 409);
        }

        $this->deleteCustomImageIfUnused($imageFilename, $vehicleId);

        return response()->json([
            'success' => true,
            'message' => 'Vehicle deleted.',
        ]);
    }

    private function validateVehicle(Request $request, bool $partial = false): array
    {
        $this->assertImageUploadSucceeded();

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
            'image' => [
                $partial ? 'sometimes' : 'nullable',
                'file',
                'max:10240',
            ],
        ]);

        if (array_key_exists('four_wd', $validated)) {
            $validated['4wd'] = $validated['four_wd'];
            unset($validated['four_wd']);
        }

        if ($request->hasFile('image') && $this->detectedImageExtension($request->file('image')) === null) {
            throw ValidationException::withMessages([
                'image' => ['Image format is unsupported or the file is corrupted.'],
            ]);
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
            'image_url' => $this->imageUrl($vehicle),
        ];
    }

    private function syncVehicleImage(
        Vehicle $vehicle,
        string $originalSlug,
        ?string $originalImageFilename,
        ?UploadedFile $image
    ): void {
        $this->ensureGalleryExists();

        if ($image !== null) {
            $filename = $this->storeUploadedImage($vehicle, $image);

            $vehicle->forceFill([
                'image_filename' => $filename,
            ])->saveQuietly();

            if ($originalImageFilename !== null && $originalImageFilename !== $filename) {
                $this->deleteCustomImageIfUnused($originalImageFilename, $vehicle->id);
            }

            return;
        }

        if ($vehicle->image_filename !== null || $originalSlug === $vehicle->slug) {
            return;
        }

        $legacyPath = $this->legacyImagePath($originalSlug);

        if (! File::exists($legacyPath)) {
            return;
        }

        $filename = $this->generateStoredImageFilename($vehicle->slug, 'avif');
        File::copy($legacyPath, $this->storedImagePath($filename));

        $vehicle->forceFill([
            'image_filename' => $filename,
        ])->saveQuietly();
    }

    private function imageUrl(Vehicle $vehicle): string
    {
        $prefix = rtrim((string) config('admin.vehicle_image_url_prefix', '/gallery/'), '/');

        return $prefix.'/'.$this->resolvedImageFilename($vehicle);
    }

    private function resolvedImageFilename(Vehicle $vehicle): string
    {
        $imageFilename = trim((string) ($vehicle->image_filename ?? ''));

        if ($imageFilename !== '') {
            return $imageFilename;
        }

        return $this->legacyImageFilename($vehicle->slug);
    }

    private function galleryPath(): string
    {
        return rtrim((string) config('admin.vehicle_gallery_path', public_path('gallery')), DIRECTORY_SEPARATOR);
    }

    private function ensureGalleryExists(): void
    {
        $galleryPath = $this->galleryPath();

        if (! File::exists($galleryPath)) {
            File::makeDirectory($galleryPath, 0755, true);
        }
    }

    private function storedImagePath(string $filename): string
    {
        return $this->galleryPath().DIRECTORY_SEPARATOR.$filename;
    }

    private function legacyImagePath(string $slug): string
    {
        return $this->storedImagePath($this->legacyImageFilename($slug));
    }

    private function legacyImageFilename(string $slug): string
    {
        return $slug.'.avif';
    }

    private function storeUploadedImage(Vehicle $vehicle, UploadedFile $image): string
    {
        $sourcePath = $image->getRealPath();
        $extension = $this->detectedImageExtension($image);

        if ($sourcePath === false || ! is_file($sourcePath) || $extension === null) {
            throw ValidationException::withMessages([
                'image' => ['Image format is unsupported or the file is corrupted.'],
            ]);
        }

        $filename = $this->generateStoredImageFilename($vehicle->slug, $extension);

        if (! File::copy($sourcePath, $this->storedImagePath($filename))) {
            throw ValidationException::withMessages([
                'image' => ['Image could not be saved.'],
            ]);
        }

        return $filename;
    }

    private function generateStoredImageFilename(string $slug, string $extension): string
    {
        $base = strtolower($slug);
        $base = (string) preg_replace('/[^a-z0-9_-]+/', '-', $base);
        $base = trim($base, '-_');

        if ($base === '') {
            $base = 'vehicle';
        }

        $normalizedExtension = strtolower($extension);

        for ($attempt = 0; $attempt < 10; $attempt++) {
            $filename = sprintf('%s-%s.%s', $base, bin2hex(random_bytes(6)), $normalizedExtension);

            if (
                ! File::exists($this->storedImagePath($filename))
                && ! Vehicle::query()->where('image_filename', $filename)->exists()
            ) {
                return $filename;
            }
        }

        throw ValidationException::withMessages([
            'image' => ['Image could not be saved. Please try again.'],
        ]);
    }

    private function deleteCustomImageIfUnused(?string $filename, int $vehicleId): void
    {
        $filename = trim((string) $filename);

        if ($filename === '') {
            return;
        }

        $stillInUse = Vehicle::query()
            ->where('image_filename', $filename)
            ->where('id', '!=', $vehicleId)
            ->exists();

        if ($stillInUse) {
            return;
        }

        $path = $this->storedImagePath($filename);

        if (File::exists($path)) {
            File::delete($path);
        }
    }

    private function detectedImageExtension(UploadedFile $image): ?string
    {
        $path = $image->getRealPath();
        $type = $path !== false ? @\exif_imagetype($path) : false;

        if ($type !== false) {
            return match ($type) {
                IMAGETYPE_AVIF => 'avif',
                IMAGETYPE_JPEG => 'jpg',
                IMAGETYPE_PNG => 'png',
                IMAGETYPE_WEBP => 'webp',
                IMAGETYPE_GIF => 'gif',
                IMAGETYPE_BMP => 'bmp',
                default => null,
            };
        }

        return match (strtolower($image->getClientOriginalExtension())) {
            'avif' => 'avif',
            'jpg', 'jpeg' => 'jpg',
            'png' => 'png',
            'webp' => 'webp',
            'gif' => 'gif',
            'bmp' => 'bmp',
            default => null,
        };
    }

    private function assertImageUploadSucceeded(): void
    {
        $error = $_FILES['image']['error'] ?? null;

        if ($error === null || (int) $error === UPLOAD_ERR_OK) {
            return;
        }

        throw ValidationException::withMessages([
            'image' => [$this->uploadErrorMessage((int) $error)],
        ]);
    }

    private function uploadErrorMessage(int $error): string
    {
        return match ($error) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => sprintf(
                'The uploaded image exceeds the server limit. Current PHP limits: upload_max_filesize=%s, post_max_size=%s.',
                ini_get('upload_max_filesize') ?: 'unknown',
                ini_get('post_max_size') ?: 'unknown'
            ),
            UPLOAD_ERR_PARTIAL => 'The image upload was only partially received. Please try again.',
            UPLOAD_ERR_NO_TMP_DIR => 'The server is missing a temporary upload directory.',
            UPLOAD_ERR_CANT_WRITE => 'The server could not write the uploaded image to disk.',
            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the image upload.',
            default => 'The image failed to upload before processing started.',
        };
    }
}
