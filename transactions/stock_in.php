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
    header("Location: stock_in.php");
    exit;
  }

  $stmt = $pdo->prepare("
  INSERT INTO txns (product_id, type, quantity, unit_price, total_price, notes, user_id, txn_date) VALUES ( ?, 'in', ?, ?, ?, ?, ?, NOW())
  ");

  $stmt->execute([$product_id, $quantity, $unit_price, $quantity * $unit_price, $notes, $user_id]);


  // Update garna ko lagi
  $stmt = $pdo->prepare("
  UPDATE products 
  SET quantity = quantity + ? 
  WHERE id = ?
  ");

  $stmt->execute([$quantity, $product_id]);

  $_SESSION['success'] = "Stock IN recorded successfully";
  header("Location: stock_in.php");
  exit;
}


// Dropdown ko lagi
$products = $pdo->query("
  SELECT id, name, quantity, price 
  FROM products
  ORDER BY name
")->fetchAll();


?>