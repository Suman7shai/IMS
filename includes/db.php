<?php
$host = "localhost";
$dbname = "inventory_management_system";
$user = "root";
$password = "12345678";

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $user,
        $password,
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