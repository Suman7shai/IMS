<?php

session_start();
require $_SERVER['DOCUMENT_ROOT'] . '/Project_IMS/includes/db.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: /Project_IMS/index.php");
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $product_id = $_POST['product_id'];
  $quantity = (int)$_POST['quantity'];
  $unit_price = $_POST['unit_price'];
  $notes = trim($_POST['notes']);
  $user_id = $_SESSION['user_id'];

  if (empty($product_id) || empty($quantity)) {
    $_SESSION['error'] = "Product name and valid quantity must be filled properly!";
    header("Location: stock_out.php");
    exit;
  }

  $stmt = $pdo->prepare("
  SELECT name, quantity FROM products WHERE id = ?
  ");
  $stmt->execute([$product_id]);
  $product = $stmt->fetch();


  if ($product['quantity'] < $quantity) {
    $_SESSION['error'] = "Insufficient stock! Available stock: " . $product['quantity'] . " units only.";
    header("Location: stock_out.php");
    exit;
  }

  $stmt = $pdo->prepare("
  INSERT INTO txns (product_id, type, quantity, unit_price, total_price, notes, user_id, txn_date) VALUES ( ?, 'out', ?, ?, ?, ?, ?, NOW())
  ");

  $stmt->execute([$product_id, $quantity, $unit_price, $quantity * $unit_price, $notes, $user_id]);


  // Decrease product quantity
  $stmt = $pdo->prepare("
  UPDATE products SET quantity = quantity - ? WHERE id = ?
  ");
  $stmt->execute([$quantity, $product_id]);

  $_SESSION['success'] = "Stock out recorded successfully!";
  header("Location: stock_out.php");
  exit;

}

$products = $pdo->query("
SELECT id, name, quantity, price
FROM products 
ORDER BY name
")->fetchAll();

?>