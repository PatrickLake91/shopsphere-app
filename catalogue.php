<?php
declare(strict_types=1);
session_start();

require __DIR__ . '/db.php';

function h(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

try {
    $stmt = $pdo->query("SELECT id, name, description, price, image_url FROM products ORDER BY id DESC");
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
  <title>ShopSphere</title>
  <style>
    body { font-family: Arial, sans-serif; margin: 24px; }
    .top { display:flex; gap:12px; align-items:center; margin-bottom:16px; }
    .top a { text-decoration:none; padding:8px 10px; border:1px solid #ccc; border-radius:8px; }
    .grid { display:grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap:14px; }
    .card { border:1px solid #ddd; border-radius:12px; padding:14px; }
    .price { font-weight:700; margin:10px 0; }
    .muted { color:#666; font-size: 0.95em; }
    .btn { display:inline-block; padding:8px 10px; border-radius:10px; border:1px solid #333; background:#fff; cursor:pointer; }
    .btnrow { display:flex; gap:10px; flex-wrap:wrap; margin-top:10px; }
  </style>
</head>
<body>
  <div class="top">
    <strong>ShopSphere</strong>
    <a href="/cart.php">Cart</a>
    <a href="/orders.php">My Orders</a>
    <a href="/wishlist.php">Wishlist</a>
    <a href="/auth.php">Login / Register</a>
    <a href="/health.php">Health</a>
  </div>

  <h1>Product Catalogue</h1>
  <p class="muted">Products are loaded from the cloud database (MySQL). Add items to cart (session) or wishlist (per user).</p>

  <?php if (count($products) === 0): ?>
    <p><strong>No products found.</strong> Run setup: <code>/setup.php?token=SETUP123</code></p>
  <?php else: ?>
    <div class="grid">
      <?php foreach ($products as $p): ?>
        <div class="card">
          <div><strong><?= h((string)$p['name']) ?></strong></div>
          <div class="muted"><?= h((string)($p['description'] ?? '')) ?></div>
          <div class="price">£<?= number_format((float)$p['price'], 2) ?></div>

          <div class="btnrow">
            <form method="post" action="/cart.php" style="margin:0;">
              <input type="hidden" name="action" value="add">
              <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
              <button class="btn" type="submit">Add to cart</button>
            </form>

            <form method="post" action="/wishlist.php" style="margin:0;">
              <input type="hidden" name="action" value="add">
              <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
              <button class="btn" type="submit">Wishlist</button>
            </form>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

</body>
</html>
