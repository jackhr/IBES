<?php

declare(strict_types=1);

namespace App\Models;

final class Vehicle
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $type,
        public readonly string $slug,
        public readonly bool $showing,
        public readonly ?int $landingOrder,
        public readonly float $basePriceXcd,
        public readonly float $basePriceUsd,
        public readonly int $insurance,
        public readonly int $timesRequested,
        public readonly int $people,
        public readonly ?int $bags,
        public readonly int $doors,
        public readonly bool $fourWd,
        public readonly bool $ac,
        public readonly bool $manual,
        public readonly int $year,
        public readonly bool $taxi
    ) {
    }

    /** @param array<string, mixed> $row */
    public static function fromArray(array $row): self
    {
        return new self(
            (int) ($row['id'] ?? 0),
            (string) ($row['name'] ?? ''),
            (string) ($row['type'] ?? ''),
            (string) ($row['slug'] ?? ''),
            ((int) ($row['showing'] ?? 0)) === 1,
            self::nullableInt($row['landing_order'] ?? null),
            (float) ($row['base_price_XCD'] ?? 0),
            (float) ($row['base_price_USD'] ?? 0),
            (int) ($row['insurance'] ?? 0),
            (int) ($row['times_requested'] ?? 0),
            (int) ($row['people'] ?? 0),
            self::nullableInt($row['bags'] ?? null),
            (int) ($row['doors'] ?? 0),
            ((int) ($row['4wd'] ?? 0)) === 1,
            ((int) ($row['ac'] ?? 0)) === 1,
            ((int) ($row['manual'] ?? 0)) === 1,
            (int) ($row['year'] ?? 0),
            ((int) ($row['taxi'] ?? 0)) === 1
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'slug' => $this->slug,
            'showing' => $this->showing,
            'landingOrder' => $this->landingOrder,
            'basePriceXcd' => $this->basePriceXcd,
            'basePriceUsd' => $this->basePriceUsd,
            'insurance' => $this->insurance,
            'timesRequested' => $this->timesRequested,
            'people' => $this->people,
            'bags' => $this->bags,
            'doors' => $this->doors,
            'fourWd' => $this->fourWd,
            'ac' => $this->ac,
            'manual' => $this->manual,
            'year' => $this->year,
            'taxi' => $this->taxi,
            'imgSrc' => '/assets/images/vehicles/' . $this->slug . '.avif',
        ];
    }

    private static function nullableInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }
}
