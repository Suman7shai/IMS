<?php
session_start();

require $_SERVER['DOCUMENT_ROOT'] . '/Project_IMS/includes/db.php';
require $_SERVER['DOCUMENT_ROOT'] . '/Project_IMS/includes/validation.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: http://localhost:8080/Project_IMS/index.php");
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $current_password = $_POST['current_password'] ?? '';
  $new_password = $_POST['new_password'] ?? '';
  $confirm_password = $_POST['confirm_password'] ?? '';

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
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Change Password | IMS</title>
  <link rel="stylesheet" href="/Project_IMS/assests/css/dashboard.css?v=<?= filemtime($_SERVER['DOCUMENT_ROOT'] . '/Project_IMS/assests/css/dashboard.css') ?>">
  <link rel="stylesheet" href="/Project_IMS/assests/css/sidebar-submenu.css">
</head>
<body>
  <div class="dashboard-layout">
    <aside class="sidebar">
      <div class="sidebar-brand">
        <p class="sidebar-kicker">IMS</p>
        <h2>Menu</h2>
        <p>Navigate the dashboard sections faster.</p>
      </div>

      <a class="sidebar-profile" href="/Project_IMS/profile/index.php">
        <span class="sidebar-profile-avatar"><?= strtoupper(substr($_SESSION['full_name'] ?? $_SESSION['username'], 0, 1)) ?></span>
        <span class="sidebar-profile-info">
          <strong><?= htmlspecialchars($_SESSION['full_name'] ?? $_SESSION['username']) ?></strong>
          <span><?= htmlspecialchars(ucfirst($_SESSION['role'] ?? 'staff')) ?></span>
        </span>
      </a>

      <nav class="sidebar-nav" aria-label="Dashboard menu">
        <a href="/Project_IMS/dashboard.php">Dashboard</a>
        <a href="/Project_IMS/products/list.php">Products</a>
        <?php if ($_SESSION['role'] === 'admin'): ?>
          <a href="/Project_IMS/categories/list.php">Categories</a>
          <a href="/Project_IMS/suppliers/list.php">Suppliers</a>
        <?php endif; ?>
        <a href="/Project_IMS/transactions/stock_in.php">Stock In</a>
        <a href="/Project_IMS/transactions/stock_out.php">Stock Out</a>
        <a href="/Project_IMS/reports/index.php">Reports</a>
        <?php if ($_SESSION['role'] === 'admin'): ?>
          <a href="/Project_IMS/users/list.php">Users</a>
        <?php endif; ?>
      </nav>

      <div class="sidebar-footer">
        <span>Live Inventory</span>
        <strong>Stock updates instantly after sales.</strong>
      </div>

      <a class="logout-btn" href="/Project_IMS/auth/logout.php">Logout</a>
    </aside>

    <main class="dashboard-shell password-page-shell">
      <header class="dashboard-header">
        <div>
          <p class="eyebrow">Account Security</p>
          <h1>Change Password</h1>
          <p class="subtitle">Update your password to keep your inventory account secure.</p>
        </div>
      </header>

      <section class="panel password-panel">
        <?php if (!empty($_SESSION['error'])): ?>
          <div class="alert error"><?= htmlspecialchars($_SESSION['error']) ?></div>
          <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        <?php if (!empty($_SESSION['success'])): ?>
          <div class="alert success"><?= htmlspecialchars($_SESSION['success']) ?></div>
          <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <div class="panel-head">
          <div>
            <p class="panel-tag">Password Settings</p>
            <h2>Set a new password</h2>
          </div>
        </div>

        <form method="POST" class="password-form">
          <label>
            Current Password
            <input type="password" name="current_password" required autocomplete="current-password">
          </label>
          <label>
            New Password
            <input type="password" name="new_password" required minlength="8" autocomplete="new-password">
          </label>
          <label>
            Confirm New Password
            <input type="password" name="confirm_password" required minlength="8" autocomplete="new-password">
          </label>
          <div class="form-actions">
            <button type="submit" class="primary-btn">Update Password</button>
            <a href="/Project_IMS/profile/index.php" class="secondary-btn">Back to Profile</a>
          </div>
        </form>
      </section>
    </main>
  </div>
  <script src="/Project_IMS/assests/js/dashboard.js"></script>
</body>
</html>