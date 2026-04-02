<?php

namespace Tests\Feature;

use App\Models\AdminApiToken;
use App\Models\AdminUser;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
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

    public function test_create_vehicle_can_store_an_uploaded_image(): void
    {
        $response = $this
            ->withHeaders($this->authHeaders())
            ->post('/api/admin/vehicles', array_merge($this->vehiclePayload(), [
                'image' => UploadedFile::fake()->create('sunbird.avif', 128, 'image/avif'),
            ]));

        $response
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.slug', 'sunbird')
            ->assertJsonPath('data.image_url', '/gallery/sunbird.avif');

        $this->assertDatabaseHas('vehicles', [
            'slug' => 'sunbird',
        ]);
        $this->assertFileExists($this->galleryPath.'/sunbird.avif');
    }

    public function test_update_vehicle_can_replace_an_uploaded_image(): void
    {
        $vehicle = $this->createVehicle();
        File::put($this->galleryPath.'/sunbird.avif', 'old-image');

        $response = $this
            ->withHeaders($this->authHeaders())
            ->post('/api/admin/vehicles/'.$vehicle->id, [
                '_method' => 'PUT',
                'image' => UploadedFile::fake()->create('replacement.avif', 256, 'image/avif'),
            ]);

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.image_url', '/gallery/sunbird.avif');

        $this->assertFileExists($this->galleryPath.'/sunbird.avif');
        $this->assertNotSame('old-image', (string) File::get($this->galleryPath.'/sunbird.avif'));
    }

    public function test_update_vehicle_with_a_new_slug_keeps_the_old_image_when_other_vehicles_still_use_it(): void
    {
        $vehicle = $this->createVehicle([
            'slug' => 'shared-suv',
        ]);
        $this->createVehicle([
            'name' => 'Shared SUV Two',
            'slug' => 'shared-suv',
        ]);
        File::put($this->galleryPath.'/shared-suv.avif', 'shared-image');

        $response = $this
            ->withHeaders($this->authHeaders())
            ->post('/api/admin/vehicles/'.$vehicle->id, [
                '_method' => 'PUT',
                'slug' => 'solo-suv',
            ]);

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.slug', 'solo-suv')
            ->assertJsonPath('data.image_url', '/gallery/solo-suv.avif');

        $this->assertFileExists($this->galleryPath.'/shared-suv.avif');
        $this->assertFileExists($this->galleryPath.'/solo-suv.avif');
        $this->assertSame('shared-image', (string) File::get($this->galleryPath.'/solo-suv.avif'));
    }

    /** @return array<string, string> */
    private function authHeaders(): array
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
        ];
    }

    /** @param array<string, mixed> $overrides */
    private function createVehicle(array $overrides = []): Vehicle
    {
        return Vehicle::query()->create($this->vehiclePayload($overrides));
    }

    /** @param array<string, mixed> $overrides
     *  @return array<string, mixed>
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
        ], $overrides);
    }
}
