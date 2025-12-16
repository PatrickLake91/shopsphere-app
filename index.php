<?php
declare(strict_types=1);
session_start();

require __DIR__ . '/db.php';

function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

$page = $_GET['page'] ?? 'catalogue';
$allowed = ['catalogue','cart','checkout','orders','order','wishlist','health'];

if (!in_array($page, $allowed, true)) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=utf-8');
    echo "Not found";
    exit;
}

?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>ShopSphere</title>
  <style>
    body { font-family: Arial, sans-serif; margin: 24px; }
    .top { display:flex; gap:12px; align-items:center; margin-bottom:16px; flex-wrap:wrap; }
    .top a { text-decoration:none; padding:8px 10px; border:1px solid #ccc; border-radius:10px; color:#000; }
    .grid { display:grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap:14px; }
    .card { border:1px solid #ddd; border-radius:14px; padding:14px; }
    .muted { color:#666; font-size:0.95em; }
    .btn { padding:8px 10px; border-radius:10px; border:1px solid #333; background:#fff; cursor:pointer; }
    table { border-collapse: collapse; width: 100%; margin-top: 12px; }
    th, td { border: 1px solid #ccc; padding: 10px; text-align:left; }
    .msg { padding:10px; border-radius:10px; background:#f3f3f3; margin:10px 0; }
  </style>
</head>
<body>

<div class="top">
  <strong>ShopSphere</strong>
  <a href="/index.php?page=catalogue">Catalogue</a>
  <a href="/index.php?page=cart">Cart</a>
  <a href="/index.php?page=checkout">Checkout</a>
  <a href="/index.php?page=orders">Orders</a>
  <a href="/index.php?page=wishlist">Wishlist</a>
  <a href="/health.php">Health</a>
</div>

<?php
$viewFile = __DIR__ . '/views/' . $page . '.php';
if (!is_file($viewFile)) {
    echo '<div class="msg"><strong>Placeholder:</strong> ' . h($viewFile) . ' not created yet.</div>';
} else {
    require $viewFile;
}
?>

</body>
</html>