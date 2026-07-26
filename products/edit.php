<?php

session_start();
require $_SERVER['DOCUMENT_ROOT'] . '/Project_IMS/includes/db.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: /Project_IMS/index.php");
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id = $_POST['id'];
  $name = trim($_POST['name']);
  $description = trim($_POST['description']);
  $category_id = $_POST['category_id'];
  $price = $_POST['price'];
  $quantity = $_POST['quantity'];
  $low_stock_threshold = $_POST['low_stock_threshold'];
  $supplier_id = $_POST['supplier_id'];

  if (empty($name) || empty($price)) {
    $_SESSION['error'] = "Product name and price must be filled properly!";
    header("Location: edit.php?id=" . $id);
    exit;
  }

  $stmt = $pdo->prepare("UPDATE products SET name = ?, description = ?, category_id = ?, price = ?, quantity = ?, low_stock_threshold = ?, supplier_id = ? WHERE id = ?");

  $stmt->execute([$name, $description, $category_id, $price, $quantity, $low_stock_threshold, $supplier_id, $id]);

  $_SESSION['success'] = "Product updated successfully!";
  header("Location: list.php");
  exit;
}


if(!isset($_GET['id'])) {
  header("Location: list.php");
  exit;
}

$id = $_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();


if (!$product) {
  $_SESSION['error'] = "Product not found!";
  header("Location: list.php");
  exit;
}

$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
$suppliers = $pdo->query("SELECT * FROM suppliers ORDER BY name")->fetchAll();
?>