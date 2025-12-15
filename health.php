<?php
declare(strict_types=1);

header("Content-Type: text/plain; charset=UTF-8");

try {
    require_once __DIR__ . "/db.php";
    $pdo->query("SELECT 1");
    echo "HEALTH_OK\n";
} catch (Throwable $e) {
    echo "HEALTH_FAIL\n";
    echo "ERROR_TYPE: " . get_class($e) . "\n";
    echo "ERROR_MSG: " . $e->getMessage() . "\n";
}
