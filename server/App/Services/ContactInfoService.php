<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ContactInfo;
use App\Repositories\RentalRepository;
use InvalidArgumentException;
use RuntimeException;

final class ContactInfoService
{
    public function __construct(private RentalRepository $rentalRepository)
    {
    }

    /** @param array<string, mixed> $data
     *  @return array<string, mixed>
     */
    public function create(array $data): array
    {
        $firstName = trim((string) ($data['firstName'] ?? $data['first_name'] ?? ''));
        $lastName = trim((string) ($data['lastName'] ?? $data['last_name'] ?? ''));
        $phone = trim((string) ($data['phone'] ?? ''));
        $email = trim((string) ($data['email'] ?? ''));

        if ($firstName === '' || $lastName === '' || $phone === '' || $email === '') {
            throw new InvalidArgumentException('firstName, lastName, phone and email are required.');
        }

        $driverLicense = trim((string) ($data['driverLicense'] ?? $data['driver_license'] ?? ''));
        $hotel = trim((string) ($data['hotel'] ?? ''));
        $countryOrRegion = trim((string) ($data['countryOrRegion'] ?? $data['country_or_region'] ?? ''));
        $street = trim((string) ($data['street'] ?? ''));
        $townOrCity = trim((string) ($data['townOrCity'] ?? $data['town_or_city'] ?? ''));
        $stateOrCounty = trim((string) ($data['stateOrCounty'] ?? $data['state_or_county'] ?? ''));

        $id = $this->rentalRepository->insertContactInfo([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'driver_license' => $driverLicense,
            'hotel' => $hotel !== '' ? $hotel : null,
            'country_or_region' => $countryOrRegion,
            'street' => $street,
            'town_or_city' => $townOrCity,
            'state_or_county' => $stateOrCounty,
            'phone' => $phone,
            'email' => $email,
        ]);

        $row = $this->rentalRepository->findContactInfoById($id);

        if (!is_array($row)) {
            throw new RuntimeException('Unable to load newly created contact info.');
        }

        return ContactInfo::fromArray($row)->toArray();
    }

    /** @return array<string, mixed>|null */
    public function find(int $id): ?array
    {
        $row = $this->rentalRepository->findContactInfoById($id);

        if (!is_array($row)) {
            return null;
        }

        return ContactInfo::fromArray($row)->toArray();
    }
}
