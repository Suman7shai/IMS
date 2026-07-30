<?php

session_start();
require $_SERVER['DOCUMENT_ROOT'] . '/Project_IMS/includes/db.php';


if (!isset($_SESSION['user_id'])) {
  header("Location: /Project_IMS/index.php");
  exit;
}

$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$low_stock = $_GET['low_stock'] ?? '';

$query = "
  SELECT p.*,
          c.name as category_name,
          s.name as supplier_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    LEFT JOIN suppliers s ON p.supplier_id = s.id
    WHERE 1 = 1
";
$params = [];


// Search filter ko lagi
if(!empty($search)) {
  $query .= " AND (p.name like ? OR p.description like ?)";
  $params[] = "%$search%";
  $params[] = "%$search%";
}


// Categories filter ko lagi
if(!empty($category)) {
  $query .= " AND p.category_id = ?";
  $params[] = $category;
}


// low stock filter ko lagi
if($low_stock === 'yes') {
  $query .= " AND p.quantity <= p.low_stock_threshold";
}

$query .= " ORDER BY p.id desc";

$stmt = $pdo->prepare($query);
$stmt->execute($params);

$products = $stmt->fetchAll();


// Categories dropdown ko lagi
$categories = $pdo->query("SELECT * from categories ORDER BY name")->fetchAll();
?>

