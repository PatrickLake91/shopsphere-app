<?php
declare(strict_types=1);

/** @var PDO|null $pdo */

// Simple admin gate for demo: user #3 is "admin"
$userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
if ($userId !== 3) {
    http_response_code(403);
    echo '<h1>Admin Products</h1>';
    echo '<p class="msg" style="background:#ffecec; border:1px solid #f5c2c2;"><strong>Forbidden:</strong> admin access only (demo rule: user #3).</p>';
    echo '<p class="muted">Log in as user <strong>#3</strong> to manage products.</p>';
    return;
}

echo "<h1>Admin Products</h1>";

if (!($pdo instanceof PDO)) {
    echo '<p class="msg"><strong>Database not configured.</strong> (PDO unavailable)</p>';
    return;
}

// Handle POST: update stock (PRG pattern to avoid resubmit + fix "whitespace below headings")
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_stock') {
    $productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    $stockRaw  = $_POST['stock'] ?? '';

    // Validate stock: must be an integer >= 0
    $stockVal = filter_var($stockRaw, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);

    if ($productId > 0 && $stockVal !== false) {
        try {
            $stmt = $pdo->prepare("UPDATE products SET stock = ? WHERE id = ?");
            $stmt->execute([(int)$stockVal, $productId]);
            $_SESSION['flash_admin'] = "Stock updated for product #{$productId}.";
        } catch (Throwable $e) {
            $_SESSION['flash_admin'] = "Update failed: " . $e->getMessage();
        }
    } else {
        $_SESSION['flash_admin'] = "Invalid stock value (must be an integer ≥ 0).";
    }

    header('Location: /index.php?page=admin_products');
    exit;
}

// Flash message
if (!empty($_SESSION['flash_admin'])) {
    echo '<div class="msg">' . h((string)$_SESSION['flash_admin']) . '</div>';
    unset($_SESSION['flash_admin']);
}

// Read list
$stmt = $pdo->query("SELECT id, productname, price, stock, created_at FROM products ORDER BY id ASC");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$rows) {
    echo '<p>No products found.</p>';
    return;
}

echo '<p class="muted">Demo admin view: update stock (minimal CRUD for assessment).</p>';

echo '<table>';
echo '<thead><tr><th>ID</th><th>Name</th><th>Price</th><th>Stock</th><th>Update Stock</th><th>Created</th></tr></thead><tbody>';

foreach ($rows as $r) {
    $id      = (int)$r['id'];
    $name    = (string)($r['productname'] ?? ('Product #' . $id));
    $price   = (float)($r['price'] ?? 0);
    $stock   = (int)($r['stock'] ?? 0);
    $created = (string)($r['created_at'] ?? '');

    echo '<tr>';
    echo '<td>' . h((string)$id) . '</td>';
    echo '<td>' . h($name) . '</td>';
    echo '<td>£' . number_format($price, 2) . '</td>';
    echo '<td>' . h((string)$stock) . '</td>';

    // Inline update form
    echo '<td>';
    echo '  <form method="post" action="/index.php?page=admin_products" style="margin:0; display:flex; gap:8px; align-items:center;">';
    echo '    <input type="hidden" name="action" value="update_stock">';
    echo '    <input type="hidden" name="product_id" value="' . h((string)$id) . '">';
    echo '    <input type="number" name="stock" min="0" step="1" value="' . h((string)$stock) . '" style="width:90px; padding:6px; border:1px solid #ccc; border-radius:8px;">';
    echo '    <button class="btn" type="submit">Save</button>';
    echo '  </form>';
    echo '</td>';

    echo '<td>' . h($created) . '</td>';
    echo '</tr>';
}

echo '</tbody></table>';