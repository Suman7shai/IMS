<?php
session_start();

require $_SERVER['DOCUMENT_ROOT'] . '/Project_IMS/includes/db.php';
require $_SERVER['DOCUMENT_ROOT'] . '/Project_IMS/includes/validation.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: http://localhost:8080/Project_IMS/index.php");
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $current_password = $_POST['current_password'];
  $new_password = $_POST['new_password'];
  $confirm_password = $_POST['confirm_password'];

  if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
    $_SESSION['error'] = "All fields are required.";
    header("Location: change_password.php");
    exit;
  }

  if ($new_password !== $confirm_password) {
    $_SESSION['error'] = "New Password and Confirm Password do not match.";
    header("Location: change_password.php");
    exit;
  }

  if (!is_valid_password($new_password)) {
    $_SESSION['error'] = "Password must be 8-72 characters and include at least one letter and one number.";
    header("Location: change_password.php");
    exit;
  }

  $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
  $stmt->execute([$_SESSION['user_id']]);
  $user = $stmt->fetch();

  if (!password_verify($current_password, $user['password'])) {
    $_SESSION['error'] = "Current Password is incorrect.";
    header("Location: change_password.php");
    exit;
  }

  $hashed = password_hash($new_password, PASSWORD_BCRYPT);

  $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
  $stmt->execute([$hashed, $_SESSION['user_id']]);

  $_SESSION['success'] = "Password changed successfully.";
  header("Location: change_password.php");
  exit;
}
?>