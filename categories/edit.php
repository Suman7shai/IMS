<?php

session_start();

require $_SERVER['DOCUMENT_ROOT'] . '/Project_IMS/includes/db.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: http://Project_IMS:8080/index.php");
  exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id = $_POST['id'];
  $name = trim($_POST['name']);
  $description = trim($_POST['description']);

  if (empty($name)) {
    $_SESSION['error'] = "Category name is required.";
    header("Location: edit.php?id=" . $id);
    exit;
  }


  $stmt = $pdo->prepare("UPDATE categories SET name = ?, description = ? where id = ?");
  $stmt->execute([$name, $description, $id]);


  $_SESSION['success'] = "Category updated successfully.";
  header("Location: list.php");
  exit;
}


if (!isset($_GET['id'])) {
  header("Location: list.php");
  exit;
}


$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
$stmt->execute([$id]);
$category = $stmt->fetch();


if (!$category) {
  $_SESSION['error'] = "Category not found";
  header("Location: list.php");
  exit;
}

?>