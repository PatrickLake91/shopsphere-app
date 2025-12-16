<?php
declare(strict_types=1);

/** @var PDO|null $pdo */

$cart = $_SESSION['cart'] ?? [];
$items = [];
$total = 0.0;
$placed = false;
$errors = [];

function money(float $n): string { return '£' . number_format($n, 2); }

// Resolve user
$userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 1;

// Virtual payment methods (demo)
$allowedMethods = ['CARD','PAYPAL','APPLEPAY','BANK_TRANSFER'];
$paymentMethod = strtoupper((string)($_POST['payment_method'] ?? 'CARD'));
if (!in_array($paymentMethod, $allowedMethods, true)) {
    $paymentMethod = 'CARD';
}

try {
    if ($cart && $pdo instanceof PDO) {
        $ids = array_map('intval', array_keys($cart));
        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        $stmt = $pdo->prepare("SELECT id, productname, price FROM products WHERE id IN ($placeholders)");
        $stmt->execute($ids);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $byId = [];
        foreach ($rows as $r) { $byId[(int)$r['id']] = $r; }

        foreach ($ids as $id) {
            if (!isset($byId[$id])) continue;
            $qty = (int)$cart[$id];
            if ($qty < 1) continue;

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
    $errors[] = "Checkout error: " . $e->getMessage();
}

// PLACE ORDER
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'place_order') {

    // Validate payment method explicitly on submit (demo safety)
    $submittedMethod = strtoupper((string)($_POST['payment_method'] ?? ''));
    if (!in_array($submittedMethod, $allowedMethods, true)) {
        $errors[] = "Invalid payment method selected.";
    } else {
        $paymentMethod = $submittedMethod;
    }

    if (!$errors && $pdo instanceof PDO && $items) {
        try {
            $pdo->beginTransaction();

            // Create order (status defaults to NEW in DB, but we set it explicitly for clarity)
            $stmt = $pdo->prepare("INSERT INTO orders (userid, status, payment_method) VALUES (?, 'NEW', ?)");
            $stmt->execute([$userId, $paymentMethod]);
            $orderId = (int)$pdo->lastInsertId();

            // Order items
            $stmtItem = $pdo->prepare("
                INSERT INTO order_items (order_id, product_id, qty, price_each)
                VALUES (?, ?, ?, ?)
            ");

            foreach ($items as $it) {
                $stmtItem->execute([
                    $orderId,
                    $it['id'],
                    $it['qty'],
                    $it['price']
                ]);
            }

            $pdo->commit();
            $_SESSION['cart'] = [];
            $placed = true;
            $items = [];
            $total = 0.0;
        } catch (Throwable $e) {
            $pdo->rollBack();
            $errors[] = "Order failed: " . $e->getMessage();
        }
    }
}
?>

<h1>Checkout</h1>

<?php if ($placed): ?>
  <div class="msg" style="border:1px solid #cfe9cf; background:#eef9ee;">
    <strong>Order placed!</strong> (Demo) Payment method recorded: <strong><?= h($paymentMethod) ?></strong>
  </div>
<?php endif; ?>

<?php foreach ($errors as $err): ?>
  <div class="msg" style="background:#ffecec; border:1px solid #f5c2c2;">
    <strong>Error:</strong> <?= h($err) ?>
  </div>
<?php endforeach; ?>

<?php if (!$items): ?>
  <p>Your cart is empty. <a href="/index.php?page=catalogue">Back to products</a></p>
<?php else: ?>
  <p class="muted">Ordering as user <strong>#<?= h((string)$userId) ?></strong>.</p>

  <table>
    <thead><tr><th>Product</th><th>Price</th><th>Qty</th><th>Line</th></tr></thead>
    <tbody>
    <?php foreach ($items as $it): ?>
      <tr>
        <td><?= h($it['name']) ?></td>
        <td><?= money($it['price']) ?></td>
        <td><?= (int)$it['qty'] ?></td>
        <td><?= money($it['line']) ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>

  <p><strong>Total:</strong> <?= money($total) ?></p>

  <form method="post">
    <input type="hidden" name="action" value="place_order">

    <label class="muted" for="payment_method"><strong>Payment method (virtual)</strong></label><br>
    <select name="payment_method" id="payment_method" class="btn" style="margin:8px 0; padding:8px 10px;">
      <?php foreach ($allowedMethods as $m): ?>
        <option value="<?= h($m) ?>" <?= $paymentMethod === $m ? 'selected' : '' ?>><?= h($m) ?></option>
      <?php endforeach; ?>
    </select>

    <div style="margin-top:10px;">
      <button class="btn">Place order</button>
    </div>
  </form>

  <p class="muted" style="margin-top:10px;">Demo note: this simulates payment selection and validates allowed methods without processing real payments.</p>
<?php endif; ?>