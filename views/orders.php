<?php
declare(strict_types=1);

/** @var PDO|null $pdo */

echo "<h1>Orders</h1>";

if (!($pdo instanceof PDO)) {
    echo '<p class="msg"><strong>Database not configured.</strong> (PDO unavailable)</p>';
    return;
}

$userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 1;

echo '<p class="muted">Showing orders for user <strong>#' . h((string)$userId) . '</strong>.</p>';

$sql = "
SELECT
  o.id AS order_id,
  o.userid,
  o.created_at,
  COALESCE(SUM(oi.qty), 0) AS total_items,
  COALESCE(SUM(oi.qty * oi.price_each), 0) AS order_total
FROM orders o
LEFT JOIN order_items oi ON oi.order_id = o.id
WHERE o.userid = ?
GROUP BY o.id, o.userid, o.created_at
ORDER BY o.id DESC
LIMIT 100
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$userId]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$rows) {
    echo "<p>You have no orders yet.</p>";
    echo '<p><a class="btn" href="/index.php?page=catalogue">Back to catalogue</a></p>';
    return;
}

echo '<table>';
echo '<thead><tr><th>Order ID</th><th>User</th><th>Created</th><th>Items</th><th>Total</th><th>Action</th></tr></thead><tbody>';

foreach ($rows as $r) {
    $oid = (int)$r['order_id'];
    $uid = (int)$r['userid'];
    $created = (string)$r['created_at'];
    $items = (int)$r['total_items'];
    $total = (float)$r['order_total'];

    echo '<tr>';
    echo '<td>' . h((string)$oid) . '</td>';
    echo '<td>' . h((string)$uid) . '</td>';
    echo '<td>' . h($created) . '</td>';
    echo '<td>' . h((string)$items) . '</td>';
    echo '<td>£' . number_format($total, 2) . '</td>';
    echo '<td><a class="btn" href="/index.php?page=order&id=' . h((string)$oid) . '">View</a></td>';
    echo '</tr>';
}

echo '</tbody></table>';
echo '<p class="muted">Note: this is a read-only summary for assessment demo.</p>';