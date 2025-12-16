<?php
declare(strict_types=1);
session_start();

require __DIR__ . '/db.php';

function h(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

try {
    $stmt = $pdo->query("SELECT id, name, description, price FROM products ORDER BY id ASC");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo "ERROR_LOADING_PRODUCTS\n";
    echo $e->getMessage();
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>ShopSphere - Products</title>
  <style>
    body { font-family: Arial, sans-serif; margin: 24px; }
    .top { display:flex; gap:12px; align-items:center; margin-bottom:16px; }
    .top a { text-decoration:none; padding:8px 10px; border:1px solid #ccc; border-radius:8px; }
    table { border-collapse: collapse; width: 100%; margin-top: 12px; }
    th, td { border: 1px solid #ccc; padding: 10px; text-align:left; }
    .btn { padding:8px 10px; border-radius:10px; border:1px solid #333; background:#fff; cursor:pointer; }
    .muted { color:#666; font-size:0.95em; }
  </style>
</head>
<body>
  <div class="top">
    <strong>ShopSphere</strong>
    <a href="/index.php">Landing</a>
    <a href="/cart.php">Cart</a>
    <a href="/health.php">Health</a>
  </div>

  <h1>Products</h1>
  <p class="muted">Catalogue loaded from Azure Database for MySQL (PDO + prepared statements used in other pages).</p>

  <?php if (count($products) === 0): ?>
    <p>No products found. If needed, run setup: <code>/setup.php?token=SETUP123</code></p>
  <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Product</th>
          <th>Description</th>
          <th>Price (£)</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($products as $p): ?>
          <tr>
            <td><?= (int)$p['id'] ?></td>
            <td><?= h((string)$p['name']) ?></td>
            <td><?= h((string)($p['description'] ?? '')) ?></td>
            <td><?= number_format((float)$p['price'], 2) ?></td>
            <td>
              <form method="post" action="/cart.php" style="margin:0;">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
                <button class="btn" type="submit">Add to cart</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</body>
</html>