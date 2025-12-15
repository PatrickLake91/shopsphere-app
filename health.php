<?php
header('Content-Type: text/plain; charset=UTF-8');

require_once __DIR__ . '/db.php';

echo "HEALTH_OK\n";
echo "PHP_VERSION: " . PHP_VERSION . "\n";

if ($pdo instanceof PDO) {
    try {
        $pdo->query('SELECT 1');
        echo "DB_STATUS: OK\n";
    } catch (Throwable $e) {
        echo "DB_STATUS: ERROR\n";
    }
} else {
    echo "DB_STATUS: NOT_CONFIGURED\n";
}
