<?php

session_start();
require $_SERVER['DOCUMENT_ROOT'] . '/Project_IMS/includes/db.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: http://localhost:8080/Project_IMS/index.php");
  exit;
}

if ($_SESSION['role'] !== 'admin') {
  $_SESSION['error'] = "Access Denied!";
  header("Location: list.php");
  exit;
}

if (!isset($_GET['id'])) {
  header("Location: list.php");
  exit;
}

$id = $_GET['id'];


$stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM products WHERE category = ?");
$stmt->execute([$id]);
$result = $stmt->fetch();


if ($result['total'] > 0) {
  $_SESSION['error'] = "Cannot delete products exist under this category!";
} else {
  $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
  $stmt->execute([$id]);
  $_SESSION['success'] = "Categories deleted successfully!";
}

header("Location: list.php");
exit;

?>