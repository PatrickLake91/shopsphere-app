<?php
declare(strict_types=1);

/** @var PDO|null $pdo */

echo "<h1>Order Details</h1>";

if (!($pdo instanceof PDO)) {
    echo '<p class="msg"><strong>Database not configured.</strong> (PDO unavailable)</p>';
    return;
}

$userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 1;

$orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($orderId < 1) {
    echo '<p class="msg"><strong>Missing or invalid order id.</strong></p>';
    echo '<p><a class="btn" href="/index.php?page=orders">Back to orders</a></p>';
    return;
}

// Confirm order belongs to this user
$stmt = $pdo->prepare("SELECT id, userid, created_at FROM orders WHERE id = ?");
$stmt->execute([$orderId]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    echo '<p class="msg"><strong>Order not found.</strong></p>';
    echo '<p><a class="btn" href="/index.php?page=orders">Back to orders</a></p>';
    return;
}

if ((int)$order['userid'] !== $userId) {
    http_response_code(403);
    echo '<p class="msg" style="background:#ffecec; border:1px solid #f5c2c2;"><strong>Forbidden:</strong> you can only view your own orders.</p>';
    echo '<p><a class="btn" href="/index.php?page=orders">Back to orders</a></p>';
    return;
}

// Fetch items
$sql = "
SELECT
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
$stmt = $pdo->prepare($sql);
$stmt->execute([$orderId]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total = 0.0;
foreach ($items as $it) {
    $total += (float)$it['line_total'];
}

echo '<p class="muted">Order <strong>#' . h((string)$orderId) . '</strong> for user <strong>#' . h((string)$userId) . '</strong></p>';
echo '<p class="muted">Created: ' . h((string)$order['created_at']) . '</p>';

if (!$items) {
    echo '<p>No items recorded for this order.</p>';
    echo '<p><a class="btn" href="/index.php?page=orders">Back to orders</a></p>';
    return;
}

echo '<table>';
echo '<thead><tr><th>Product</th><th>Qty</th><th>Price</th><th>Line total</th></tr></thead><tbody>';

foreach ($items as $it) {
    $name = $it['productname'] !== null ? (string)$it['productname'] : ('Product #' . (int)$it['product_id']);
    $qty = (int)$it['qty'];
    $price = (float)$it['price_each'];
    $line = (float)$it['line_total'];

    echo '<tr>';
    echo '<td>' . h($name) . '</td>';
    echo '<td>' . h((string)$qty) . '</td>';
    echo '<td>£' . number_format($price, 2) . '</td>';
    echo '<td>£' . number_format($line, 2) . '</td>';
    echo '</tr>';
}

echo '</tbody></table>';
echo '<p style="margin-top:12px;"><strong>Total:</strong> £' . number_format($total, 2) . '</p>';
echo '<p style="margin-top:12px;"><a class="btn" href="/index.php?page=orders">Back to orders</a></p>';