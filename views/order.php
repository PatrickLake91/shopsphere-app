<?php
declare(strict_types=1);

/** @var PDO|null $pdo */

echo "<h1>Order details</h1>";

if (!($pdo instanceof PDO)) {
    echo '<p class="msg"><strong>Database not configured.</strong> (PDO unavailable)</p>';
    return;
}

$orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($orderId < 1) {
    echo '<p class="msg"><strong>Missing or invalid order id.</strong></p>';
    echo '<p><a class="btn" href="/index.php?page=orders">Back to orders</a></p>';
    return;
}

// Fetch basic order header
$stmt = $pdo->prepare("SELECT id, userid, created_at FROM orders WHERE id = ?");
$stmt->execute([$orderId]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    echo '<p class="msg"><strong>Order not found.</strong></p>';
    echo '<p><a class="btn" href="/index.php?page=orders">Back to orders</a></p>';
    return;
}

// Fetch line items
$sql = "
SELECT
  oi.id AS order_item_id,
  oi.product_id,
  p.productname,
  oi.qty,
  oi.price_each,
  (oi.qty * oi.price_each) AS line_total
FROM order_items oi
LEFT JOIN products p ON p.id = oi.product_id
WHERE oi.order_id = ?
ORDER BY oi.id ASC
";
$stmtItems = $pdo->prepare($sql);
$stmtItems->execute([$orderId]);
$items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

echo '<p class="muted">Order <strong>#' . h((string)$order['id']) . '</strong> | User: ' . h((string)$order['userid']) . ' | Created: ' . h((string)$order['created_at']) . '</p>';

if (!$items) {
    echo '<p>No items found for this order.</p>';
    echo '<p><a class="btn" href="/index.php?page=orders">Back to orders</a></p>';
    return;
}

$total = 0.0;

echo "<table>";
echo "<thead><tr><th>Item ID</th><th>Product</th><th>Qty</th><th>Price</th><th>Line total</th></tr></thead><tbody>";

foreach ($items as $it) {
    $line = (float)$it['line_total'];
    $total += $line;

    $name = isset($it['productname']) && $it['productname'] !== null
        ? (string)$it['productname']
        : ("Product #" . (int)$it['product_id']);

    echo "<tr>";
    echo "<td>" . h((string)$it['order_item_id']) . "</td>";
    echo "<td>" . h($name) . "</td>";
    echo "<td>" . h((string)(int)$it['qty']) . "</td>";
    echo "<td>£" . number_format((float)$it['price_each'], 2) . "</td>";
    echo "<td>£" . number_format($line, 2) . "</td>";
    echo "</tr>";
}

echo "</tbody></table>";

echo '<p style="margin-top:12px;"><strong>Order total:</strong> £' . number_format($total, 2) . '</p>';

echo '<p style="margin-top:12px;">';
echo '<a class="btn" href="/index.php?page=orders">Back to orders</a> ';
echo '<a class="btn" href="/index.php?page=catalogue">Shop more</a>';
echo '</p>';