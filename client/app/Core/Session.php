<?php

declare(strict_types=1);

namespace App\Core;

final class Session
{
    public static function start(): void
    {
        // Laravel boots the session for web requests.
    }

    /** @return array<string, mixed>|null */
    public static function getReservation(): ?array
    {
        $value = session('reservation');

        return is_array($value) ? $value : null;
    }

    /** @param array<string, mixed> $value */
    public static function setReservation(array $value): void
    {
        session(['reservation' => $value]);
    }

    public static function clearReservation(): void
    {
        session()->forget('reservation');
    }

    public static function destroy(): void
    {
        session()->invalidate();
        session()->regenerateToken();
    }
}
