<?php

session_start();
require $_SERVER['DOCUMENT_ROOT'] . '/Project_IMS/includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  header('Location: /Project_IMS/index.php');
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $full_name = trim($_POST['full_name']);
  $username = trim($_POST['username']);
  $email = trim($_POST['email']);
  $password = $_POST['password'];
  $role = $_POST['role'];

  if (empty($full_name) || empty($username) || empty($email) || empty($password) || empty($role)) {
    $_SESSION['error'] = 'All fields are required.';
    header("Location: /Project_IMS/users/add.php");
    exit;
  }

  $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
  $stmt->execute([$username]);
  if($stmt->fetch()) {
    $_SESSION['error'] = 'Username already exists.';
    header("Location: /Project_IMS/users/add.php");
    exit;
  }


  $hashed = password_hash($password, PASSWORD_BCRYPT);

  $stmt = $pdo->prepare("
    INSERT INTO users (full_name, username, email, password, role)
    VALUES (?, ?, ?, ?, ?)
  ");
  $stmt->execute([$full_name, $username, $email, $hashed, $role]);

  $_SESSION['success'] = 'User added successfully.';
  header("Location: /Project_IMS/users/list.php");
  exit;
}

?>