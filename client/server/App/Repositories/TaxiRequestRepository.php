<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class TaxiRequestRepository
{
    public function __construct(private PDO $pdo)
    {
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
}
