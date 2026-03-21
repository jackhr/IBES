<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class AddOnRepository
{
    public function __construct(private PDO $pdo)
    {
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
}
