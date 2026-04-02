<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Vehicle;
use App\Repositories\VehicleRepository;

final class VehicleService
{
    public function __construct(private VehicleRepository $vehicleRepository)
    {
    }

    /** @return array<int, array<string, mixed>> */
    public function list(bool $showingOnly = false): array
    {
        $rows = $this->vehicleRepository->findAllVehicles($showingOnly);

        return array_map(
            static fn(array $row): array => Vehicle::fromArray($row)->toArray(),
            $rows
        );
    }

    /** @return array<int, array<string, mixed>> */
    public function landing(): array
    {
        $rows = $this->vehicleRepository->findLandingVehicles();
        $vehicles = [];

        foreach ($rows as $row) {
            $vehicle = Vehicle::fromArray($row)->toArray();
            $vehicle['discountDays'] = isset($row['discount_days']) ? (int) $row['discount_days'] : null;
            $vehicles[] = $vehicle;
        }

        return $vehicles;
    }

    /** @return array<string, mixed>|null */
    public function find(int $id): ?array
    {
        $row = $this->vehicleRepository->findVehicleById($id);

        if (!is_array($row)) {
            return null;
        }

        return Vehicle::fromArray($row)->toArray();
    }
}
