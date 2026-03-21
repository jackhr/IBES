<?php

declare(strict_types=1);

namespace App\Support;

final class Settings
{
    public static function companyName(): string
    {
        return (string) \Config::get('COMPANY_NAME', 'Ibes Car Rental');
    }

    public static function domain(): string
    {
        return (string) \Config::get('APP_DOMAIN', 'ibescarrental.com');
    }

    public static function contactEmailString(): string
    {
        return (string) \Config::get('CONTACT_EMAIL_STRING', self::emailString());
    }

    public static function emailString(): string
    {
        return (string) \Config::get('EMAIL_STRING', '');
    }

    public static function testingEmailString(): ?string
    {
        $value = \Config::get('TESTING_EMAIL_STRING');

        return is_string($value) && $value !== '' ? $value : null;
    }

    public static function debuggingEmailString(): ?string
    {
        $value = \Config::get('DEBUGGING_EMAIL_STRING');

        return is_string($value) && $value !== '' ? $value : null;
    }

    public static function destroySessionAfterOrdering(): bool
    {
        return \Config::bool('DESTROY_SESSION_AFTER_ORDERING', true);
    }
}
