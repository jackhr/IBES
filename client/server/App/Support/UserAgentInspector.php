<?php

declare(strict_types=1);

namespace App\Support;

final class UserAgentInspector
{
    /** @return array{deviceType: string, isBot: bool, osName: ?string, browserName: ?string} */
    public static function inspect(string $userAgent): array
    {
        $normalized = trim($userAgent);

        if ($normalized === '') {
            return [
                'deviceType' => 'other',
                'isBot' => false,
                'osName' => null,
                'browserName' => null,
            ];
        }

        $isBot = preg_match('/bot|crawl|spider|slurp|bingpreview|headless|phantom|crawler|facebookexternalhit|preview/i', $normalized) === 1;

        return [
            'deviceType' => self::detectDeviceType($normalized, $isBot),
            'isBot' => $isBot,
            'osName' => self::detectOsName($normalized),
            'browserName' => self::detectBrowserName($normalized),
        ];
    }

    private static function detectDeviceType(string $userAgent, bool $isBot): string
    {
        if ($isBot) {
            return 'bot';
        }

        if (preg_match('/ipad|tablet|playbook|silk|kindle|nexus 7|nexus 9|nexus 10|sm-t/i', $userAgent) === 1) {
            return 'tablet';
        }

        if (preg_match('/mobi|iphone|ipod|android.+mobile|windows phone|blackberry|phone/i', $userAgent) === 1) {
            return 'mobile';
        }

        if (preg_match('/macintosh|windows nt|linux x86_64|x11/i', $userAgent) === 1) {
            return 'desktop';
        }

        return 'other';
    }

    private static function detectOsName(string $userAgent): ?string
    {
        $map = [
            '/windows nt/i' => 'Windows',
            '/iphone|ipad|ipod/i' => 'iOS',
            '/android/i' => 'Android',
            '/mac os x|macintosh/i' => 'macOS',
            '/linux/i' => 'Linux',
            '/cros/i' => 'Chrome OS',
        ];

        foreach ($map as $pattern => $name) {
            if (preg_match($pattern, $userAgent) === 1) {
                return $name;
            }
        }

        return null;
    }

    private static function detectBrowserName(string $userAgent): ?string
    {
        $map = [
            '/edg\//i' => 'Edge',
            '/opr\//i' => 'Opera',
            '/chrome\//i' => 'Chrome',
            '/safari\//i' => 'Safari',
            '/firefox\//i' => 'Firefox',
            '/msie|trident/i' => 'Internet Explorer',
        ];

        foreach ($map as $pattern => $name) {
            if (preg_match($pattern, $userAgent) === 1) {
                return $name;
            }
        }

        return null;
    }
}

