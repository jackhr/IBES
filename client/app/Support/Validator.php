<?php

declare(strict_types=1);

namespace App\Support;

use DateTimeImmutable;
use InvalidArgumentException;

final class Validator
{
    /** @param array<string, mixed> $payload
     *  @param array<int, string> $keys
     */
    public static function requiredString(
        array $payload,
        array $keys,
        string $label,
        int $minLength = 1,
        int $maxLength = 255
    ): string {
        $value = self::stringValue($payload, $keys);

        if ($value === '') {
            throw new InvalidArgumentException("$label is required.");
        }

        $length = self::stringLength($value);

        if ($length < $minLength || $length > $maxLength) {
            throw new InvalidArgumentException("$label must be between $minLength and $maxLength characters.");
        }

        return $value;
    }

    /** @param array<string, mixed> $payload
     *  @param array<int, string> $keys
     */
    public static function optionalString(
        array $payload,
        array $keys,
        string $label,
        int $maxLength = 1000
    ): string {
        $value = self::stringValue($payload, $keys);

        if ($value !== '' && self::stringLength($value) > $maxLength) {
            throw new InvalidArgumentException("$label must not exceed $maxLength characters.");
        }

        return $value;
    }

    /** @param array<string, mixed> $payload
     *  @param array<int, string> $keys
     */
    public static function requiredEmail(array $payload, array $keys, string $label): string
    {
        $email = self::requiredString($payload, $keys, $label, 3, 254);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("$label is invalid.");
        }

        return $email;
    }

    /** @param array<string, mixed> $payload
     *  @param array<int, string> $keys
     */
    public static function requiredPhone(array $payload, array $keys, string $label): string
    {
        $phone = self::requiredString($payload, $keys, $label, 7, 30);

        if (!preg_match('/^[0-9+\s().-]+$/', $phone)) {
            throw new InvalidArgumentException("$label is invalid.");
        }

        return $phone;
    }

    /** @param array<string, mixed> $payload
     *  @param array<int, string> $keys
     */
    public static function requiredInt(
        array $payload,
        array $keys,
        string $label,
        int $minValue,
        int $maxValue
    ): int {
        $raw = self::firstPresent($payload, $keys);

        if ($raw === null || $raw === '') {
            throw new InvalidArgumentException("$label is required.");
        }

        if (!is_int($raw) && !is_numeric($raw)) {
            throw new InvalidArgumentException("$label must be a valid number.");
        }

        $value = (int) $raw;

        if ($value < $minValue || $value > $maxValue) {
            throw new InvalidArgumentException("$label must be between $minValue and $maxValue.");
        }

        return $value;
    }

    /** @param array<string, mixed> $payload
     *  @param array<int, string> $keys
     */
    public static function requiredDateTime(array $payload, array $keys, string $label): DateTimeImmutable
    {
        $raw = self::requiredString($payload, $keys, $label, 4, 80);

        try {
            return new DateTimeImmutable($raw);
        } catch (\Exception) {
            throw new InvalidArgumentException("$label is invalid.");
        }
    }

    /** @param array<string, mixed> $payload
     *  @param array<int, string> $keys
     */
    public static function requiredTimestamp(
        array $payload,
        array $keys,
        string $label,
        int $minValue = 1
    ): int {
        $raw = self::firstPresent($payload, $keys);

        if ($raw === null || $raw === '') {
            throw new InvalidArgumentException("$label is required.");
        }

        if (is_string($raw) && trim($raw) !== '' && !is_numeric($raw)) {
            $timestamp = strtotime($raw);

            if ($timestamp === false) {
                throw new InvalidArgumentException("$label is invalid.");
            }

            $raw = $timestamp;
        }

        if (!is_int($raw) && !is_numeric($raw)) {
            throw new InvalidArgumentException("$label is invalid.");
        }

        $value = (int) $raw;

        if ($value < $minValue) {
            throw new InvalidArgumentException("$label is invalid.");
        }

        return $value;
    }

    /** @param array<string, mixed> $payload
     *  @param array<int, string> $keys
     */
    private static function stringValue(array $payload, array $keys): string
    {
        $raw = self::firstPresent($payload, $keys);

        return trim((string) ($raw ?? ''));
    }

    /** @param array<string, mixed> $payload
     *  @param array<int, string> $keys
     */
    private static function firstPresent(array $payload, array $keys): mixed
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $payload)) {
                return $payload[$key];
            }
        }

        return null;
    }

    private static function stringLength(string $value): int
    {
        if (function_exists('mb_strlen')) {
            return mb_strlen($value);
        }

        return strlen($value);
    }
}
