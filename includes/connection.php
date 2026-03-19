<?php

declare(strict_types=1);

include_once __DIR__ . '/app-config.php';
require_once __DIR__ . '/../server/Database.php';

$con = mysqli_connect($hostname, $username, $password, $database);

if ($con === false || mysqli_connect_errno()) {
    error_log('MySQLi connection failed: ' . mysqli_connect_error());
    http_response_code(500);
    exit('Database connection failed.');
}

try {
    // Initialize the PDO connection path so server/Database.php is executed.
    $pdo = Database::connection();
} catch (Throwable $exception) {
    error_log('PDO connection failed: ' . $exception->getMessage());
    http_response_code(500);
    exit('Database connection failed.');
}
