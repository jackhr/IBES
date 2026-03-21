<?php

declare(strict_types=1);

namespace App\Models;

final class ContactInfo
{
    public function __construct(
        public readonly int $id,
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly ?string $driverLicense,
        public readonly ?string $hotel,
        public readonly ?string $countryOrRegion,
        public readonly ?string $street,
        public readonly ?string $townOrCity,
        public readonly ?string $stateOrCounty,
        public readonly string $phone,
        public readonly string $email
    ) {
    }

    /** @param array<string, mixed> $row */
    public static function fromArray(array $row): self
    {
        return new self(
            (int) ($row['id'] ?? 0),
            (string) ($row['first_name'] ?? ''),
            (string) ($row['last_name'] ?? ''),
            self::nullableString($row['driver_license'] ?? null),
            self::nullableString($row['hotel'] ?? null),
            self::nullableString($row['country_or_region'] ?? null),
            self::nullableString($row['street'] ?? null),
            self::nullableString($row['town_or_city'] ?? null),
            self::nullableString($row['state_or_county'] ?? null),
            (string) ($row['phone'] ?? ''),
            (string) ($row['email'] ?? '')
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'driverLicense' => $this->driverLicense,
            'hotel' => $this->hotel,
            'countryOrRegion' => $this->countryOrRegion,
            'street' => $this->street,
            'townOrCity' => $this->townOrCity,
            'stateOrCounty' => $this->stateOrCounty,
            'phone' => $this->phone,
            'email' => $this->email,
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
