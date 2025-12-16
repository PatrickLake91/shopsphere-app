<?php
declare(strict_types=1);

/** @var PDO|null $pdo */

echo "<h1>Orders</h1>";

if (!($pdo instanceof PDO)) {
    echo '<p class="msg"><strong>Database not configured.</strong> (PDO unavailable)</p>';
    return;
}

// Simple read-only orders summary (latest first).
// Uses existing schema only: orders + order_items.
$sql = "
SELECT
  o.id AS order_id,
  o.userid,
  o.created_at,
  COALESCE(SUM(oi.qty * oi.price_each), 0) AS order_total,
  COALESCE(SUM(oi.qty), 0) AS total_items
FROM orders o
LEFT JOIN order_items oi ON oi.order_id = o.id
GROUP BY o.id, o.userid, o.created_at
ORDER BY o.id DESC
LIMIT 50
";

$orders = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

if (!$orders) {
    echo "<p>No orders found yet.</p>";
    return;
}

echo "<table>";
echo "<thead><tr><th>Order ID</th><th>User</th><th>Created</th><th>Items</th><th>Total</th></tr></thead><tbody>";

foreach ($orders as $o) {
    $orderId = (int)$o['order_id'];
    $userId = isset($o['userid']) ? (int)$o['userid'] : 0;
    $created = (string)$o['created_at'];
    $items = (int)$o['total_items'];
    $total = (float)$o['order_total'];

    echo "<tr>";
    echo "<td>" . h((string)$orderId) . "</td>";
    echo "<td>" . h((string)$userId) . "</td>";
    echo "<td>" . h($created) . "</td>";
    echo "<td>" . h((string)$items) . "</td>";
    echo "<td>£" . number_format($total, 2) . "</td>";
    echo "</tr>";
}

echo "</tbody></table>";

echo '<p class="muted" style="margin-top:10px;">Note: this is a read-only summary for assessment demo.</p>';