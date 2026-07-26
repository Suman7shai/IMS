<?php

session_start();
require $_SERVER['DOCUMENT_ROOT'] . '/Project_IMS/includes/db.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: /Project_IMS/index.php");
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['name']);
  $description = trim($_POST['description']);
  $category_id = $_POST['category_id'];
  $price = $_POST['price'];
  $quantity = $_POST['quantity'];
  $low_stock_threshold = $_POST['low_stock_threshold'];
  $supplier_id = $_POST['supplier_id'];


  if(empty($name) || empty($price)) {
    $_SESSION['error'] = "Product name and price must be filled properly!";
    header("Location: add.php");
    exit;
  }

  $stmt = $pdo->prepare("INSERT INTO products(name, description, category_id, price, quantity, low_stock_threhold, supplier_id) VALUES (?, ?, ?, ?, ?, ?, ?)");

  $stmt->execute([$name, $description, $category_id, $price, $quantity, $low_stock_threshold, $supplier_id]);

  $_SESSION['success'] = "Products added successfully!";
  header("Location: list.php");
  exit;
}

// for Dropdowns
$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
$suppliers = $pdo->query("SELECT * FROM suppliers ORDER BY name")->fetchAll();

?>