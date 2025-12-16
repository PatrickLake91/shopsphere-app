<?php
declare(strict_types=1);
session_start();

require __DIR__ . '/db.php';

function h(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

$_SESSION['cart'] = $_SESSION['cart'] ?? []; // product_id => qty

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $pid = (int)($_POST['product_id'] ?? 0);

    if ($action === 'add' && $pid > 0) {
        $_SESSION['cart'][$pid] = ($_SESSION['cart'][$pid] ?? 0) + 1;
        header('Location: /cart.php');
        exit;
    }

    if ($action === 'remove' && $pid > 0) {
        unset($_SESSION['cart'][$pid]);
        header('Location: /cart.php');
        exit;
    }

    if ($action === 'clear') {
        $_SESSION['cart'] = [];
        header('Location: /cart.php');
        exit;
    }
}

// Load product details for items in cart
$items = [];
$total = 0.0;

$cartIds = array_keys($_SESSION['cart']);
if (count($cartIds) > 0) {
    $placeholders = implode(',', array_fill(0, count($cartIds), '?'));
    $stmt = $pdo->prepare("SELECT id, name, price FROM products WHERE id IN ($placeholders)");
    $stmt->execute($cartIds);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $byId = [];
    foreach ($products as $p) { $byId[(int)$p['id']] = $p; }

    foreach ($_SESSION['cart'] as $id => $qty) {
        $id = (int)$id;
        $qty = (int)$qty;
        if (!isset($byId[$id])) { continue; }
        $price = (float)$byId[$id]['price'];
        $line = $price * $qty;
        $total += $line;

        $items[] = [
            'id' => $id,
            'name' => (string)$byId[$id]['name'],
            'price' => $price,
            'qty' => $qty,
            'line' => $line
        ];
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>ShopSphere - Cart</title>
  <style>
    body { font-family: Arial, sans-serif; margin: 24px; }
    a { text-decoration:none; }
    table { border-collapse: collapse; width: 100%; margin-top: 12px; }
    th, td { border: 1px solid #ccc; padding: 10px; text-align:left; }
    .top { display:flex; gap:12px; align-items:center; margin-bottom:16px; }
    .btn { padding:8px 10px; border-radius:10px; border:1px solid #333; background:#fff; cursor:pointer; }
    .right { text-align:right; }
  </style>
</head>
<body>
  <div class="top">
    <strong>ShopSphere</strong>
    <a class="btn" href="/home.php">Back to products</a>
    <a class="btn" href="/checkout.php">Checkout</a>
  </div>

  <h1>Your Cart</h1>

  <?php if (count($items) === 0): ?>
    <p>Your cart is empty.</p>
  <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>Product</th>
          <th>Price</th>
          <th>Qty</th>
          <th class="right">Line total</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($items as $it): ?>
          <tr>
            <td><?= h($it['name']) ?></td>
            <td>£<?= number_format($it['price'], 2) ?></td>
            <td><?= (int)$it['qty'] ?></td>
            <td class="right">£<?= number_format($it['line'], 2) ?></td>
            <td>
              <form method="post" action="/cart.php" style="margin:0;">
                <input type="hidden" name="action" value="remove">
                <input type="hidden" name="product_id" value="<?= (int)$it['id'] ?>">
                <button class="btn" type="submit">Remove</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot>
        <tr>
          <th colspan="3" class="right">Total</th>
          <th class="right">£<?= number_format($total, 2) ?></th>
          <th>
            <form method="post" action="/cart.php" style="margin:0;">
              <input type="hidden" name="action" value="clear">
              <button class="btn" type="submit">Clear</button>
            </form>
          </th>
        </tr>
      </tfoot>
    </table>
  <?php endif; ?>
</body>
</html>