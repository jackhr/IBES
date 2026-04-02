<?php

declare(strict_types=1);

namespace App\Models;

final class VehicleDiscount
{
    public function __construct(
        public readonly int $id,
        public readonly int $vehicleId,
        public readonly float $priceXcd,
        public readonly float $priceUsd,
        public readonly int $days
    ) {
    }

    /** @param array<string, mixed> $row */
    public static function fromArray(array $row): self
    {
        return new self(
            (int) ($row['id'] ?? 0),
            (int) ($row['vehicle_id'] ?? 0),
            (float) ($row['price_XCD'] ?? 0),
            (float) ($row['price_USD'] ?? 0),
            (int) ($row['days'] ?? 0)
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'vehicleId' => $this->vehicleId,
            'priceXcd' => $this->priceXcd,
            'priceUsd' => $this->priceUsd,
            'days' => $this->days,
        ];
    }
}
