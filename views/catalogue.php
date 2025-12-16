<?php
declare(strict_types=1);

/** @var PDO $pdo */
$stmt = $pdo->query("SELECT id, name, description, price, image_url FROM products ORDER BY id ASC");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h1>Products</h1>

<div class="grid">
  <?php foreach ($products as $p): ?>
    <div class="card">
      <div><strong><?= h((string)$p['name']) ?></strong></div>
      <div class="muted"><?= h((string)($p['description'] ?? '')) ?></div>
      <div class="price">£<?= number_format((float)$p['price'], 2) ?></div>

      <form method="post" action="/index.php?page=cart" style="margin-top:10px;">
        <input type="hidden" name="action" value="add">
        <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
        <button class="btn" type="submit">Add to cart</button>
      </form>
    </div>
  <?php endforeach; ?>
</div>