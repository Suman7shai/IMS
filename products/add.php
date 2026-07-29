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
<?php
$editId = isset($_GET['edit']) ? trim($_GET['edit']) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add / Edit Product</title>
</head>
<body>
    <main class="page-shell">
        
    </main>
    <script>
        const editId = `<?= json_encode($editId); ?>`;
    </script>
    <script src="/Project_IMS/assests/js/add.js"></script>
    <script src="/Project_IMS/assests/js/dashboard.js"></script>
</body>
</html>