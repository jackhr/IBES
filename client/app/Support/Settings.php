<?php

declare(strict_types=1);

namespace App\Support;

final class Settings
{
    public static function companyName(): string
    {
        return (string) config('client.company_name', 'Ibes Car Rental');
    }

    public static function domain(): string
    {
        return (string) config('client.app_domain', 'ibescarrental.com');
    }

    public static function contactEmailString(): string
    {
        return (string) config('client.contact_email_string', self::emailString());
    }

    public static function emailString(): string
    {
        return (string) config('client.email_string', '');
    }

    public static function testingEmailString(): ?string
    {
        $value = config('client.testing_email_string');

        return is_string($value) && $value !== '' ? $value : null;
    }

    public static function debuggingEmailString(): ?string
    {
        $value = config('client.debugging_email_string');

        return is_string($value) && $value !== '' ? $value : null;
    }

    public static function destroySessionAfterOrdering(): bool
    {
        return (bool) config('client.destroy_session_after_ordering', true);
    }

    public static function visitorTrackingEnabled(): bool
    {
        return (bool) config('client.visitor_tracking_enabled', true);
    }
}
