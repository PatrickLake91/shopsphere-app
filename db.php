<?php
// db.php — reads Azure App Settings and creates $pdo (PDO connection)

// Accept either DB_USER or DB_USERNAME (covers earlier confusion)
$DB_HOST = getenv('DB_HOST') ?: '';
$DB_NAME = getenv('DB_NAME') ?: '';
$DB_USER = getenv('DB_USER') ?: (getenv('DB_USERNAME') ?: '');
$DB_PASS = getenv('DB_PASSWORD') ?: '';

// If any are missing, don't attempt DB connection.
// health.php will show NOT_CONFIGURED.
if ($DB_HOST === '' || $DB_NAME === '' || $DB_USER === '' || $DB_PASS === '') {
    return;
}

$dsn = "mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4";

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

$pdo = new PDO($dsn, $DB_USER, $DB_PASS, $options);
