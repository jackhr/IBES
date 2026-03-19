<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Session;
use App\Repositories\RentalRepository;
use App\Support\ReservationMath;

final class ReservationService
{
    public function __construct(private RentalRepository $rentalRepository)
    {
    }

    /** @param array<string, mixed> $data */
    public function handle(array $data): array
    {
        if (isset($data['step'])) {
            $reservation = Session::getReservation() ?? [];
            $reservation['step'] = $data['step'];
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
            default => $data,
        };
    }

    /** @param array<string, mixed> $data */
    private function saveItinerary(array $data): array
    {
        unset($data['action']);

        $pickUpDate = (string) (($data['pickUpDate']['date'] ?? ''));
        $returnDate = (string) (($data['returnDate']['date'] ?? ''));
        $days = ReservationMath::getDifferenceInDays($pickUpDate, $returnDate);

        $reservation = Session::getReservation() ?? [];
        $reservation['itinerary'] = $data;
        $reservation['itinerary']['days'] = $days;

        if (isset($reservation['vehicle']['id'])) {
            $discount = $this->rentalRepository->findDiscountForVehicleDays((int) $reservation['vehicle']['id'], $days);
            $reservation['discount'] = $discount;
        }

        Session::setReservation($reservation);

        return $reservation;
    }

    /** @param array<string, mixed> $data */
    private function saveVehicle(array $data): array
    {
        $vehicleId = (int) ($data['id'] ?? 0);

        $vehicle = $this->rentalRepository->findVehicleById($vehicleId);

        if ($vehicle === null) {
            return Session::getReservation() ?? [];
        }

        $vehicle['imgSrc'] = '/assets/images/vehicles/' . ($vehicle['slug'] ?? '') . '.avif';

        $reservation = Session::getReservation() ?? [];
        $reservation['vehicle'] = $vehicle;

        if (isset($reservation['itinerary']['days'])) {
            $days = (int) $reservation['itinerary']['days'];
            $discount = $this->rentalRepository->findDiscountForVehicleDays((int) $vehicle['id'], $days);
            $reservation['discount'] = $discount;
        }

        Session::setReservation($reservation);

        return $reservation;
    }

    /** @param array<string, mixed> $data */
    private function addAddOn(array $data): array
    {
        $addOnId = (int) ($data['id'] ?? 0);
        $addOn = $this->rentalRepository->findAddOnById($addOnId);

        $reservation = Session::getReservation() ?? [];

        if ($addOn !== null) {
            if (!isset($reservation['add_ons']) || !is_array($reservation['add_ons'])) {
                $reservation['add_ons'] = [];
            }

            $reservation['add_ons'][$addOn['id']] = $addOn;

            uasort($reservation['add_ons'], static fn(array $a, array $b): int => ((int) $a['id']) <=> ((int) $b['id']));

            Session::setReservation($reservation);
        }

        return $reservation;
    }

    /** @param array<string, mixed> $data */
    private function removeAddOn(array $data): array
    {
        $addOnId = (int) ($data['id'] ?? 0);
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
}
