<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class RentalRepository
{
    public function __construct(private PDO $pdo)
    {
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
        string $passengers,
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
        int $subTotal,
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

    public function incrementVehicleTimesRequested(int $vehicleId): void
    {
        $statement = $this->pdo->prepare('UPDATE vehicles SET times_requested = times_requested + 1 WHERE id = :id');
        $statement->execute(['id' => $vehicleId]);
    }
}
