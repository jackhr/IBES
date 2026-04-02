<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ContactInfo;
use App\Repositories\BookingRepository;
use App\Support\Validator;
use RuntimeException;

final class ContactInfoService
{
    public function __construct(private BookingRepository $bookingRepository)
    {
    }

    /** @param array<string, mixed> $data
     *  @return array<string, mixed>
     */
    public function create(array $data): array
    {
        $firstName = Validator::requiredString($data, ['firstName', 'first_name'], 'First name', 1, 100);
        $lastName = Validator::requiredString($data, ['lastName', 'last_name'], 'Last name', 1, 100);
        $phone = Validator::requiredPhone($data, ['phone'], 'Phone');
        $email = Validator::requiredEmail($data, ['email'], 'Email');

        $driverLicense = Validator::optionalString($data, ['driverLicense', 'driver_license'], 'Driver license', 100);
        $hotel = Validator::optionalString($data, ['hotel'], 'Hotel', 180);
        $countryOrRegion = Validator::optionalString($data, ['countryOrRegion', 'country_or_region'], 'Country or region', 120);
        $street = Validator::optionalString($data, ['street'], 'Street', 180);
        $townOrCity = Validator::optionalString($data, ['townOrCity', 'town_or_city'], 'Town or city', 120);
        $stateOrCounty = Validator::optionalString($data, ['stateOrCounty', 'state_or_county'], 'State or county', 120);

        $id = $this->bookingRepository->insertContactInfo([
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

        $row = $this->bookingRepository->findContactInfoById($id);

        if (!is_array($row)) {
            throw new RuntimeException('Unable to load newly created contact info.');
        }

        return ContactInfo::fromArray($row)->toArray();
    }

    /** @return array<string, mixed>|null */
    public function find(int $id): ?array
    {
        $row = $this->bookingRepository->findContactInfoById($id);

        if (!is_array($row)) {
            return null;
        }

        return ContactInfo::fromArray($row)->toArray();
    }
}
