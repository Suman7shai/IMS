<?php

session_start();

require '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email']);
  $password = $_POST['password'];

  if (empty($email) || empty($password)){
    $_SESSION['error'] = 'All fields are required.';
    header("Location: /index.php");
    exit;
  }

  $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND is_active = 1");
  $stmt->execute(['email']);
  $user = $stmt->fetch();


  if($user && password_verify($password, $user['password'])){
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['role'] = $user['role'];
    header("Location: /dashboard.php");
  } else {
    $_SESSION['error'] = "Invalid email or password";
    header("Location: /index.php");
  }
  exit;
}
?>