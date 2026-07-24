<?php

session_start();
require $_SERVER['DOCUMENT_ROOT'] . '/Project_IMS/includes/db.php';

if (!isset($_SESSION['user_id'])){
  header("Location: /Project_IMS/index.php");
  exit;
}


if ($_SESSION['role'] !== 'admin') {
  $_SESSION['error'] = "Access denied!";
  header("Location: list.php");
  exit;
}

if (!isset($_GET['id'])) {
  header("Location: list.php");
  exit;
}

$id = $_GET['id'];

$stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM products WHERE supplier_id = ?");
$stmt->execute([$id]);
$result = $stmt->fetch();

if ($result['total'] > 0) {
  $_SESSION['error'] = "Cannot delete this supplier";
} else {
  $stmt = $pdo->prepare("DELETE FROM suppliers WHERE id = ?");
  $stmt->execute([$id]);
  $_SESSION['success'] = "Supplier deleted successfully!";
}


header("Location: list.php");
exit;
?>