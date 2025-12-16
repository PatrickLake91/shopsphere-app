<?php
declare(strict_types=1);

/** @var PDO $pdo */

try {
    $stmt = $pdo->query("SELECT id, productname, price, created_at FROM products ORDER BY id ASC");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    echo '<div class="msg" style="border:1px solid #f3b; background:#fee;">View error: ' . h($e->getMessage()) . '</div>';
    $products = [];
}
?>

<h1>Products</h1>

<div class="grid">
  <?php foreach ($products as $p): ?>
    <div class="card">
      <div><strong><?= h((string)($p['productname'] ?? '')) ?></strong></div>
      <div class="muted">Added: <?= h((string)($p['created_at'] ?? '')) ?></div>
      <div class="price">£<?= number_format((float)($p['price'] ?? 0), 2) ?></div>

      <form method="post" action="/index.php?page=cart" style="margin-top:10px;">
        <input type="hidden" name="action" value="add">
        <input type="hidden" name="product_id" value="<?= (int)($p['id'] ?? 0) ?>">
        <button class="btn" type="submit">Add to cart</button>
      </form>
    </div>
  <?php endforeach; ?>

  <?php if (count($products) === 0): ?>
    <div class="msg">No products found.</div>
  <?php endif; ?>
</div>