<?php

session_start();
require $_SERVER['DOCUMENT_ROOT'] . '/Project_IMS/includes/db.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: /Project_IMS/index.php");
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id = $_POST['id'];
  $name = trim($_POST['name']);
  $contact_person = trim($_POST['contact_person']);
  $email = trim($_POST['email']);
  $phone = trim($_POST['phone']);
  $address = trim($_POST['address']);
  
  if (empty($name)) {
    $_SESSION['error'] = "Supplier name is required.";
    header("Location: edit.php");
    exit;
  }

  $stmt = $pdo->prepare("UPDATE suppliers SET name = ?, contact_person = ?, email = ?, phone = ?, address = ? WHERE id = ?");
  $stmt->execute([$name, $contact_person, $email, $phone, $address, $id]);


  $_SESSION['success'] = "Suppliers details updated successfully";
  header("Location: edit.php");
  exit;
}


if(!isset($_GET['id'])) {
  header("Location: list.php");
  exit;
}


$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM suppliers WHERE id = ?");
$stmt->execute([$id]);
$supplier = $stmt->fetch();


if(!$supplier) {
  $_SESSION['error'] = "Supplier not found.";
  header("Location: list.php");
  exit;
}
?>