<?php
declare(strict_types=1);

/** @var PDO|null $pdo */

echo "<h1>Login</h1>";

$currentUserId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
$msg = '';
$err = '';

if (!($pdo instanceof PDO)) {
    echo '<p class="msg"><strong>Database not configured.</strong> (PDO unavailable)</p>';
    echo '<p class="muted">Login/registration requires the database connection.</p>';
    return;
}

$action = $_POST['action'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'logout') {
    unset($_SESSION['user_id']);
    header('Location: /index.php?page=login');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'register') {
    $first = trim((string)($_POST['firstname'] ?? ''));
    $last  = trim((string)($_POST['lastname'] ?? ''));
    $email = trim((string)($_POST['email'] ?? ''));

    if ($email === '' || strpos($email, '@') === false) {
        $err = "Please enter a valid email address.";
    } else {
        try {
            // basic check (users table has no unique constraint, so we guard manually)
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existing) {
                $err = "That email is already registered. Try logging in.";
            } else {
                $stmt = $pdo->prepare("INSERT INTO users (firstname, lastname, email) VALUES (?, ?, ?)");
                $stmt->execute([$first ?: null, $last ?: null, $email]);
                $newId = (int)$pdo->lastInsertId();

                $_SESSION['user_id'] = $newId;
                $currentUserId = $newId;
                $msg = "Registered and logged in as user #{$newId}.";
            }
        } catch (Throwable $e) {
            $err = "Registration failed: " . $e->getMessage();
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'login') {
    $email = trim((string)($_POST['email'] ?? ''));

    if ($email === '' || strpos($email, '@') === false) {
        $err = "Please enter a valid email address.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id, firstname, lastname, email FROM users WHERE email = ? ORDER BY id DESC LIMIT 1");
            $stmt->execute([$email]);
            $u = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$u) {
                $err = "No user found for that email. Register first.";
            } else {
                $_SESSION['user_id'] = (int)$u['id'];
                $currentUserId = (int)$u['id'];
                $msg = "Logged in as user #{$currentUserId}.";
            }
        } catch (Throwable $e) {
            $err = "Login failed: " . $e->getMessage();
        }
    }
}

// Display current user info (if logged in)
$currentUser = null;
if ($currentUserId > 0) {
    $stmt = $pdo->prepare("SELECT id, firstname, lastname, email, created_at FROM users WHERE id = ? LIMIT 1");
    $stmt->execute([$currentUserId]);
    $currentUser = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<?php if ($msg): ?>
  <div class="msg" style="border:1px solid #cfe9cf; background:#eef9ee;"><?= h($msg) ?></div>
<?php endif; ?>

<?php if ($err): ?>
  <div class="msg" style="border:1px solid #f5c2c2; background:#ffecec;"><strong>Error:</strong> <?= h($err) ?></div>
<?php endif; ?>

<?php if ($currentUserId > 0 && $currentUser): ?>
  <p class="msg">
    <strong>Logged in</strong><br>
    User ID: <?= h((string)$currentUser['id']) ?><br>
    Name: <?= h(trim(((string)$currentUser['firstname']).' '.((string)$currentUser['lastname']))) ?><br>
    Email: <?= h((string)$currentUser['email']) ?>
  </p>

  <form method="post" action="/index.php?page=login" style="margin-top:12px;">
    <input type="hidden" name="action" value="logout">
    <button class="btn" type="submit">Logout</button>
  </form>

<?php else: ?>

  <div class="card" style="max-width:520px;">
    <h2 style="margin-top:0;">Login</h2>
    <form method="post" action="/index.php?page=login">
      <input type="hidden" name="action" value="login">
      <label>Email<br>
        <input name="email" type="email" required style="width:100%; padding:8px; margin-top:6px;">
      </label>
      <div style="margin-top:10px;">
        <button class="btn" type="submit">Login</button>
      </div>
    </form>
  </div>

  <div style="height:14px;"></div>

  <div class="card" style="max-width:520px;">
    <h2 style="margin-top:0;">Register</h2>
    <form method="post" action="/index.php?page=login">
      <input type="hidden" name="action" value="register">

      <label>First name<br>
        <input name="firstname" type="text" style="width:100%; padding:8px; margin-top:6px;">
      </label>

      <div style="height:10px;"></div>

      <label>Last name<br>
        <input name="lastname" type="text" style="width:100%; padding:8px; margin-top:6px;">
      </label>

      <div style="height:10px;"></div>

      <label>Email<br>
        <input name="email" type="email" required style="width:100%; padding:8px; margin-top:6px;">
      </label>

      <div style="margin-top:10px;">
        <button class="btn" type="submit">Register</button>
      </div>
    </form>
  </div>

<?php endif; ?>

<p style="margin-top:12px;"><a class="btn" href="/index.php?page=catalogue">Back to catalogue</a></p>