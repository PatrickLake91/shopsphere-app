<?php
declare(strict_types=1);
session_start();

require __DIR__ . '/db.php';

function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

$page = $_GET['page'] ?? 'catalogue';
$allowed = ['catalogue','cart','checkout','auth','orders','wishlist','logout'];

if (!in_array($page, $allowed, true)) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=utf-8');
    echo "Not found";
    exit;
}

if ($page === 'logout') {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
    }
    session_destroy();
    header('Location: /index.php?page=auth');
    exit;
}

header('Content-Type: text/html; charset=utf-8');

?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>ShopSphere</title>
</head>
<body>
  <div style="font-family:Arial; margin:24px;">
    <h1>ShopSphere Router OK</h1>
    <p>page = <?= h($page) ?></p>
    <ul>
      <li><a href="/index.php?page=catalogue">Catalogue</a></li>
      <li><a href="/index.php?page=cart">Cart</a></li>
      <li><a href="/index.php?page=orders">Orders</a></li>
      <li><a href="/index.php?page=wishlist">Wishlist</a></li>
      <li><a href="/index.php?page=auth">Login/Register</a></li>
      <li><a href="/health.php">Health</a></li>
    </ul>

    <?php
      $viewFile = __DIR__ . '/views/' . $page . '.php';
      if (!is_file($viewFile)) {
          echo "<pre>Placeholder: $viewFile not created yet.</pre>";
      } else {
          require $viewFile;
      }
    ?>
  </div>
</body>
</html>