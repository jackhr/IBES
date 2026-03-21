<?php

declare(strict_types=1);

namespace App\Support;

use DateTime;

final class ReservationMath
{
    public static function getDifferenceInDays(string $pickUpDate, string $returnDate): int
    {
        $start = new DateTime($pickUpDate);
        $end = new DateTime($returnDate);

        $start->setTime(0, 0);
        $end->setTime(0, 0);

        return $start->diff($end)->days;
    }

    /** @param array<string, mixed> $addOn */
    public static function getAddOnCostForTotalDays(array $addOn, int $days = 1, ?array $vehicle = null): int
    {
        $newCost = (int) ($addOn['cost'] ?? 0);

        if (($addOn['name'] ?? '') === 'Collision Insurance') {
            if (is_array($vehicle) && isset($vehicle['insurance'])) {
                return (int) $vehicle['insurance'] * $days;
            }

            return 0;
        }

        if (($addOn['name'] ?? '') === 'Child Seat (If Available)') {
            if (is_array($vehicle)) {
                return $newCost * $days;
            }

            return 0;
        }

        return $newCost;
    }

    /** @param array<int|string, array<string, mixed>>|null $addOns */
    public static function getAddOnsSubTotal(?array $addOns, int $days, ?array $vehicle = null): int
    {
        if (!is_array($addOns)) {
            return 0;
        }

        $subtotal = 0;

        foreach ($addOns as $addOn) {
            if (!is_array($addOn)) {
                continue;
            }

            $subtotal += self::getAddOnCostForTotalDays($addOn, $days, $vehicle);
        }

        return $subtotal;
    }

    public static function generateRandomKey(int $length = 24): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $max = strlen($characters) - 1;
        $key = '';

        for ($i = 0; $i < $length; $i++) {
            $key .= $characters[random_int(0, $max)];
        }

        return $key;
    }
}
