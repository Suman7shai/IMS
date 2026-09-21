<?php

session_start();

require $_SERVER['DOCUMENT_ROOT'] . '/Project_IMS/includes/db.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: http://Project_IMS:8080/index.php");
  exit;
}

if ($_SESSION['role'] !== 'admin') {
  $_SESSION['error'] = "You do not have permission to access this page.";
  header("Location: http://Project_IMS:8080/dashboard.php");
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
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Category | IMS</title>
  <link rel="stylesheet" href="/Project_IMS/assests/css/dashboard.css">
  <link rel="stylesheet" href="/Project_IMS/assests/css/sidebar-submenu.css">
  <link rel="stylesheet" href="/Project_IMS/products/add.css">
</head>
<body>
  <div class="dashboard-layout">
    <aside class="sidebar">
      <div class="sidebar-brand"><p class="sidebar-kicker">IMS</p><h2>Menu</h2><p>Navigate the dashboard sections faster.</p></div>
      <nav class="sidebar-nav" aria-label="Dashboard menu">
        <a href="/Project_IMS/dashboard.php">Dashboard</a>
        <div class="nav-item has-submenu">
          <button type="button" class="nav-parent" aria-expanded="false"><span>Products</span><svg class="chevron" width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></button>
          <ul class="submenu"><li><a href="/Project_IMS/products/add.php">Add Product</a></li><li><a href="/Project_IMS/products/list.php">List Products</a></li></ul>
        </div>
        <div class="nav-item has-submenu">
          <button type="button" class="nav-parent" aria-expanded="false"><span>Categories</span><svg class="chevron" width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></button>
          <ul class="submenu"><li><a href="./add.php">Add Categories</a></li><li><a href="./list.php">List Categories</a></li></ul>
        </div>
        <div class="nav-item has-submenu">
          <button type="button" class="nav-parent" aria-expanded="false"><span>Suppliers</span><svg class="chevron" width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></button>
          <ul class="submenu"><li><a href="/Project_IMS/suppliers/add.php">Add Suppliers</a></li><li><a href="/Project_IMS/suppliers/list.php">List Suppliers</a></li></ul>
        </div>
        <a href="/Project_IMS/transactions/stock_in.php">Stock In</a><a href="/Project_IMS/transactions/stock_out.php">Stock Out</a><a href="/Project_IMS/reports/index.php">Reports</a><a href="/Project_IMS/users/list.php">Users</a>
      </nav>
      <div class="sidebar-footer"><span>Live Inventory</span><strong>Stock updates instantly after sales.</strong></div>
      <a class="logout-btn" id="logoutBtn" href="/Project_IMS/auth/logout.php">Logout</a>
    </aside>
    <main class="dashboard-shell page-shell">
      <header class="dashboard-header"><div><p class="eyebrow">Inventory</p><h1>Edit Category</h1><p class="subtitle">Update the selected category details.</p></div></header>
      <section class="form-card">
        <div class="form-header"><div><p class="panel-tag">Category</p><h2>Edit Category Details</h2></div><a href="./list.php" class="secondary-btn">Back to List</a></div>
        <form method="POST" action="edit.php?id=<?= (int)$category['id'] ?>">
          <input type="hidden" name="id" value="<?= (int)$category['id'] ?>">
          <div class="form-grid">
            <div class="form-group full"><label for="name">Category Name</label><input type="text" id="name" name="name" value="<?= htmlspecialchars($category['name']) ?>" required></div>
            <div class="form-group full"><label for="description">Description</label><textarea id="description" name="description"><?= htmlspecialchars($category['description'] ?? '') ?></textarea></div>
          </div>
          <div class="form-actions"><a href="./list.php" class="secondary-btn">Cancel</a><button type="submit" class="primary-btn">Update Category</button></div>
        </form>
      </section>
    </main>
  </div>
  <script src="/Project_IMS/assests/js/dashboard.js"></script>
</body>
</html>