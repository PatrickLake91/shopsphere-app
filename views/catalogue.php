<?php
declare(strict_types=1);

/** @var PDO|null $pdo */

if (!($pdo instanceof PDO)) {
    echo '<h1>Products</h1>';
    echo '<p class="msg"><strong>Database not configured.</strong> (PDO unavailable)</p>';
    return;
}

$stmt = $pdo->query("SELECT id, productname, price, stock, created_at FROM products ORDER BY id ASC");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h1>Products</h1>

<div class="grid">
  <?php foreach ($products as $p): ?>
    <?php
      $pid = (int)$p['id'];
      $name = (string)($p['productname'] ?? ('Product #' . $pid));
      $price = (float)($p['price'] ?? 0);
      $stock = (int)($p['stock'] ?? 0);
      $created = (string)($p['created_at'] ?? '');
      $inStock = $stock > 0;
    ?>
    <div class="card">
      <div><strong><?= h($name) ?></strong></div>
      <div class="muted">Added: <?= h($created) ?></div>
      <div><strong>£<?= number_format($price, 2) ?></strong></div>
      <div class="muted">Stock: <strong><?= h((string)$stock) ?></strong></div>

      <?php if ($inStock): ?>
        <form method="post" action="/index.php?page=cart" style="margin-top:10px;">
          <input type="hidden" name="action" value="add">
          <input type="hidden" name="product_id" value="<?= $pid ?>">
          <button class="btn" type="submit">Add to cart</button>
        </form>
      <?php else: ?>
        <div class="msg" style="margin-top:10px; background:#fff6d6; border:1px solid #f0d48a;">
          <strong>Out of stock</strong>
        </div>
        <button class="btn" type="button" disabled style="opacity:0.5; margin-top:10px;">Add to cart</button>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
</div>