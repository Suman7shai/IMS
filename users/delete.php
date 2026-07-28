<?php

session_start();

require $_SERVER['DOCUMENT_ROOT'] . '/Project_IMS/includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  header('Location: /Project_IMS/index.php');
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