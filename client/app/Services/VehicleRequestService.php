<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Session;
use App\Repositories\BookingRepository;
use App\Repositories\VehicleRepository;
use App\Support\EmailSender;
use App\Support\ReservationEmailBuilder;
use App\Support\ReservationMath;
use App\Support\Settings;
use App\Support\Validator;
use RuntimeException;

final class VehicleRequestService
{
    public function __construct(
        private BookingRepository $bookingRepository,
        private VehicleRepository $vehicleRepository
    ) {
    }

    /** @param array<string, mixed> $data */
    public function submit(array $data): array
    {
        $reservation = Session::getReservation() ?? [];
        $itinerary = $reservation['itinerary'] ?? null;
        $vehicle = $reservation['vehicle'] ?? null;
        $addOns = isset($reservation['add_ons']) && is_array($reservation['add_ons']) ? $reservation['add_ons'] : [];
        $discount = is_array($reservation['discount'] ?? null) ? $reservation['discount'] : null;

        if (!is_array($itinerary) || !is_array($vehicle)) {
            throw new RuntimeException('Reservation session is incomplete.');
        }

        $firstNameTrimmed = Validator::requiredString($data, ['first-name', 'first_name'], 'First name', 1, 100);
        $lastNameTrimmed = Validator::requiredString($data, ['last-name', 'last_name'], 'Last name', 1, 100);
        $driverLicenseTrimmed = Validator::optionalString($data, ['driver-license', 'driver_license'], 'Driver license', 100);
        $countryRegionTrimmed = Validator::requiredString($data, ['country-region', 'country_region'], 'Country or region', 2, 120);
        $streetTrimmed = Validator::requiredString($data, ['street'], 'Street', 2, 180);
        $townCityTrimmed = Validator::requiredString($data, ['town-city', 'town_city'], 'Town or city', 2, 120);
        $stateCountyTrimmed = Validator::requiredString($data, ['state-county', 'state_county'], 'State or county', 2, 120);
        $phoneTrimmed = Validator::requiredPhone($data, ['phone'], 'Phone');
        $emailTrimmed = Validator::requiredEmail($data, ['email'], 'Email');

        $hotel = Validator::optionalString($data, ['hotel'], 'Hotel', 180);
        $hotelForStorage = $hotel !== '' ? $hotel : null;

        $days = (int) ($itinerary['days'] ?? 0);

        if ($days <= 0 || $days > 365) {
            throw new RuntimeException('Reservation duration is invalid.');
        }

        $pickUpLocation = trim((string) ($itinerary['pickUpLocation'] ?? ''));
        $dropOffLocation = trim((string) ($itinerary['returnLocation'] ?? ''));

        if ($pickUpLocation === '' || $dropOffLocation === '') {
            throw new RuntimeException('Reservation locations are incomplete.');
        }

        $pickUpTsMs = (int) ($itinerary['pickUpDate']['ts'] ?? 0);
        $dropOffTsMs = (int) ($itinerary['returnDate']['ts'] ?? 0);

        if ($pickUpTsMs <= 0 || $dropOffTsMs <= 0) {
            throw new RuntimeException('Reservation dates are incomplete.');
        }

        $pickUpTs = (int) ($pickUpTsMs / 1000);
        $dropOffTs = (int) ($dropOffTsMs / 1000);

        if ($dropOffTs <= $pickUpTs) {
            throw new RuntimeException('Drop off date must be after pick up date.');
        }

        $vehicleId = (int) ($vehicle['id'] ?? 0);

        if ($vehicleId <= 0) {
            throw new RuntimeException('Reservation vehicle is invalid.');
        }

        $pricePerDay = (int) ($vehicle['base_price_USD'] ?? 0);

        if (is_array($discount) && isset($discount['price_USD'])) {
            $pricePerDay = (int) $discount['price_USD'];
        }

        $vehicleSubtotal = $pricePerDay * $days;
        $subtotal = $vehicleSubtotal + ReservationMath::getAddOnsSubTotal($addOns, $days, $vehicle);

        $timestamp = time();

        $contactInfoId = $this->bookingRepository->insertContactInfo([
            'first_name' => $firstNameTrimmed,
            'last_name' => $lastNameTrimmed,
            'driver_license' => $driverLicenseTrimmed,
            'hotel' => $hotelForStorage,
            'country_or_region' => $countryRegionTrimmed,
            'street' => $streetTrimmed,
            'town_or_city' => $townCityTrimmed,
            'state_or_county' => $stateCountyTrimmed,
            'phone' => $phoneTrimmed,
            'email' => $emailTrimmed,
        ]);

        $key = $this->generateUniqueOrderKey();

        $orderRequestId = $this->bookingRepository->insertOrderRequest(
            $key,
            $pickUpTs,
            $dropOffTs,
            $pickUpLocation,
            $dropOffLocation,
            $contactInfoId,
            $subtotal,
            $vehicleId,
            $days
        );

        foreach ($addOns as $addOnId => $_addOn) {
            $this->bookingRepository->insertOrderRequestAddOn($orderRequestId, (int) $addOnId);
        }

        $this->vehicleRepository->incrementVehicleTimesRequested($vehicleId);

        $clientEmailBody = ReservationEmailBuilder::build(
            $hotelForStorage,
            $firstNameTrimmed,
            $lastNameTrimmed,
            $countryRegionTrimmed,
            $streetTrimmed,
            $townCityTrimmed,
            $stateCountyTrimmed,
            $phoneTrimmed,
            $emailTrimmed,
            $orderRequestId,
            $vehicle,
            $addOns,
            $itinerary,
            $days,
            $subtotal,
            $timestamp,
            $key,
            $vehicleSubtotal,
            false
        );

        $adminEmailBody = ReservationEmailBuilder::build(
            $hotelForStorage,
            $firstNameTrimmed,
            $lastNameTrimmed,
            $countryRegionTrimmed,
            $streetTrimmed,
            $townCityTrimmed,
            $stateCountyTrimmed,
            $phoneTrimmed,
            $emailTrimmed,
            $orderRequestId,
            $vehicle,
            $addOns,
            $itinerary,
            $days,
            $subtotal,
            $timestamp,
            $key,
            $vehicleSubtotal,
            true
        );

        $subject = 'Car Rental Request at Ibes Car Rental';
        $from = 'bookings@ibescarrental.com';

        $mailResClient = EmailSender::sendHtml($emailTrimmed, $subject, $clientEmailBody, $from);

        $debugging = Settings::debuggingEmailString() !== null;

        if ($debugging) {
            $adminEmailStr = (string) Settings::debuggingEmailString();
        } elseif (Settings::testingEmailString() !== null) {
            $adminEmailStr = (string) Settings::testingEmailString();
        } else {
            $adminEmailStr = Settings::emailString();
        }

        $mailResAdmin = EmailSender::sendHtml($adminEmailStr, $subject, $adminEmailBody, $from, $emailTrimmed);

        if (Settings::destroySessionAfterOrdering() && $debugging) {
            Session::destroy();
        }

        Session::clearReservation();

        $response = [
            'success' => true,
            'message' => 'success',
            'status' => 200,
            'data' => [
                'mail' => [
                    'mail_res_client' => $mailResClient,
                    'mail_res_admin' => $mailResAdmin,
                ],
                'key' => $key,
                'debugging' => $debugging,
            ],
        ];

        if ($debugging) {
            $response['data']['admin_email_body'] = $adminEmailBody;
            $response['data']['client_email_body'] = $clientEmailBody;
        }

        return $response;
    }

    private function generateUniqueOrderKey(): string
    {
        do {
            $key = ReservationMath::generateRandomKey();
        } while ($this->bookingRepository->keyExists($key));

        return $key;
    }
}
