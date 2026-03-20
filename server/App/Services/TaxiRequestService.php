<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\TaxiRequest;
use App\Repositories\TaxiRequestRepository;
use DateTime;
use InvalidArgumentException;
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
        $name = trim((string) ($data['name'] ?? $data['customerName'] ?? $data['customer_name'] ?? ''));
        $phone = trim((string) ($data['phone'] ?? $data['customerPhone'] ?? $data['customer_phone'] ?? ''));
        $pickUp = trim((string) ($data['pickUp'] ?? $data['pickupLocation'] ?? $data['pickup_location'] ?? ''));
        $dropOff = trim((string) ($data['dropOff'] ?? $data['dropoffLocation'] ?? $data['dropoff_location'] ?? ''));
        $passengers = (int) ($data['passengers'] ?? $data['numberOfPassengers'] ?? $data['number_of_passengers'] ?? 0);
        $message = trim((string) ($data['message'] ?? $data['specialRequirements'] ?? $data['special_requirements'] ?? ''));
        $pickUpTimeRaw = (string) ($data['pickUpTime'] ?? $data['pickupTime'] ?? $data['pickup_time'] ?? '');

        if ($name === '' || $phone === '' || $pickUp === '' || $dropOff === '' || $passengers <= 0 || trim($pickUpTimeRaw) === '') {
            throw new InvalidArgumentException('Missing required taxi request fields.');
        }

        $pickUpDate = new DateTime($pickUpTimeRaw);
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
