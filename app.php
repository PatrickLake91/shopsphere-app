<?php
require_once __DIR__ . "/db.php";

$stmt = $pdo->query("SELECT id, productname, price FROM products ORDER BY id ASC");
$products = $stmt->fetchAll();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Product List</title>
  <style>
    body { font-family: Arial, sans-serif; margin: 40px; }
    table { border-collapse: collapse; width: 60%; }
    th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
    th { background: #f4f4f4; }
  </style>
</head>
<body>
  <h1>Products</h1>
  <table>
    <tr><th>ID</th><th>Product</th><th>Price (£)</th></tr>
    <?php foreach ($products as $p): ?>
      <tr>
        <td><?= htmlspecialchars((string)$p['id']) ?></td>
        <td><?= htmlspecialchars((string)$p['productname']) ?></td>
        <td><?= number_format((float)$p['price'], 2) ?></td>
      </tr>
    <?php endforeach; ?>
  </table>
</body>
</html>