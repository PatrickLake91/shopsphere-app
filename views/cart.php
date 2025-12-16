<?php
declare(strict_types=1);

/** @var PDO $pdo */
/** h() is defined in index.php */

if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = []; // product_id => qty
}

# Handle actions (add/remove/clear)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $pid    = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;

    if ($action === 'add' && $pid > 0) {
        $_SESSION['cart'][$pid] = (int)($_SESSION['cart'][$pid] ?? 0) + 1;
    }

    if ($action === 'remove' && $pid > 0) {
        if (isset($_SESSION['cart'][$pid])) {
            $_SESSION['cart'][$pid]--;
            if ($_SESSION['cart'][$pid] <= 0) { unset($_SESSION['cart'][$pid]); }
        }
    }

    if ($action === 'clear') {
        $_SESSION['cart'] = [];
    }
}

$cart = $_SESSION['cart'];
$items = [];
$total = 0.0;

if (!empty($cart)) {
    $ids = array_keys($cart);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));

    try {
        $stmt = $pdo->prepare("SELECT id, productname, price FROM products WHERE id IN ($placeholders)");
        $stmt->execute($ids);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $byId = [];
        foreach ($rows as $r) { $byId[(int)$r['id']] = $r; }

        foreach ($cart as $id => $qty) {
            $id = (int)$id;
            $qty = (int)$qty;
            if (!isset($byId[$id])) { continue; }
            $name = (string)$byId[$id]['productname'];
            $price = (float)$byId[$id]['price'];
            $line = $price * $qty;
            $total += $line;
            $items[] = ['id'=>$id,'name'=>$name,'price'=>$price,'qty'=>$qty,'line'=>$line];
        }
    } catch (Throwable $e) {
        echo '<div class="msg" style="border:1px solid #f5c2c7;background:#f8d7da;">';
        echo '<strong>Cart view error:</strong> ' . h($e->getMessage());
        echo '</div>';
    }
}
?>

<div class="top">
  <strong>ShopSphere</strong>
  <a href="/index.php?page=catalogue">Back to products</a>
  <a href="/index.php?page=checkout">Checkout</a>
</div>

<h1>Your Cart</h1>

<?php if (empty($items)): ?>
  <p>Your cart is empty.</p>
<?php else: ?>
  <table style="border-collapse:collapse;width:100%;margin-top:12px;">
    <thead>
      <tr>
        <th style="border:1px solid #ccc;padding:10px;text-align:left;">Product</th>
        <th style="border:1px solid #ccc;padding:10px;text-align:left;">Qty</th>
        <th style="border:1px solid #ccc;padding:10px;text-align:left;">Price</th>
        <th style="border:1px solid #ccc;padding:10px;text-align:left;">Line</th>
        <th style="border:1px solid #ccc;padding:10px;text-align:left;">Action</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($items as $it): ?>
        <tr>
          <td style="border:1px solid #ccc;padding:10px;"><?= h($it['name']) ?></td>
          <td style="border:1px solid #ccc;padding:10px;"><?= (int)$it['qty'] ?></td>
          <td style="border:1px solid #ccc;padding:10px;">£<?= number_format((float)$it['price'], 2) ?></td>
          <td style="border:1px solid #ccc;padding:10px;">£<?= number_format((float)$it['line'], 2) ?></td>
          <td style="border:1px solid #ccc;padding:10px;">
            <form method="post" action="/index.php?page=cart" style="margin:0;display:inline;">
              <input type="hidden" name="action" value="remove">
              <input type="hidden" name="product_id" value="<?= (int)$it['id'] ?>">
              <button class="btn" type="submit">Remove one</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <div class="msg" style="margin-top:12px;">
    <strong>Total:</strong> £<?= number_format($total, 2) ?>
  </div>

  <form method="post" action="/index.php?page=cart" style="margin-top:10px;">
    <input type="hidden" name="action" value="clear">
    <button class="btn" type="submit">Clear cart</button>
  </form>
<?php endif; ?>