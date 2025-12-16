<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

header('Content-Type: text/plain; charset=UTF-8');

require_once __DIR__ . '/db.php';

echo "HEALTH_OK\n";
echo "PHP_VERSION: " . PHP_VERSION . "\n";

try {
    if (!($pdo instanceof PDO)) {
        echo "DB_STATUS: NOT_CONFIGURED\n";
        exit;
    }
    $pdo->query("SELECT 1");
    echo "DB_STATUS: OK\n";
} catch (Throwable $e) {
    echo "DB_STATUS: ERROR\n";
    echo "ERROR_MSG: " . $e->getMessage() . "\n";
}
