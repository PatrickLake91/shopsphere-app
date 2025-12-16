<?php
declare(strict_types=1);

/**
 * Checkout view (Step C3a)
 * - Reads session cart
 * - Displays cart summary (products from DB)
 * - "Place order" writes to orders + order_items, then clears cart
 *
 * Schema used (confirmed):
 * orders: id (AI), userid, productid, quantity, created_at
 * order_items: id (AI), order_id, product_id, qty, price_each
 */

/** @var PDO|null $pdo */

$cart = $_SESSION['cart'] ?? []; // product_id => qty
$items = [];
$total = 0.0;
$placed = false;
$errors = [];

function money(float $n): string { return '£' . number_format($n, 2); }

if (!($pdo instanceof PDO)) {
    $errors[] = "Database not configured (PDO unavailable).";
} else {
    // Build display items from DB
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
}

// Handle place order (DB write)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'place_order') {
    if (!($pdo instanceof PDO)) {
        $errors[] = "Cannot place order: database not available.";
    } elseif (!$items) {
        $errors[] = "Cannot place order: cart is empty.";
    } else {
        // Demo user until login exists
        $demoUserId = 1;

        try {
            $pdo->beginTransaction();

            // Create an "order header" row (minimal; productid/quantity are nullable in schema).
            $stmtOrder = $pdo->prepare("INSERT INTO orders (userid) VALUES (?)");
            $stmtOrder->execute([$demoUserId]);
            $orderId = (int)$pdo->lastInsertId();

            // Insert order items
            $stmtItem = $pdo->prepare("
                INSERT INTO order_items (order_id, product_id, qty, price_each)
                VALUES (?, ?, ?, ?)
            ");

            foreach ($items as $it) {
                $stmtItem->execute([
                    $orderId,
                    (int)$it['id'],
                    (int)$it['qty'],
                    (float)$it['price']
                ]);
            }

            $pdo->commit();

            // Clear cart after successful commit
            $_SESSION['cart'] = [];
            $placed = true;
            $items = [];
            $total = 0.0;

        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $errors[] = "Order placement failed: " . $e->getMessage();
        }
    }
}
?>

<h1>Checkout</h1>

<?php if ($placed): ?>
  <div class="msg" style="border:1px solid #cfe9cf; background:#eef9ee; padding:10px; border-radius:8px; margin:12px 0;">
    <strong>Order placed!</strong> Cart saved to database and cleared.
    <div class="muted" style="margin-top:6px;">You can view it on the <a href="/index.php?page=orders">Orders</a> page.</div>
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