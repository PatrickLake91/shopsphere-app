<?php
declare(strict_types=1);

/** @var PDO|null $pdo */

// Simple admin gate for demo: user #1 is "admin"
$userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
if ($userId !== 3) {
    http_response_code(403);
    echo '<h1>Admin Products</h1>';
    echo '<p class="msg" style="background:#ffecec; border:1px solid #f5c2c2;"><strong>Forbidden:</strong> admin access only (demo rule: user #1).</p>';
    echo '<p class="muted">Log in as user <strong>#1</strong> to manage products.</p>';
    return;
}

echo "<h1>Admin Products</h1>";

if (!($pdo instanceof PDO)) {
    echo '<p class="msg"><strong>Database not configured.</strong> (PDO unavailable)</p>';
    return;
}

// Read-only list for now
$stmt = $pdo->query("SELECT id, productname, price, stock, created_at FROM products ORDER BY id ASC");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$rows) {
    echo '<p>No products found.</p>';
    return;
}

echo '<p class="muted">Demo admin view: product catalogue overview (CRUD actions added in next step).</p>';

echo '<table>';
echo '<thead><tr><th>ID</th><th>Name</th><th>Price</th><th>Stock</th><th>Created</th></tr></thead><tbody>';

foreach ($rows as $r) {
    $id = (int)$r['id'];
    $name = (string)($r['productname'] ?? ('Product #' . $id));
    $price = (float)($r['price'] ?? 0);
    $stock = (int)($r['stock'] ?? 0);
    $created = (string)($r['created_at'] ?? '');

    echo '<tr>';
    echo '<td>' . h((string)$id) . '</td>';
    echo '<td>' . h($name) . '</td>';
    echo '<td>Â£' . number_format($price, 2) . '</td>';
    echo '<td>' . h((string)$stock) . '</td>';
    echo '<td>' . h($created) . '</td>';
    echo '</tr>';
}

echo '</tbody></table>';