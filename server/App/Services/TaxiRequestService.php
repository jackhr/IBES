<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\TaxiRequest;
use App\Repositories\TaxiRequestRepository;
use App\Support\Validator;
use RuntimeException;

final class TaxiRequestService
{
    public function __construct(private TaxiRequestRepository $taxiRequestRepository)
    {
    }

    /** @param array<string, mixed> $data
     *  @return array<string, mixed>
     */
    public function create(array $data): array
    {
        $name = Validator::requiredString($data, ['name', 'customerName', 'customer_name'], 'Name', 2, 120);
        $phone = Validator::requiredPhone($data, ['phone', 'customerPhone', 'customer_phone'], 'Phone');
        $pickUp = Validator::requiredString($data, ['pickUp', 'pickupLocation', 'pickup_location'], 'Pick up location', 2, 200);
        $dropOff = Validator::requiredString($data, ['dropOff', 'dropoffLocation', 'dropoff_location'], 'Drop off location', 2, 200);
        $passengers = Validator::requiredInt($data, ['passengers', 'numberOfPassengers', 'number_of_passengers'], 'Passengers', 1, 30);
        $message = Validator::optionalString($data, ['message', 'specialRequirements', 'special_requirements'], 'Special requirements', 1500);

        $pickUpDate = Validator::requiredDateTime($data, ['pickUpTime', 'pickupTime', 'pickup_time'], 'Pick up time');
        $pickUpDateTime = $pickUpDate->format('Y-m-d H:i:s');

        $requestId = $this->taxiRequestRepository->insertTaxiRequest(
            $name,
            $phone,
            $pickUp,
            $dropOff,
            $pickUpDateTime,
            $passengers,
            $message
        );

        $row = $this->taxiRequestRepository->findTaxiRequestById($requestId);

        if (!is_array($row)) {
            throw new RuntimeException('Unable to load newly created taxi request.');
        }

        return TaxiRequest::fromArray($row)->toArray();
    }

    /** @return array<string, mixed>|null */
    public function find(int $requestId): ?array
    {
        $row = $this->taxiRequestRepository->findTaxiRequestById($requestId);

        if (!is_array($row)) {
            return null;
        }

        return TaxiRequest::fromArray($row)->toArray();
    }
}
