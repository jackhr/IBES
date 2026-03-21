<?php

declare(strict_types=1);

namespace App\Models;

final class TaxiRequest
{
    public function __construct(
        public readonly int $requestId,
        public readonly string $customerName,
        public readonly string $customerPhone,
        public readonly string $pickupLocation,
        public readonly string $dropoffLocation,
        public readonly string $pickupTime,
        public readonly int $numberOfPassengers,
        public readonly ?string $specialRequirements,
        public readonly ?string $createdAt
    ) {
    }

    /** @param array<string, mixed> $row */
    public static function fromArray(array $row): self
    {
        return new self(
            (int) ($row['request_id'] ?? 0),
            (string) ($row['customer_name'] ?? ''),
            (string) ($row['customer_phone'] ?? ''),
            (string) ($row['pickup_location'] ?? ''),
            (string) ($row['dropoff_location'] ?? ''),
            (string) ($row['pickup_time'] ?? ''),
            (int) ($row['number_of_passengers'] ?? 0),
            self::nullableString($row['special_requirements'] ?? null),
            self::nullableString($row['created_at'] ?? null)
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'requestId' => $this->requestId,
            'customerName' => $this->customerName,
            'customerPhone' => $this->customerPhone,
            'pickupLocation' => $this->pickupLocation,
            'dropoffLocation' => $this->dropoffLocation,
            'pickupTime' => $this->pickupTime,
            'numberOfPassengers' => $this->numberOfPassengers,
            'specialRequirements' => $this->specialRequirements,
            'createdAt' => $this->createdAt,
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
