<?php

declare(strict_types=1);

namespace App\Models;

final class AddOn
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly ?float $cost,
        public readonly string $description,
        public readonly string $abbr,
        public readonly bool $fixedPrice
    ) {
    }

    /** @param array<string, mixed> $row */
    public static function fromArray(array $row): self
    {
        $costRaw = $row['cost'] ?? null;
        $cost = null;

        if ($costRaw !== null && $costRaw !== '') {
            $cost = (float) $costRaw;
        }

        return new self(
            (int) ($row['id'] ?? 0),
            (string) ($row['name'] ?? ''),
            $cost,
            (string) ($row['description'] ?? ''),
            (string) ($row['abbr'] ?? ''),
            ((int) ($row['fixed_price'] ?? 0)) === 1
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'cost' => $this->cost,
            'description' => $this->description,
            'abbr' => $this->abbr,
            'fixedPrice' => $this->fixedPrice,
        ];
    }
}
