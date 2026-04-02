<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\OrderRequest;
use App\Repositories\BookingRepository;
use App\Support\ReservationMath;
use App\Support\Validator;
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
        $pickUpTimestamp = Validator::requiredTimestamp($data, ['pickUpTimestamp', 'pick_up_timestamp'], 'Pick up timestamp');
        $dropOffTimestamp = Validator::requiredTimestamp($data, ['dropOffTimestamp', 'drop_off_timestamp'], 'Drop off timestamp');

        if ($dropOffTimestamp <= $pickUpTimestamp) {
            throw new InvalidArgumentException('Drop off timestamp must be after pick up timestamp.');
        }

        $pickUpLocation = Validator::requiredString($data, ['pickUpLocation', 'pick_up_location'], 'Pick up location', 2, 200);
        $dropOffLocation = Validator::requiredString($data, ['dropOffLocation', 'drop_off_location'], 'Drop off location', 2, 200);
        $contactInfoId = Validator::requiredInt($data, ['contactInfoId', 'contact_info_id'], 'Contact info ID', 1, PHP_INT_MAX);
        $carId = Validator::requiredInt($data, ['carId', 'car_id'], 'Vehicle ID', 1, PHP_INT_MAX);
        $days = Validator::requiredInt($data, ['days'], 'Days', 1, 365);

        $subTotalRaw = $data['subTotal'] ?? $data['sub_total'] ?? null;

        if ($subTotalRaw === null || $subTotalRaw === '' || !is_numeric($subTotalRaw)) {
            throw new InvalidArgumentException('Subtotal is required.');
        }

        $subTotal = (float) $subTotalRaw;

        if ($subTotal < 0 || $subTotal > 1000000) {
            throw new InvalidArgumentException('Subtotal is out of range.');
        }

        $key = trim((string) ($data['key'] ?? ''));

        if ($key === '') {
            $key = $this->generateUniqueOrderKey();
        } elseif (!preg_match('/^[A-Za-z0-9_-]{6,64}$/', $key)) {
            throw new InvalidArgumentException('Order key format is invalid.');
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
