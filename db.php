<?php
// db.php — create $pdo or throw a useful exception (do NOT die/echo here)

declare(strict_types=1);

$host = getenv('DB_HOST') ?: '';
$db   = getenv('DB_NAME') ?: '';
$user = getenv('DB_USER') ?: '';
$pass = getenv('DB_PASS') ?: '';
$port = getenv('DB_PORT') ?: '3306';

if ($host === '' || $db === '' || $user === '') {
    throw new RuntimeException("Missing DB env vars. Need DB_HOST, DB_NAME, DB_USER (and DB_PASS if required).");
}

$dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

// If your Azure MySQL requires SSL (common), uncomment these two lines:
// $options[PDO::MYSQL_ATTR_SSL_CA] = '/etc/ssl/certs/ca-certificates.crt';
// $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;

$pdo = new PDO($dsn, $user, $pass, $options);
