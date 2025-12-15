<?php
require_once __DIR__ . "/db.php";

header("Content-Type: text/plain; charset=UTF-8");

try {
    $pdo->query("SELECT 1");
    echo "HEALTH_OK\n";
} catch (Throwable $e) {
    echo "HEALTH_FAIL\n";
    // Keep output safe for assessment (no secrets). Uncomment if you need:
    // echo $e->getMessage() . "\n";
}
