<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class BookingRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    /** @param array<string, mixed> $contactInfo */
    public function insertContactInfo(array $contactInfo): int
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO contact_info
            (first_name, last_name, driver_license, hotel, country_or_region, street, town_or_city, state_or_county, phone, email)
            VALUES
            (:first_name, :last_name, :driver_license, :hotel, :country_or_region, :street, :town_or_city, :state_or_county, :phone, :email)'
        );

        $statement->bindValue('first_name', (string) $contactInfo['first_name']);
        $statement->bindValue('last_name', (string) $contactInfo['last_name']);
        $statement->bindValue('driver_license', (string) $contactInfo['driver_license']);

        if (($contactInfo['hotel'] ?? null) === null || (string) $contactInfo['hotel'] === '') {
            $statement->bindValue('hotel', null, PDO::PARAM_NULL);
        } else {
            $statement->bindValue('hotel', (string) $contactInfo['hotel']);
        }

        $statement->bindValue('country_or_region', (string) $contactInfo['country_or_region']);
        $statement->bindValue('street', (string) $contactInfo['street']);
        $statement->bindValue('town_or_city', (string) $contactInfo['town_or_city']);
        $statement->bindValue('state_or_county', (string) $contactInfo['state_or_county']);
        $statement->bindValue('phone', (string) $contactInfo['phone']);
        $statement->bindValue('email', (string) $contactInfo['email']);

        $statement->execute();

        return (int) $this->pdo->lastInsertId();
    }

    /** @return array<string, mixed>|null */
    public function findContactInfoById(int $id): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM contact_info WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $id]);
        $contactInfo = $statement->fetch();

        return is_array($contactInfo) ? $contactInfo : null;
    }

    public function keyExists(string $key): bool
    {
        $statement = $this->pdo->prepare('SELECT 1 FROM order_requests WHERE `key` = :key LIMIT 1');
        $statement->execute(['key' => $key]);

        return $statement->fetchColumn() !== false;
    }

    public function insertOrderRequest(
        string $key,
        int $pickUpTimestamp,
        int $dropOffTimestamp,
        string $pickUpLocation,
        string $dropOffLocation,
        int $contactInfoId,
        float $subTotal,
        int $carId,
        int $days
    ): int {
        $statement = $this->pdo->prepare(
            'INSERT INTO order_requests
            (`key`, pick_up, drop_off, pick_up_location, drop_off_location, confirmed, contact_info_id, sub_total, car_id, days)
            VALUES (:key, FROM_UNIXTIME(:pick_up), FROM_UNIXTIME(:drop_off), :pick_up_location, :drop_off_location, 0, :contact_info_id, :sub_total, :car_id, :days)'
        );

        $statement->execute([
            'key' => $key,
            'pick_up' => $pickUpTimestamp,
            'drop_off' => $dropOffTimestamp,
            'pick_up_location' => $pickUpLocation,
            'drop_off_location' => $dropOffLocation,
            'contact_info_id' => $contactInfoId,
            'sub_total' => $subTotal,
            'car_id' => $carId,
            'days' => $days,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    /** @return array<string, mixed>|null */
    public function findOrderRequestById(int $id): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM order_requests WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $id]);
        $orderRequest = $statement->fetch();

        return is_array($orderRequest) ? $orderRequest : null;
    }

    /** @return array<string, mixed>|null */
    public function findOrderRequestByKey(string $key): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM order_requests WHERE `key` = :key ORDER BY id DESC LIMIT 1');
        $statement->execute(['key' => $key]);
        $orderRequest = $statement->fetch();

        return is_array($orderRequest) ? $orderRequest : null;
    }

    public function insertOrderRequestAddOn(int $orderRequestId, int $addOnId): void
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO order_request_add_ons (order_request_id, add_on_id) VALUES (:order_request_id, :add_on_id)'
        );

        $statement->execute([
            'order_request_id' => $orderRequestId,
            'add_on_id' => $addOnId,
        ]);
    }

    /** @return array<int, int> */
    public function findOrderRequestAddOnIds(int $orderRequestId): array
    {
        $statement = $this->pdo->prepare(
            'SELECT add_on_id FROM order_request_add_ons WHERE order_request_id = :order_request_id ORDER BY add_on_id ASC'
        );
        $statement->execute([
            'order_request_id' => $orderRequestId,
        ]);

        $rows = $statement->fetchAll(PDO::FETCH_COLUMN);

        if (!is_array($rows)) {
            return [];
        }

        return array_map(static fn(mixed $value): int => (int) $value, $rows);
    }
}
