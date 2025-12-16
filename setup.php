<?php
declare(strict_types=1);

// One-time DB schema + seed script.
// Protect with a token so it cannot be run accidentally/publicly.
$expectedToken = 'SETUP123';
$token = $_GET['token'] ?? '';
if (!hash_equals($expectedToken, $token)) {
    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');
    echo "Forbidden. Provide token.\n";
    exit;
}

header('Content-Type: text/plain; charset=utf-8');

require __DIR__ . '/db.php'; // must provide $pdo (PDO instance)

try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create tables
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(255) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS products (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(200) NOT NULL,
            description TEXT NULL,
            price DECIMAL(10,2) NOT NULL,
            image_url VARCHAR(500) NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS wishlists (
            user_id INT NOT NULL,
            product_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (user_id, product_id),
            CONSTRAINT fk_wish_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            CONSTRAINT fk_wish_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS orders (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NULL,
            total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            payment_method VARCHAR(50) NOT NULL DEFAULT 'virtual',
            payment_status VARCHAR(50) NOT NULL DEFAULT 'PAID',
            order_status VARCHAR(50) NOT NULL DEFAULT 'Processing',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS order_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT NOT NULL,
            product_id INT NOT NULL,
            qty INT NOT NULL,
            price_each DECIMAL(10,2) NOT NULL,
            CONSTRAINT fk_items_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
            CONSTRAINT fk_items_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // Seed sample products only if table is empty
    $count = (int)$pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
    if ($count === 0) {
        $stmt = $pdo->prepare("INSERT INTO products (name, description, price, image_url) VALUES (?, ?, ?, ?)");
        $products = [
            ['Azure Hoodie', 'Soft hoodie with cloud-ready comfort.', '39.99', null],
            ['DevOps Mug', 'For pipelines, pull requests, and caffeine.', '12.50', null],
            ['Wireless Mouse', 'Smooth scrolling for long build logs.', '24.95', null],
            ['USB-C Hub', 'Ports for days (almost).', '19.99', null],
            ['Notebook', 'Write down your next deployment plan.', '6.99', null],
            ['Headphones', 'Noise isolation for focused coding.', '59.00', null],
        ];
        foreach ($products as $p) { $stmt->execute($p); }
        echo "Seeded products: " . count($products) . "\n";
    } else {
        echo "Products already present (count=$count). Skipping seed.\n";
    }

    echo "SETUP_COMPLETE\n";
} catch (Throwable $e) {
    http_response_code(500);
    echo "SETUP_FAILED\n";
    echo $e->getMessage() . "\n";
}
