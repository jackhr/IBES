<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\OrderRequest;
use App\Repositories\BookingRepository;
use App\Support\ReservationMath;
use InvalidArgumentException;
use RuntimeException;

final class OrderRequestService
{
    public function __construct(private BookingRepository $bookingRepository)
    {
    }

    /** @param array<string, mixed> $data
     *  @return array<string, mixed>
     */
    public function create(array $data): array
    {
        $pickUpTimestamp = $this->resolveTimestamp($data['pickUpTimestamp'] ?? $data['pick_up_timestamp'] ?? null);
        $dropOffTimestamp = $this->resolveTimestamp($data['dropOffTimestamp'] ?? $data['drop_off_timestamp'] ?? null);

        $pickUpLocation = trim((string) ($data['pickUpLocation'] ?? $data['pick_up_location'] ?? ''));
        $dropOffLocation = trim((string) ($data['dropOffLocation'] ?? $data['drop_off_location'] ?? ''));
        $contactInfoId = (int) ($data['contactInfoId'] ?? $data['contact_info_id'] ?? 0);
        $subTotal = (float) ($data['subTotal'] ?? $data['sub_total'] ?? 0);
        $carId = (int) ($data['carId'] ?? $data['car_id'] ?? 0);
        $days = (int) ($data['days'] ?? 0);

        if ($pickUpTimestamp <= 0 || $dropOffTimestamp <= 0) {
            throw new InvalidArgumentException('Valid pick up and drop off timestamps are required.');
        }

        if ($pickUpLocation === '' || $dropOffLocation === '' || $contactInfoId <= 0 || $carId <= 0 || $days <= 0) {
            throw new InvalidArgumentException('Missing required order request fields.');
        }

        $key = trim((string) ($data['key'] ?? ''));

        if ($key === '') {
            $key = $this->generateUniqueOrderKey();
        }

        $orderRequestId = $this->bookingRepository->insertOrderRequest(
            $key,
            $pickUpTimestamp,
            $dropOffTimestamp,
            $pickUpLocation,
            $dropOffLocation,
            $contactInfoId,
            $subTotal,
            $carId,
            $days
        );

        $addOnIds = $this->normalizeAddOnIds($data['addOnIds'] ?? $data['add_on_ids'] ?? []);

        foreach ($addOnIds as $addOnId) {
            $this->bookingRepository->insertOrderRequestAddOn($orderRequestId, $addOnId);
        }

        $row = $this->bookingRepository->findOrderRequestById($orderRequestId);

        if (!is_array($row)) {
            throw new RuntimeException('Unable to load newly created order request.');
        }

        $orderRequest = OrderRequest::fromArray($row)->toArray();
        $orderRequest['addOnIds'] = $this->bookingRepository->findOrderRequestAddOnIds($orderRequestId);

        return $orderRequest;
    }

    /** @return array<string, mixed>|null */
    public function findByKey(string $key): ?array
    {
        $row = $this->bookingRepository->findOrderRequestByKey($key);

        if (!is_array($row)) {
            return null;
        }

        $orderRequest = OrderRequest::fromArray($row)->toArray();
        $orderRequest['addOnIds'] = $this->bookingRepository->findOrderRequestAddOnIds((int) ($row['id'] ?? 0));

        return $orderRequest;
    }

    private function generateUniqueOrderKey(): string
    {
        do {
            $key = ReservationMath::generateRandomKey();
        } while ($this->bookingRepository->keyExists($key));

        return $key;
    }

    private function resolveTimestamp(mixed $value): int
    {
        if (is_int($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        if (!is_string($value) || trim($value) === '') {
            return 0;
        }

        $timestamp = strtotime($value);

        return $timestamp === false ? 0 : $timestamp;
    }

    /** @return array<int, int> */
    private function normalizeAddOnIds(mixed $rawIds): array
    {
        if (!is_array($rawIds)) {
            return [];
        }

        $ids = [];

        foreach ($rawIds as $rawId) {
            $id = (int) $rawId;

            if ($id > 0) {
                $ids[] = $id;
            }
        }

        $ids = array_values(array_unique($ids));
        sort($ids);

        return $ids;
    }
}
