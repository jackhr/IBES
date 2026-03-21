<?php

declare(strict_types=1);

namespace App\Core;

final class Session
{
    public static function start(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    /** @return array<string, mixed>|null */
    public static function getReservation(): ?array
    {
        self::start();

        $value = $_SESSION['reservation'] ?? null;

        return is_array($value) ? $value : null;
    }

    /** @param array<string, mixed> $value */
    public static function setReservation(array $value): void
    {
        self::start();
        $_SESSION['reservation'] = $value;
    }

    public static function clearReservation(): void
    {
        self::start();
        unset($_SESSION['reservation']);
    }

    public static function destroy(): void
    {
        self::start();
        session_destroy();
    }
}
