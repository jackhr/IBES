<?php

declare(strict_types=1);

namespace App\Models;

final class OrderRequest
{
    public function __construct(
        public readonly int $id,
        public readonly string $key,
        public readonly string $pickUp,
        public readonly string $dropOff,
        public readonly string $pickUpLocation,
        public readonly string $dropOffLocation,
        public readonly bool $confirmed,
        public readonly int $contactInfoId,
        public readonly float $subTotal,
        public readonly int $carId,
        public readonly int $days,
        public readonly ?string $createdAt,
        public readonly ?string $updatedAt
    ) {
    }

    /** @param array<string, mixed> $row */
    public static function fromArray(array $row): self
    {
        return new self(
            (int) ($row['id'] ?? 0),
            (string) ($row['key'] ?? ''),
            (string) ($row['pick_up'] ?? ''),
            (string) ($row['drop_off'] ?? ''),
            (string) ($row['pick_up_location'] ?? ''),
            (string) ($row['drop_off_location'] ?? ''),
            ((int) ($row['confirmed'] ?? 0)) === 1,
            (int) ($row['contact_info_id'] ?? 0),
            (float) ($row['sub_total'] ?? 0),
            (int) ($row['car_id'] ?? 0),
            (int) ($row['days'] ?? 0),
            self::nullableString($row['created_at'] ?? null),
            self::nullableString($row['updated_at'] ?? null)
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'key' => $this->key,
            'pickUp' => $this->pickUp,
            'dropOff' => $this->dropOff,
            'pickUpLocation' => $this->pickUpLocation,
            'dropOffLocation' => $this->dropOffLocation,
            'confirmed' => $this->confirmed,
            'contactInfoId' => $this->contactInfoId,
            'subTotal' => $this->subTotal,
            'carId' => $this->carId,
            'days' => $this->days,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
        ];
    }

    private static function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $string = trim((string) $value);

        return $string === '' ? null : $string;
    }
}
