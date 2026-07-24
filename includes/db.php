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

} catch (PDOException $e) {
    die(json_encode([
        "success" => false,
        "message" => "DB Connection failed: " . $e->getMessage()
    ]));
}
?>