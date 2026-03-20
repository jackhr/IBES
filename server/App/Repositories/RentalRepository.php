<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class RentalRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    /** @return array<int, array<string, mixed>> */
    public function findLandingVehicles(): array
    {
        $statement = $this->pdo->query(
            'SELECT
                v.*,
                vd.max_days AS discount_days
            FROM vehicles v
            LEFT JOIN (
                SELECT vehicle_id, MAX(`days`) AS max_days
                FROM vehicle_discounts
                GROUP BY vehicle_id
            ) vd ON vd.vehicle_id = v.id
            WHERE v.showing = 1
                AND v.landing_order IS NOT NULL
            ORDER BY v.landing_order ASC'
        );

        $vehicles = $statement->fetchAll();

        return is_array($vehicles) ? $vehicles : [];
    }

    /** @return array<int, array<string, mixed>> */
    public function findAllVehicles(bool $showingOnly = false): array
    {
        if ($showingOnly) {
            $statement = $this->pdo->query('SELECT * FROM vehicles WHERE showing = 1 ORDER BY COALESCE(landing_order, 999999), id ASC');
        } else {
            $statement = $this->pdo->query('SELECT * FROM vehicles ORDER BY COALESCE(landing_order, 999999), id ASC');
        }

        $vehicles = $statement->fetchAll();

        return is_array($vehicles) ? $vehicles : [];
    }

    /** @return array<string, mixed>|null */
    public function findVehicleById(int $id): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM vehicles WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $id]);
        $vehicle = $statement->fetch();

        return is_array($vehicle) ? $vehicle : null;
    }

    /** @return array<string, mixed>|null */
    public function findDiscountForVehicleDays(int $vehicleId, int $days): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT * FROM vehicle_discounts WHERE vehicle_id = :vehicle_id AND `days` <= :days ORDER BY `days` DESC LIMIT 1'
        );
        $statement->execute([
            'vehicle_id' => $vehicleId,
            'days' => $days,
        ]);

        $discount = $statement->fetch();

        return is_array($discount) ? $discount : null;
    }

    /** @return array<int, array<string, mixed>> */
    public function findVehicleDiscounts(?int $vehicleId = null): array
    {
        if ($vehicleId === null) {
            $statement = $this->pdo->query('SELECT * FROM vehicle_discounts ORDER BY vehicle_id ASC, `days` ASC');
            $discounts = $statement->fetchAll();

            return is_array($discounts) ? $discounts : [];
        }

        $statement = $this->pdo->prepare(
            'SELECT * FROM vehicle_discounts WHERE vehicle_id = :vehicle_id ORDER BY `days` ASC'
        );
        $statement->execute([
            'vehicle_id' => $vehicleId,
        ]);

        $discounts = $statement->fetchAll();

        return is_array($discounts) ? $discounts : [];
    }

    /** @return array<int, array<string, mixed>> */
    public function findAllAddOns(): array
    {
        $statement = $this->pdo->query('SELECT * FROM add_ons ORDER BY id ASC');
        $addOns = $statement->fetchAll();

        return is_array($addOns) ? $addOns : [];
    }

    /** @return array<string, mixed>|null */
    public function findAddOnById(int $id): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM add_ons WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $id]);
        $addOn = $statement->fetch();

        return is_array($addOn) ? $addOn : null;
    }

    public function insertTaxiRequest(
        string $name,
        string $phone,
        string $pickUp,
        string $dropOff,
        string $pickUpDateTime,
        int $passengers,
        string $message
    ): int {
        $statement = $this->pdo->prepare(
            'INSERT INTO taxi_requests (customer_name, customer_phone, pickup_location, dropoff_location, pickup_time, number_of_passengers, special_requirements, created_at)
            VALUES (:customer_name, :customer_phone, :pickup_location, :dropoff_location, :pickup_time, :number_of_passengers, :special_requirements, CURRENT_TIMESTAMP)'
        );

        $statement->execute([
            'customer_name' => $name,
            'customer_phone' => $phone,
            'pickup_location' => $pickUp,
            'dropoff_location' => $dropOff,
            'pickup_time' => $pickUpDateTime,
            'number_of_passengers' => $passengers,
            'special_requirements' => $message,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    /** @return array<string, mixed>|null */
    public function findTaxiRequestById(int $requestId): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM taxi_requests WHERE request_id = :request_id LIMIT 1');
        $statement->execute(['request_id' => $requestId]);
        $request = $statement->fetch();

        return is_array($request) ? $request : null;
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

    public function incrementVehicleTimesRequested(int $vehicleId): void
    {
        $statement = $this->pdo->prepare('UPDATE vehicles SET times_requested = times_requested + 1 WHERE id = :id');
        $statement->execute(['id' => $vehicleId]);
    }
}
