<?php
declare(strict_types=1);

/**
 * Checkout view (Step A)
 * - Reads session cart
 * - Displays cart summary (products from DB)
 * - "Place order" clears cart (assessment demo)
 *
 * Assumes:
 * - $pdo exists (from db.php)
 * - session already started in index.php
 * - h() exists in index.php
 */

/** @var PDO $pdo */

$cart = $_SESSION['cart'] ?? []; // product_id => qty
$items = [];
$total = 0.0;
$placed = false;
$errors = [];

function money(float $n): string { return '£' . number_format($n, 2); }

try {
    if ($cart) {
        $ids = array_map('intval', array_keys($cart));
        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        $stmt = $pdo->prepare("SELECT id, productname, price FROM products WHERE id IN ($placeholders) ORDER BY id ASC");
        $stmt->execute($ids);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $byId = [];
        foreach ($rows as $r) { $byId[(int)$r['id']] = $r; }

        foreach ($ids as $id) {
            if (!isset($byId[$id])) { continue; }
            $qty = (int)$cart[$id];
            if ($qty < 1) { continue; }

            $price = (float)$byId[$id]['price'];
            $line = $price * $qty;
            $total += $line;

            $items[] = [
                'id' => $id,
                'name' => (string)$byId[$id]['productname'],
                'price' => $price,
                'qty' => $qty,
                'line' => $line
            ];
        }
    }
} catch (Throwable $e) {
    $errors[] = "Checkout view error: " . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'place_order') {
    $_SESSION['cart'] = [];
    $placed = true;
    $items = [];
    $total = 0.0;
}
?>

<h1>Checkout</h1>

<?php if ($placed): ?>
  <div class="msg" style="border:1px solid #cfe9cf; background:#eef9ee; padding:10px; border-radius:8px; margin:12px 0;">
    <strong>Order placed!</strong> (Demo) Cart cleared.
  </div>
<?php endif; ?>

<?php foreach ($errors as $err): ?>
  <div class="msg" style="background:#ffecec; border:1px solid #f5c2c2; padding:10px; border-radius:8px; margin:12px 0;">
    <strong>Error:</strong> <?= h((string)$err) ?>
  </div>
<?php endforeach; ?>

<?php if (!$items): ?>
  <p>Your cart is empty. <a href="/index.php?page=catalogue">Back to products</a></p>
<?php else: ?>

  <p class="muted">Simulated payment (assessment demo): click <strong>Place order</strong>.</p>

  <table style="width:100%; border-collapse:collapse; margin-top:12px;">
    <thead>
      <tr>
        <th style="border:1px solid #ccc; padding:10px; text-align:left;">Product</th>
        <th style="border:1px solid #ccc; padding:10px; text-align:left;">Price</th>
        <th style="border:1px solid #ccc; padding:10px; text-align:left;">Qty</th>
        <th style="border:1px solid #ccc; padding:10px; text-align:left;">Line total</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($items as $it): ?>
        <tr>
          <td style="border:1px solid #ccc; padding:10px;"><?= h($it['name']) ?></td>
          <td style="border:1px solid #ccc; padding:10px;"><?= h(money((float)$it['price'])) ?></td>
          <td style="border:1px solid #ccc; padding:10px;"><?= (int)$it['qty'] ?></td>
          <td style="border:1px solid #ccc; padding:10px;"><?= h(money((float)$it['line'])) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <p style="margin-top:12px;"><strong>Total:</strong> <?= h(money($total)) ?></p>

  <form method="post" action="/index.php?page=checkout" style="margin-top:12px;">
    <input type="hidden" name="action" value="place_order">
    <button class="btn" type="submit">Place order</button>
  </form>

  <p style="margin-top:10px;"><a href="/index.php?page=cart">Back to cart</a></p>

<?php endif; ?>