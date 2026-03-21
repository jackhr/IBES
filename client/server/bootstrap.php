<?php

declare(strict_types=1);

require_once __DIR__ . '/Config.php';
require_once __DIR__ . '/Database.php';

Config::loadEnv(dirname(__DIR__) . '/.env');

$timezone = Config::get('APP_TIMEZONE', 'UTC');

if (is_string($timezone) && $timezone !== '') {
    date_default_timezone_set($timezone);
}

spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';

    if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));
    $file = __DIR__ . '/App/' . str_replace('\\', '/', $relativeClass) . '.php';

    if (is_file($file)) {
        require_once $file;
    }
});
