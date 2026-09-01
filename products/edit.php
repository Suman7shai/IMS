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
  $description = trim($_POST['description']);
  $category_id = $_POST['category_id'] ?? null;
  $price = $_POST['price'];
  $quantity = $_POST['quantity'] ?? 0;
  $low_stock_threshold = $_POST['low_stock_threshold'] ?? 10;
  $supplier_id = $_POST['supplier_id'] ?? null;

  if ($category_id === '' || $category_id === '0') {
    $category_id = null;
  }

  if ($supplier_id === '' || $supplier_id === '0') {
    $supplier_id = null;
  }

  if (empty($name) || $price === '' || $price === null) {
    $_SESSION['error'] = "Product name and price must be filled properly!";
    header("Location: edit.php?id=" . $id);
    exit;
  }

  $stmt = $pdo->prepare(
    "UPDATE products SET name = ?, description = ?, category_id = ?, price = ?, quantity = ?, low_stock_threshold = ?, supplier_id = ? WHERE id = ?"
  );

  $stmt->execute([$name, $description, $category_id, $price, $quantity, $low_stock_threshold, $supplier_id, $id]);

  $_SESSION['success'] = "Product updated successfully!";
  header("Location: list.php");
  exit;
}

if (!isset($_GET['id'])) {
  header("Location: list.php");
  exit;
}

$id = $_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
  $_SESSION['error'] = "Product not found!";
  header("Location: list.php");
  exit;
}

$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
$suppliers = $pdo->query("SELECT * FROM suppliers ORDER BY name")->fetchAll();
$successMessage = $_SESSION['success'] ?? '';
$errorMessage = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product | IMS</title>
    <link rel="stylesheet" href="/Project_IMS/assests/css/dashboard.css">
    <link rel="stylesheet" href="/Project_IMS/assests/css/sidebar-submenu.css">
    <link rel="stylesheet" href="/Project_IMS/products/add.css">
</head>
<body>
    <div class="dashboard-layout">
        <aside class="sidebar">
            <div class="sidebar-brand">
                <p class="sidebar-kicker">IMS</p>
                <h2>Menu</h2>
                <p>Navigate the dashboard sections faster.</p>
            </div>

            <nav class="sidebar-nav" aria-label="Dashboard menu">
                <a href="/Project_IMS/dashboard.php">Dashboard</a>
                <div class="nav-item has-submenu">
                    <button type="button" class="nav-parent" aria-expanded="false">
                        <span>Products</span>
                        <svg class="chevron" width="14" height="14" viewBox="0 0 24 24" fill="none">
                            <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                    <ul class="submenu">
                        <li><a href="./add.php">Add Product</a></li>
                        <li><a href="./list.php">List Products</a></li>
                    </ul>
                </div>
                <div class="nav-item has-submenu">
                    <button type="button" class="nav-parent" aria-expanded="false">
                        <span>Categories</span>
                        <svg class="chevron" width="14" height="14" viewBox="0 0 24 24" fill="none">
                            <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                    <ul class="submenu">
                        <li><a href="/Project_IMS/categories/add.php">Add Categories</a></li>
                        <li><a href="/Project_IMS/categories/list.php">List Categories</a></li>
                    </ul>
                </div>
                <div class="nav-item has-submenu">
                    <button type="button" class="nav-parent" aria-expanded="false">
                        <span>Suppliers</span>
                        <svg class="chevron" width="14" height="14" viewBox="0 0 24 24" fill="none">
                            <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                    <ul class="submenu">
                        <li><a href="/Project_IMS/suppliers/add.php">Add Suppliers</a></li>
                        <li><a href="/Project_IMS/suppliers/list.php">List Suppliers</a></li>
                    </ul>
                </div>
                <a href="/Project_IMS/transactions/stock_in.php">Stock In</a>
                <a href="/Project_IMS/transactions/stock_out.php">Stock Out</a>
                <a href="/Project_IMS/reports/index.php">Reports</a>
                <a href="/Project_IMS/users/list.php">Users</a>
            </nav>

            <div class="sidebar-footer">
                <span>Live Inventory</span>
                <strong>Stock updates instantly after sales.</strong>
            </div>

            <button type="button" class="logout-btn" id="logoutBtn">Logout</button>
        </aside>

        <div class="dashboard-shell page-shell">
            <header class="dashboard-header">
                <div>
                    <p class="eyebrow">Inventory</p>
                    <h1>Edit Product</h1>
                    <p class="subtitle">Update the selected product with the latest price, stock, and supplier information.</p>
                </div>
            </header>

            <?php if ($successMessage): ?>
                <div class="alert success"><?= htmlspecialchars($successMessage) ?></div>
            <?php endif; ?>

            <?php if ($errorMessage): ?>
                <div class="alert error"><?= htmlspecialchars($errorMessage) ?></div>
            <?php endif; ?>

            <section class="form-card">
                <div class="form-header">
                    <div>
                        <p class="panel-tag">Product</p>
                        <h2>Update Product Details</h2>
                    </div>
                    <a href="./list.php" class="secondary-btn">Back to List</a>
                </div>

                <form method="POST" action="edit.php?id=<?= (int)$product['id'] ?>">
                    <input type="hidden" name="id" value="<?= (int)$product['id'] ?>">

                    <div class="form-grid">
                        <div class="form-group full">
                            <label for="name">Product Name</label>
                            <input type="text" id="name" name="name" value="<?= htmlspecialchars($product['name']) ?>" required>
                        </div>

                        <div class="form-group full">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" placeholder="Short product description"><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="category_id">Category</label>
                            <select id="category_id" name="category_id">
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= (int)$category['id'] ?>" <?= (string)$product['category_id'] === (string)$category['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($category['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="supplier_id">Supplier</label>
                            <select id="supplier_id" name="supplier_id">
                                <option value="">Select Supplier</option>
                                <?php foreach ($suppliers as $supplier): ?>
                                    <option value="<?= (int)$supplier['id'] ?>" <?= (string)$product['supplier_id'] === (string)$supplier['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($supplier['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="price">Price</label>
                            <input type="number" id="price" name="price" min="0" step="0.01" value="<?= htmlspecialchars((string)$product['price']) ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="quantity">Quantity</label>
                            <input type="number" id="quantity" name="quantity" min="0" step="1" value="<?= (int)$product['quantity'] ?>">
                        </div>

                        <div class="form-group full">
                            <label for="low_stock_threshold">Low Stock Threshold</label>
                            <input type="number" id="low_stock_threshold" name="low_stock_threshold" min="0" step="1" value="<?= (int)$product['low_stock_threshold'] ?>">
                        </div>
                    </div>

                    <div class="form-actions">
                        <a href="./list.php" class="secondary-btn">Cancel</a>
                        <button type="submit" class="primary-btn">Update Product</button>
                    </div>
                </form>
            </section>
        </div>
    </div>

    <script src="/Project_IMS/assests/js/dashboard.js"></script>
</body>
</html>
