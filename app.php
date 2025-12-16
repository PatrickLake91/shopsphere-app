<?php
declare(strict_types=1);
require __DIR__ . '/db.php';

$stmt = $pdo->query("SELECT id, name, price FROM products ORDER BY id ASC");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Products</title>
  <style>
    body { font-family: Arial, sans-serif; margin: 24px; }
    table { border-collapse: collapse; width: 100%; }
    th, td { border: 1px solid #ccc; padding: 10px; text-align:left; }
  </style>
</head>
<body>
<h1>Products</h1>
<table>
  <thead><tr><th>ID</th><th>Product</th><th>Price (£)</th></tr></thead>
  <tbody>
    <?php foreach ($rows as $r): ?>
      <tr>
        <td><?= (int)$r['id'] ?></td>
        <td><?= htmlspecialchars((string)$r['name'], ENT_QUOTES, 'UTF-8') ?></td>
        <td><?= number_format((float)$r['price'], 2) ?></td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
</body>
</html>