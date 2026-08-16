<?php

session_start();

require $_SERVER['DOCUMENT_ROOT'] . '/Project_IMS/includes/db.php';

if (!isset($_SESSION['user_id'])) {
  header('Location: /Project_IMS/index.php');
  exit;
}

if ($_SESSION['role'] !== 'admin') {
  $_SESSION['error'] = "You do not have permission to access this page.";
  header("Location: /Project_IMS/dashboard.php");
  exit;
}


if (!isset($_GET['id'])) {
  header('Location: list.php');
  exit;
}

$id = $_GET['id'];


if ($id === $_SESSION['user_id']) {
  $_SESSION['error'] = "You cannot delete your own account.";
  header('Location: list.php');
  exit;
}

$stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
$stmt->execute([$id]);

$_SESSION['success'] = "User deleted successfully.";
header('Location: list.php');
exit;

?>