<?php
declare(strict_types=1);

/** @var PDO|null $pdo */

if (!($pdo instanceof PDO)) {
    echo '<h1>Products</h1>';
    echo '<p class="msg"><strong>Database not configured.</strong> (PDO unavailable)</p>';
    return;
}

// Use logged-in user if present, otherwise fallback to demo user 1
$userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 1;

// Handle "Add to wishlist" POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'wishlist_add' && isset($_POST['product_id'])) {
    $pid = (int)$_POST['product_id'];
    if ($pid > 0) {
        // composite PK (user_id, product_id) => duplicates safe via INSERT IGNORE
        $stmt = $pdo->prepare("INSERT IGNORE INTO wishlists (user_id, product_id) VALUES (?, ?)");
        $stmt->execute([$userId, $pid]);
    }
    header('Location: /index.php?page=catalogue');
    exit;
}

$stmt = $pdo->query("SELECT id, productname, price, created_at FROM products ORDER BY id ASC");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h1>Products</h1>

<div class="grid">
  <?php foreach ($products as $p): ?>
    <div class="card">
      <div><strong><?= h((string)$p['productname']) ?></strong></div>
      <div class="muted">Added: <?= h((string)$p['created_at']) ?></div>
      <div><strong>£<?= number_format((float)$p['price'], 2) ?></strong></div>

      <div style="display:flex; gap:10px; margin-top:10px; flex-wrap:wrap;">
        <form method="post" action="/index.php?page=cart" style="margin:0;">
          <input type="hidden" name="action" value="add">
          <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
          <button class="btn" type="submit">Add to cart</button>
        </form>

        <form method="post" action="/index.php?page=catalogue" style="margin:0;">
          <input type="hidden" name="action" value="wishlist_add">
          <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
          <button class="btn" type="submit">♡ Wishlist</button>
        </form>
      </div>
    </div>
  <?php endforeach; ?>
</div>