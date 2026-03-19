<?php

declare(strict_types=1);

require_once __DIR__ . '/../server/bootstrap.php';
require_once __DIR__ . '/../server/Config.php';

$hostname = Config::get('DB_HOST', '127.0.0.1');
$username = Config::get('DB_USERNAME', '');
$password = Config::get('DB_PASSWORD', '');
$database = Config::get('DB_DATABASE', '');

$domain = Config::get('APP_DOMAIN', '');
$www_domain = Config::get('APP_WWW_DOMAIN', $domain !== '' ? "www.$domain" : '');

$company_owner_first = Config::get('COMPANY_OWNER_FIRST', '');
$company_owner_last = Config::get('COMPANY_OWNER_LAST', '');
$company_owner_full = Config::get('COMPANY_OWNER_FULL', trim("$company_owner_first $company_owner_last"));
$company_name = Config::get('COMPANY_NAME', '');

$app_env = strtolower((string) Config::get('APP_ENV', ($_SERVER['SERVER_NAME'] ?? '') === 'localhost' ? 'local' : 'production'));
$testing = $app_env !== 'production';
$prod = !$testing;

$show_testimonials = Config::bool('SHOW_TESTIMONIALS', false);

$email_string = Config::get('EMAIL_STRING', '');
$contact_email_string = Config::get('CONTACT_EMAIL_STRING', $email_string);

$testing_email_value = Config::get('TESTING_EMAIL_STRING');
if (is_string($testing_email_value) && $testing_email_value !== '') {
    $testing_email_string = $testing_email_value;
}

$debugging_email_value = Config::get('DEBUGGING_EMAIL_STRING');
if (is_string($debugging_email_value) && $debugging_email_value !== '') {
    $debugging_email_string = $debugging_email_value;
}

$destory_session_after_ordering = Config::bool('DESTORY_SESSION_AFTER_ORDERING', true);
