<?php
declare(strict_types=1);

if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$action = $_POST['action'] ?? null;
$productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : null;

if ($action === 'add' && $productId) {
    $_SESSION['cart'][$productId] = ($_SESSION['cart'][$productId] ?? 0) + 1;
    header('Location: /index.php?page=cart');
    exit;
}

if ($action === 'remove' && $productId) {
    unset($_SESSION['cart'][$productId]);
    header('Location: /index.php?page=cart');
    exit;
}

$ids = array_keys($_SESSION['cart']);
$items = [];
$total = 0.0;

if ($ids) {
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT id, productname, price FROM products WHERE id IN ($placeholders)");
    $stmt->execute($ids);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $byId = [];
    foreach ($rows as $r) { $byId[(int)$r['id']] = $r; }

    foreach ($ids as $id) {
        if (!isset($byId[$id])) { continue; }
        $qty = (int)$_SESSION['cart'][$id];
        $price = (float)$byId[$id]['price'];
        $line = $qty * $price;
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
?>

<h1>Your Cart</h1>

<p>
  <a class="btn" href="/index.php?page=catalogue">Back to products</a>
  <a class="btn" href="/index.php?page=checkout">Checkout</a>
</p>

<?php if (!$items): ?>
  <p>Your cart is empty.</p>
<?php else: ?>
  <table>
    <thead>
      <tr><th>Product</th><th>Price</th><th>Qty</th><th>Line total</th><th>Action</th></tr>
    </thead>
    <tbody>
      <?php foreach ($items as $it): ?>
        <tr>
          <td><?= h($it['name']) ?></td>
          <td>£<?= number_format($it['price'], 2) ?></td>
          <td><?= (int)$it['qty'] ?></td>
          <td>£<?= number_format($it['line'], 2) ?></td>
          <td>
            <form method="post" action="/index.php?page=cart" style="margin:0;">
              <input type="hidden" name="action" value="remove">
              <input type="hidden" name="product_id" value="<?= (int)$it['id'] ?>">
              <button class="btn" type="submit">Remove</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <p><strong>Total: £<?= number_format($total, 2) ?></strong></p>
<?php endif; ?>