<?php

session_start();

require $_SERVER['DOCUMENT_ROOT'] . '/Project_IMS/includes/db.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: http://localhost:8080/Project_IMS/index.php");
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['name']);
  $description = trim($_POST['description']);


  if (empty($name)) {
    $_SESSION['error'] = "Category name is required!!!";
    header("Location: add.php");
    exit;
  }

  $stmt = $pdo->prepare("INSERT INTO categories (name, description) VALUES ( ?, ? )");
  $stmt->execute([$name, $description]);

  $_SESSION['success'] = "Categories added successfully";
  header("Location: list.php");
  exit;
}
?>