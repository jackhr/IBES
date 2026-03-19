<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Session;
use App\Repositories\RentalRepository;
use App\Support\EmailSender;
use App\Support\ReservationEmailBuilder;
use App\Support\ReservationMath;
use App\Support\Settings;
use RuntimeException;

final class VehicleRequestService
{
    public function __construct(private RentalRepository $rentalRepository)
    {
    }

    /** @param array<string, mixed> $data */
    public function submit(array $data): array
    {
        if (($data['h826r2whj4fi_cjz8jxs2zuwahhhk6'] ?? '') !== '') {
            return [
                'success' => false,
                'message' => 'error',
                'status' => 400,
                'data' => [],
            ];
        }

        $reservation = Session::getReservation() ?? [];
        $itinerary = $reservation['itinerary'] ?? null;
        $vehicle = $reservation['vehicle'] ?? null;
        $addOns = isset($reservation['add_ons']) && is_array($reservation['add_ons']) ? $reservation['add_ons'] : [];
        $discount = is_array($reservation['discount'] ?? null) ? $reservation['discount'] : null;

        if (!is_array($itinerary) || !is_array($vehicle)) {
            throw new RuntimeException('Reservation session is incomplete.');
        }

        $firstNameTrimmed = trim((string) ($data['first-name'] ?? ''));
        $lastNameTrimmed = trim((string) ($data['last-name'] ?? ''));
        $driverLicenseTrimmed = trim((string) ($data['driver-license'] ?? ''));
        $countryRegionTrimmed = trim((string) ($data['country-region'] ?? ''));
        $streetTrimmed = trim((string) ($data['street'] ?? ''));
        $townCityTrimmed = trim((string) ($data['town-city'] ?? ''));
        $stateCountyTrimmed = trim((string) ($data['state-county'] ?? ''));
        $phoneTrimmed = trim((string) ($data['phone'] ?? ''));
        $emailTrimmed = trim((string) ($data['email'] ?? ''));

        $hotel = trim((string) ($data['hotel'] ?? ''));
        $hotelForStorage = $hotel !== '' ? $hotel : null;

        $days = (int) ($itinerary['days'] ?? 0);
        $pricePerDay = (int) ($vehicle['base_price_USD'] ?? 0);

        if (is_array($discount) && isset($discount['price_USD'])) {
            $pricePerDay = (int) $discount['price_USD'];
        }

        $vehicleSubtotal = $pricePerDay * $days;
        $subtotal = $vehicleSubtotal + ReservationMath::getAddOnsSubTotal($addOns, $days, $vehicle);

        $timestamp = time();
        $pickUpTs = (int) (((int) ($itinerary['pickUpDate']['ts'] ?? 0)) / 1000);
        $dropOffTs = (int) (((int) ($itinerary['returnDate']['ts'] ?? 0)) / 1000);

        $pickUpLocation = (string) ($itinerary['pickUpLocation'] ?? '');
        $dropOffLocation = (string) ($itinerary['returnLocation'] ?? '');

        $contactInfoId = $this->rentalRepository->insertContactInfo([
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

        $orderRequestId = $this->rentalRepository->insertOrderRequest(
            $key,
            $pickUpTs,
            $dropOffTs,
            $pickUpLocation,
            $dropOffLocation,
            $contactInfoId,
            $subtotal,
            (int) ($vehicle['id'] ?? 0),
            $days
        );

        foreach ($addOns as $addOnId => $_addOn) {
            $this->rentalRepository->insertOrderRequestAddOn($orderRequestId, (int) $addOnId);
        }

        $this->rentalRepository->incrementVehicleTimesRequested((int) ($vehicle['id'] ?? 0));

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
        } while ($this->rentalRepository->keyExists($key));

        return $key;
    }
}
