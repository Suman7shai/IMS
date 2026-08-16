<?php

session_start();
require $_SERVER['DOCUMENT_ROOT'] . '/Project_IMS/includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  header('Location: /Project_IMS/index.php');
  exit;
}

if ($_SESSION['role'] !== 'admin') {
  $_SESSION['error'] = "You do not have permission to access this page.";
  header("Location: /Project_IMS/dashboard.php");
  exit;
}

$users = $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();
?>