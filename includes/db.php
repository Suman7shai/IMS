<?php
$db_host = "localhost";
$db_name = "inventory_management_system";
$db_username = "root";
$db_password = "12345678";

try {
    $pdo = new PDO(
        "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4",
        $db_username,
        $db_password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );

    $columns = $pdo->query("SHOW COLUMNS FROM products")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('buy_price', $columns, true)) {
        $pdo->exec("ALTER TABLE products ADD COLUMN buy_price DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER price");
        $pdo->exec("UPDATE products SET buy_price = price WHERE buy_price = 0");
    }
    if (!in_array('sale_price', $columns, true)) {
        $pdo->exec("ALTER TABLE products ADD COLUMN sale_price DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER buy_price");
        $pdo->exec("UPDATE products SET sale_price = price WHERE sale_price = 0");
    }

} catch (PDOException $e) {
    die(json_encode([
        "success" => false,
        "message" => "DB Connection failed: " . $e->getMessage()
    ]));
}
?>