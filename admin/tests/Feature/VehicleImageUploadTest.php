<?php

namespace Tests\Feature;

use App\Models\AdminApiToken;
use App\Models\AdminUser;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Tests\TestCase;

class VehicleImageUploadTest extends TestCase
{
    use RefreshDatabase;

    private string $galleryPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->galleryPath = storage_path('framework/testing/vehicle-gallery');

        if (File::exists($this->galleryPath)) {
            File::deleteDirectory($this->galleryPath);
        }

        File::makeDirectory($this->galleryPath, 0755, true, true);
        config()->set('admin.vehicle_gallery_path', $this->galleryPath);
    }

    protected function tearDown(): void
    {
        if (File::exists($this->galleryPath)) {
            File::deleteDirectory($this->galleryPath);
        }

        parent::tearDown();
    }

    public function test_create_vehicle_preserves_uploaded_png_extension_and_uses_a_generated_filename(): void
    {
        $response = $this
            ->withHeaders($this->apiHeaders())
            ->post('/api/admin/vehicles', array_merge($this->vehiclePayload(), [
                'image' => $this->makeImageUpload('sunbird.png', 'png'),
            ]));

        $vehicle = Vehicle::query()->where('slug', 'sunbird')->firstOrFail();

        $response
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.slug', 'sunbird')
            ->assertJsonPath('data.image_url', '/gallery/'.$vehicle->image_filename);

        $this->assertMatchesRegularExpression('/^sunbird-[a-f0-9]{12}\.png$/', (string) $vehicle->image_filename);
        $this->assertFileExists($this->galleryPath.'/'.$vehicle->image_filename);
        $this->assertSame('image/png', (new \finfo(FILEINFO_MIME_TYPE))->file($this->galleryPath.'/'.$vehicle->image_filename));
    }

    public function test_create_vehicle_accepts_a_webp_upload_and_preserves_the_extension(): void
    {
        if (! function_exists('imagewebp')) {
            $this->markTestSkipped('WebP fixture generation requires imagewebp() in the local PHP build.');
        }

        $response = $this
            ->withHeaders($this->apiHeaders())
            ->post('/api/admin/vehicles', array_merge($this->vehiclePayload([
                'slug' => 'reef-runner',
            ]), [
                'image' => $this->makeImageUpload('reef-runner.webp', 'webp'),
            ]));

        $vehicle = Vehicle::query()->where('slug', 'reef-runner')->firstOrFail();

        $response
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.image_url', '/gallery/'.$vehicle->image_filename);

        $this->assertMatchesRegularExpression('/^reef-runner-[a-f0-9]{12}\.webp$/', (string) $vehicle->image_filename);
        $this->assertFileExists($this->galleryPath.'/'.$vehicle->image_filename);
        $this->assertSame('image/webp', (new \finfo(FILEINFO_MIME_TYPE))->file($this->galleryPath.'/'.$vehicle->image_filename));
    }

    public function test_create_vehicle_accepts_an_avif_upload_and_preserves_the_extension(): void
    {
        $upload = $this->makeImageUpload('shoreline.avif', 'avif');
        $uploadContents = (string) File::get($upload->getRealPath());

        $response = $this
            ->withHeaders($this->apiHeaders())
            ->post('/api/admin/vehicles', array_merge($this->vehiclePayload([
                'slug' => 'shoreline',
            ]), [
                'image' => $upload,
            ]));

        $vehicle = Vehicle::query()->where('slug', 'shoreline')->firstOrFail();

        $response
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.image_url', '/gallery/'.$vehicle->image_filename);

        $this->assertMatchesRegularExpression('/^shoreline-[a-f0-9]{12}\.avif$/', (string) $vehicle->image_filename);
        $this->assertFileExists($this->galleryPath.'/'.$vehicle->image_filename);
        $this->assertSame($uploadContents, (string) File::get($this->galleryPath.'/'.$vehicle->image_filename));
    }

    public function test_update_vehicle_can_replace_an_uploaded_image_and_delete_the_old_custom_file(): void
    {
        $vehicle = $this->createVehicle([
            'image_filename' => 'sunbird-old.png',
        ]);
        File::put($this->galleryPath.'/sunbird-old.png', 'old-image');

        $response = $this
            ->withHeaders($this->apiHeaders())
            ->post('/api/admin/vehicles/'.$vehicle->id, [
                '_method' => 'PUT',
                'image' => $this->makeImageUpload('replacement.jpg', 'jpeg'),
            ]);

        $vehicle->refresh();

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.image_url', '/gallery/'.$vehicle->image_filename);

        $this->assertMatchesRegularExpression('/^sunbird-[a-f0-9]{12}\.jpg$/', (string) $vehicle->image_filename);
        $this->assertFileDoesNotExist($this->galleryPath.'/sunbird-old.png');
        $this->assertFileExists($this->galleryPath.'/'.$vehicle->image_filename);
        $this->assertSame('image/jpeg', (new \finfo(FILEINFO_MIME_TYPE))->file($this->galleryPath.'/'.$vehicle->image_filename));
    }

    public function test_update_vehicle_with_a_new_slug_copies_the_legacy_slug_image_to_a_generated_filename(): void
    {
        $vehicle = $this->createVehicle([
            'slug' => 'shared-suv',
        ]);
        File::put($this->galleryPath.'/shared-suv.avif', 'shared-image');

        $response = $this
            ->withHeaders($this->apiHeaders())
            ->post('/api/admin/vehicles/'.$vehicle->id, [
                '_method' => 'PUT',
                'slug' => 'solo-suv',
            ]);

        $vehicle->refresh();

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.slug', 'solo-suv')
            ->assertJsonPath('data.image_url', '/gallery/'.$vehicle->image_filename);

        $this->assertMatchesRegularExpression('/^solo-suv-[a-f0-9]{12}\.avif$/', (string) $vehicle->image_filename);
        $this->assertFileExists($this->galleryPath.'/shared-suv.avif');
        $this->assertFileExists($this->galleryPath.'/'.$vehicle->image_filename);
        $this->assertSame('shared-image', (string) File::get($this->galleryPath.'/'.$vehicle->image_filename));
    }

    public function test_destroy_vehicle_deletes_an_unused_custom_image_file(): void
    {
        $vehicle = $this->createVehicle([
            'image_filename' => 'sunbird-custom.png',
        ]);
        File::put($this->galleryPath.'/sunbird-custom.png', 'custom-image');

        $response = $this
            ->withHeaders($this->apiHeaders())
            ->delete('/api/admin/vehicles/'.$vehicle->id);

        $response
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('vehicles', [
            'id' => $vehicle->id,
        ]);
        $this->assertFileDoesNotExist($this->galleryPath.'/sunbird-custom.png');
    }

    /** @return array<string, string> */
    private function apiHeaders(): array
    {
        $admin = AdminUser::query()->create([
            'username' => 'admin',
            'password_hash' => 'not-used-in-this-test',
            'role' => 'admin',
            'active' => true,
        ]);

        $plainToken = 'test-admin-token';

        AdminApiToken::query()->create([
            'admin_user_id' => $admin->id,
            'token_hash' => hash('sha256', $plainToken),
            'expires_at' => now()->addHour(),
        ]);

        return [
            'Authorization' => 'Bearer '.$plainToken,
            'Accept' => 'application/json',
        ];
    }

    /** @param array<string, mixed> $overrides */
    private function createVehicle(array $overrides = []): Vehicle
    {
        return Vehicle::query()->create($this->vehiclePayload($overrides));
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function vehiclePayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Sunbird',
            'type' => 'suv',
            'slug' => 'sunbird',
            'showing' => true,
            'landing_order' => 1,
            'base_price_XCD' => 200,
            'base_price_USD' => 75,
            'insurance' => 25,
            'times_requested' => 0,
            'people' => 5,
            'bags' => 3,
            'doors' => 4,
            '4wd' => false,
            'ac' => true,
            'manual' => false,
            'year' => 2024,
            'taxi' => false,
            'image_filename' => null,
        ], $overrides);
    }

    private function makeImageUpload(string $originalName, string $format): UploadedFile
    {
        if ($format === 'avif') {
            return $this->makeFixtureUpload($this->avifFixturePath(), $originalName);
        }

        $path = tempnam(sys_get_temp_dir(), 'vehicle-image-test-');

        if ($path === false) {
            throw new RuntimeException('Unable to create temporary file for image upload test.');
        }

        $image = imagecreatetruecolor(64, 48);
        $background = imagecolorallocate($image, 70, 120, 190);
        $foreground = imagecolorallocate($image, 255, 255, 255);

        imagefill($image, 0, 0, $background);
        imagefilledellipse($image, 32, 24, 28, 20, $foreground);

        $written = match ($format) {
            'png' => imagepng($image, $path),
            'jpeg' => imagejpeg($image, $path, 90),
            'webp' => imagewebp($image, $path, 90),
            default => false,
        };

        imagedestroy($image);

        if (! $written) {
            File::delete($path);

            throw new RuntimeException('Unable to generate image upload fixture.');
        }

        return new UploadedFile(
            $path,
            $originalName,
            (string) mime_content_type($path),
            null,
            true
        );
    }

    private function makeFixtureUpload(string $sourcePath, string $originalName): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'vehicle-image-fixture-');

        if ($path === false || ! File::copy($sourcePath, $path)) {
            throw new RuntimeException('Unable to copy image upload fixture.');
        }

        return new UploadedFile(
            $path,
            $originalName,
            (string) mime_content_type($path),
            null,
            true
        );
    }

    private function avifFixturePath(): string
    {
        $path = base_path('tests/Fixtures/vehicle-upload.avif');

        if (! File::exists($path)) {
            throw new RuntimeException('The AVIF upload fixture is missing.');
        }

        return $path;
    }
}
