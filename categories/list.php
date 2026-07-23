<?php
session_start();

require $_SERVER['DOCUMENT_ROOT'] . '/Project_IMS/includes/db.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: http://localhost:8080/Project_IMS/index.php");
  exit;
}


$categories = $pdo->query("SELECT * from categories ORDER BY created_at DESC")->fetchAll();

?>