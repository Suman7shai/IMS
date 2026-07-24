<?php

session_start();
require $_SERVER['DOCUMENT_ROOT'] . '/Project_IMS/includes/db.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: /Project_IMS/index.php");
  exit;
}


if ($_SERVER['REQEUEST_METHOD'] === 'POST') {
  $name = trim($_POST['name']);
  $contact_person = trim($_POST['contact_person']);
  $email = trim($_POST['email']);
  $phone = trim($_POST['phone']);
  $address = trim($_POST['address']);

  if (empty($name)) {
    $_SESSION['error'] = "Supplier name is required.";
    header("Location: add.php");
    exit;
  }


  $stmt = $pdo->prepare("INSERT INTO suppliers (name, contact_person, email, phone, address) VALUES ( ?, ?, ?, ?, ? )");
  $stmt->execute([$name, $contact_person, $email, $phone, $address]);


  $_SESSION['success'] = "Supplier added successfully";
  header("Location: list.php");
  exit;
}
?>