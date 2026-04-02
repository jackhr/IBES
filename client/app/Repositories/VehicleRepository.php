<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class VehicleRepository
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

    public function incrementVehicleTimesRequested(int $vehicleId): void
    {
        $statement = $this->pdo->prepare('UPDATE vehicles SET times_requested = times_requested + 1 WHERE id = :id');
        $statement->execute(['id' => $vehicleId]);
    }
}
