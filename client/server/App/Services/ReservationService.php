<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Session;
use App\Repositories\AddOnRepository;
use App\Repositories\VehicleRepository;
use App\Support\ReservationMath;
use InvalidArgumentException;

final class ReservationService
{
    public function __construct(
        private VehicleRepository $vehicleRepository,
        private AddOnRepository $addOnRepository
    ) {
    }

    /** @param array<string, mixed> $data */
    public function handle(array $data): array
    {
        if (isset($data['step'])) {
            $step = trim((string) $data['step']);

            if ($step === '' || self::stringLength($step) > 50) {
                throw new InvalidArgumentException('Invalid reservation step value.');
            }

            $reservation = Session::getReservation() ?? [];
            $reservation['step'] = $step;
            Session::setReservation($reservation);
            unset($data['step']);
        }

        $action = (string) ($data['action'] ?? '');

        return match ($action) {
            'itinerary' => $this->saveItinerary($data),
            'vehicle' => $this->saveVehicle($data),
            'add_add_on' => $this->addAddOn($data),
            'remove_add_on' => $this->removeAddOn($data),
            'get_reservation' => Session::getReservation() ?? [],
            'reset_reservation' => $this->resetReservation(),
            default => throw new InvalidArgumentException('Unsupported reservation action.'),
        };
    }

    /** @param array<string, mixed> $data */
    private function saveItinerary(array $data): array
    {
        unset($data['action']);

        $pickUpDate = trim((string) (($data['pickUpDate']['date'] ?? '')));
        $returnDate = trim((string) (($data['returnDate']['date'] ?? '')));
        $pickUpTs = (int) (($data['pickUpDate']['ts'] ?? 0));
        $returnTs = (int) (($data['returnDate']['ts'] ?? 0));
        $pickUpLocation = trim((string) ($data['pickUpLocation'] ?? ''));
        $returnLocation = trim((string) ($data['returnLocation'] ?? ''));

        if ($pickUpDate === '' || $returnDate === '' || $pickUpTs <= 0 || $returnTs <= 0) {
            throw new InvalidArgumentException('Reservation dates are required.');
        }

        if ($returnTs <= $pickUpTs) {
            throw new InvalidArgumentException('Return date must be after pick up date.');
        }

        if ($pickUpLocation === '' || $returnLocation === '') {
            throw new InvalidArgumentException('Pick up and return locations are required.');
        }

        $days = ReservationMath::getDifferenceInDays($pickUpDate, $returnDate);

        if ($days <= 0 || $days > 365) {
            throw new InvalidArgumentException('Reservation duration is invalid.');
        }

        $reservation = Session::getReservation() ?? [];
        $reservation['itinerary'] = $data;
        $reservation['itinerary']['days'] = $days;

        if (isset($reservation['vehicle']['id'])) {
            $discount = $this->vehicleRepository->findDiscountForVehicleDays((int) $reservation['vehicle']['id'], $days);
            $reservation['discount'] = $discount;
        }

        Session::setReservation($reservation);

        return $reservation;
    }

    /** @param array<string, mixed> $data */
    private function saveVehicle(array $data): array
    {
        $vehicleId = (int) ($data['id'] ?? 0);

        if ($vehicleId <= 0) {
            throw new InvalidArgumentException('Vehicle ID is required.');
        }

        $vehicle = $this->vehicleRepository->findVehicleById($vehicleId);

        if ($vehicle === null) {
            throw new InvalidArgumentException('Vehicle not found.');
        }

        $vehicle['imgSrc'] = '/assets/images/vehicles/' . ($vehicle['slug'] ?? '') . '.avif';

        $reservation = Session::getReservation() ?? [];
        $reservation['vehicle'] = $vehicle;

        if (isset($reservation['itinerary']['days'])) {
            $days = (int) $reservation['itinerary']['days'];
            $discount = $this->vehicleRepository->findDiscountForVehicleDays((int) $vehicle['id'], $days);
            $reservation['discount'] = $discount;
        }

        Session::setReservation($reservation);

        return $reservation;
    }

    /** @param array<string, mixed> $data */
    private function addAddOn(array $data): array
    {
        $addOnId = (int) ($data['id'] ?? 0);

        if ($addOnId <= 0) {
            throw new InvalidArgumentException('Add-on ID is required.');
        }

        $addOn = $this->addOnRepository->findAddOnById($addOnId);

        $reservation = Session::getReservation() ?? [];

        if ($addOn === null) {
            throw new InvalidArgumentException('Add-on not found.');
        }

        if (!isset($reservation['add_ons']) || !is_array($reservation['add_ons'])) {
            $reservation['add_ons'] = [];
        }

        $reservation['add_ons'][$addOn['id']] = $addOn;

        uasort($reservation['add_ons'], static fn(array $a, array $b): int => ((int) $a['id']) <=> ((int) $b['id']));

        Session::setReservation($reservation);

        return $reservation;
    }

    /** @param array<string, mixed> $data */
    private function removeAddOn(array $data): array
    {
        $addOnId = (int) ($data['id'] ?? 0);

        if ($addOnId <= 0) {
            throw new InvalidArgumentException('Add-on ID is required.');
        }

        $reservation = Session::getReservation() ?? [];

        if (isset($reservation['add_ons'][$addOnId])) {
            unset($reservation['add_ons'][$addOnId]);
            Session::setReservation($reservation);
        }

        return $reservation;
    }

    /** @return array<string, mixed> */
    private function resetReservation(): array
    {
        Session::clearReservation();

        return [];
    }

    private static function stringLength(string $value): int
    {
        if (function_exists('mb_strlen')) {
            return mb_strlen($value);
        }

        return strlen($value);
    }
}
