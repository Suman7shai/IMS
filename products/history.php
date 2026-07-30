<?php

session_start();
require $_SERVER['DOCUMENT_ROOT'] . '/Project_IMS/includes/db.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: /Project_IMS/index.php");
  exit;
}

if (!isset($_GET['id'])) {
  header("Location: list.php");
  exit;
}

$product_id = $_GET['id'];

$stmt = $pdo->prepare("
  SELECT p.*,
          c.name as category_name,
          s.name as supplier_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    LEFT JOIN suppliers s ON p.supplier_id = s.id
    WHERE p.id = ?
");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!product) {
  $_SESSION['error'] = "Product not found!";
  header("Location: list.php");
  exit;
}

// Transaction fetch ko lagi
$stmt = $pdo->prepare("
  SELECT t.*,
          u.full_name as user_name
    FROM transactions t
    LEFT JOIN users u ON t.user_id = u.id
    WHERE t.product_id = ?
    ORDER BY t.txn_date DESC
");
$stmt->execute([$product_id]);
$transactions = $stmt->fetchAll();

$total_in = 0;
$total_out = 0;

foreach ($transactions as $txn) {
  if ($txn['txn_type'] === 'in') {
    $total_in += $txn['quantity'];
  } else {
    $total_out += $txn['quantity'];
  }
}

?>