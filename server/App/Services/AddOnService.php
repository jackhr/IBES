<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AddOn;
use App\Repositories\RentalRepository;

final class AddOnService
{
    public function __construct(private RentalRepository $rentalRepository)
    {
    }

    /** @return array<int, array<string, mixed>> */
    public function list(): array
    {
        $rows = $this->rentalRepository->findAllAddOns();

        return array_map(
            static fn(array $row): array => AddOn::fromArray($row)->toArray(),
            $rows
        );
    }

    /** @return array<string, mixed>|null */
    public function find(int $id): ?array
    {
        $row = $this->rentalRepository->findAddOnById($id);

        if (!is_array($row)) {
            return null;
        }

        return AddOn::fromArray($row)->toArray();
    }
}
