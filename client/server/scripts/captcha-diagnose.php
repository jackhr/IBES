#!/usr/bin/env php
<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    echo "CLI only.\n";
    exit(1);
}

$serverRoot = dirname(__DIR__);
$bootstrapPath = $serverRoot . '/bootstrap.php';

if (!is_file($bootstrapPath)) {
    fwrite(STDERR, "Missing bootstrap.php at: {$bootstrapPath}\n");
    exit(2);
}

require_once $bootstrapPath;

$token = trim((string) ($argv[1] ?? 'invalid-token'));
$provider = App\Support\CaptchaVerifier::provider();
$transport = (string) Config::get('CAPTCHA_HTTP_TRANSPORT', 'auto');
$allowUrlFopen = ini_get('allow_url_fopen');
$allowUrlFopenNormalized = strtolower(trim($allowUrlFopen === false ? '' : (string) $allowUrlFopen));
$allowUrlFopenEnabled = in_array($allowUrlFopenNormalized, ['1', 'on', 'true', 'yes'], true);
$hasCurl = function_exists('curl_init');
$hasSocket = function_exists('stream_socket_client');
$result = App\Support\CaptchaVerifier::verify($token);

echo "php_sapi: " . PHP_SAPI . "\n";
echo "php_ini: " . (php_ini_loaded_file() ?: 'none') . "\n";
echo "captcha_provider: {$provider}\n";
echo "captcha_transport: {$transport}\n";
echo "allow_url_fopen_raw: " . ($allowUrlFopen === false ? 'unknown' : (string) $allowUrlFopen) . "\n";
echo "allow_url_fopen_enabled: " . ($allowUrlFopenEnabled ? 'yes' : 'no') . "\n";
echo "curl_available: " . ($hasCurl ? 'yes' : 'no') . "\n";
echo "socket_available: " . ($hasSocket ? 'yes' : 'no') . "\n";
echo 'verify_result: ' . json_encode($result, JSON_UNESCAPED_SLASHES) . "\n";

if ($token === 'invalid-token') {
    echo "note: invalid-token should fail with invalid-input-response if endpoint access is working.\n";
}
