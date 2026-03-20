<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\VehicleDiscount;
use App\Repositories\RentalRepository;

final class VehicleDiscountService
{
    public function __construct(private RentalRepository $rentalRepository)
    {
    }

    /** @return array<int, array<string, mixed>> */
    public function list(?int $vehicleId = null): array
    {
        $rows = $this->rentalRepository->findVehicleDiscounts($vehicleId);

        return array_map(
            static fn(array $row): array => VehicleDiscount::fromArray($row)->toArray(),
            $rows
        );
    }

    /** @return array<string, mixed>|null */
    public function findBestForDays(int $vehicleId, int $days): ?array
    {
        $row = $this->rentalRepository->findDiscountForVehicleDays($vehicleId, $days);

        if (!is_array($row)) {
            return null;
        }

        return VehicleDiscount::fromArray($row)->toArray();
    }
}
