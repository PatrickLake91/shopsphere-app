<?php
declare(strict_types=1);

/** @var PDO|null $pdo */

echo "<h1>Wishlist</h1>";

if (!($pdo instanceof PDO)) {
    echo '<p class="msg"><strong>Database not configured.</strong> (PDO unavailable)</p>';
    return;
}

// Use logged-in user if present, otherwise fallback to demo user 1
$userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 1;

echo '<p class="muted">Showing wishlist for user <strong>#' . h((string)$userId) . '</strong>.</p>';

// Remove handler (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'remove' && isset($_POST['product_id'])) {
    $pid = (int)$_POST['product_id'];
    if ($pid > 0) {
        $stmt = $pdo->prepare("DELETE FROM wishlists WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$userId, $pid]);
    }
    header('Location: /index.php?page=wishlist');
    exit;
}

// Read wishlist items
$sql = "
SELECT
  w.product_id,
  w.created_at,
  p.productname,
  p.price
FROM wishlists w
LEFT JOIN products p ON p.id = w.product_id
WHERE w.user_id = ?
ORDER BY w.created_at DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$userId]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$items) {
    echo "<p>Your wishlist is empty.</p>";
    echo '<p><a class="btn" href="/index.php?page=catalogue">Back to catalogue</a></p>';
    return;
}

echo "<table>";
echo "<thead><tr><th>Product</th><th>Price</th><th>Added</th><th>Action</th></tr></thead><tbody>";

foreach ($items as $it) {
    $pid = (int)$it['product_id'];
    $name = $it['productname'] !== null ? (string)$it['productname'] : ("Product #" . $pid);
    $price = $it['price'] !== null ? (float)$it['price'] : 0.0;
    $created = (string)$it['created_at'];

    echo "<tr>";
    echo "<td>" . h($name) . "</td>";
    echo "<td>£" . number_format($price, 2) . "</td>";
    echo "<td>" . h($created) . "</td>";
    echo "<td>";
    echo '<form method="post" action="/index.php?page=wishlist" style="margin:0;">';
    echo '<input type="hidden" name="action" value="remove">';
    echo '<input type="hidden" name="product_id" value="' . h((string)$pid) . '">';
    echo '<button class="btn" type="submit">Remove</button>';
    echo '</form>';
    echo "</td>";
    echo "</tr>";
}

echo "</tbody></table>";
echo '<p style="margin-top:12px;"><a class="btn" href="/index.php?page=catalogue">Back to catalogue</a></p>';