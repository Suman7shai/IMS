<?php

session_start();
require $_SERVER['DOCUMENT_ROOT'] . '/Project_IMS/includes/db.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: /Project_IMS/index.php");
  exit;
}

$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$low_stock = $_GET['low_stock'] ?? '';

$query = "
  SELECT p.*, c.name AS category_name, s.name AS supplier_name
  FROM products p
  LEFT JOIN categories c ON p.category_id = c.id
  LEFT JOIN suppliers s ON p.supplier_id = s.id
  WHERE 1 = 1
";
$params = [];

if (!empty($search)) {
  $query .= " AND (p.name LIKE ? OR p.description LIKE ?)";
  $params[] = "%$search%";
  $params[] = "%$search%";
}

if (!empty($category)) {
  $query .= " AND p.category_id = ?";
  $params[] = $category;
}

if ($low_stock === 'yes') {
  $query .= " AND p.quantity <= p.low_stock_threshold";
}

$query .= " ORDER BY p.id DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll();

$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();

$successMessage = $_SESSION['success'] ?? '';
$errorMessage = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product List | IMS</title>
    <link rel="stylesheet" href="/Project_IMS/assests/css/dashboard.css">
    <link rel="stylesheet" href="/Project_IMS/products/list.css">
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
                        <li><a href="./edit.php">Edit Product</a></li>
                        <li><a href="./delete.php">Delete Product</a></li>
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
                        <li><a href="/Project_IMS/categories/edit.php">Edit Categories</a></li>
                        <li><a href="/Project_IMS/categories/delete.php">Delete Categories</a></li>
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
                        <li><a href="/Project_IMS/suppliers/edit.php">Edit Suppliers</a></li>
                        <li><a href="/Project_IMS/suppliers/delete.php">Delete Suppliers</a></li>
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

        <div class="dashboard-shell list-page-shell">
            <header class="dashboard-header">
                <div>
                    <p class="eyebrow">Inventory</p>
                    <h1>Products</h1>
                    <p class="subtitle">Manage your product list, stock levels, and quick actions from one place.</p>
                </div>

                <div class="header-metrics">
                    <div class="metric-card">
                        <span>Total Products</span>
                        <strong><?= count($products) ?></strong>
                    </div>
                    <div class="metric-card">
                        <span>Low Stock</span>
                        <strong>
                            <?= count(array_filter($products, function ($product) {
                                return (int)$product['quantity'] <= (int)$product['low_stock_threshold'];
                            })) ?>
                        </strong>
                    </div>
                </div>
            </header>

            <section class="toolbar">
                <form method="GET" class="toolbar-form">
                    <label>
                        Search Product
                        <input type="search" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Type product name or description...">
                    </label>

                    <label>
                        Category
                        <select name="category">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $categoryItem): ?>
                                <option value="<?= (int)$categoryItem['id'] ?>" <?= $category == $categoryItem['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($categoryItem['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>

                    <label>
                        Low Stock
                        <select name="low_stock">
                            <option value="">All</option>
                            <option value="yes" <?= $low_stock === 'yes' ? 'selected' : '' ?>>Only low stock</option>
                        </select>
                    </label>

                    <button type="submit" class="primary-btn">Filter</button>
                </form>
            </section>

            <?php if ($successMessage): ?>
                <div class="alert success"><?= htmlspecialchars($successMessage) ?></div>
            <?php endif; ?>

            <?php if ($errorMessage): ?>
                <div class="alert error"><?= htmlspecialchars($errorMessage) ?></div>
            <?php endif; ?>

            <section class="panel-table">
                <div class="panel-head">
                    <div>
                        <p class="panel-tag">Products</p>
                        <h2>All Product List</h2>
                    </div>
                    <a href="./add.php" class="primary-btn">Add Product</a>
                </div>

                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Category</th>
                                <th>Supplier</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($products)): ?>
                                <tr>
                                    <td colspan="7" class="empty-state">No product found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($products as $product): ?>
                                    <?php
                                        $quantity = (int)$product['quantity'];
                                        $threshold = (int)$product['low_stock_threshold'];
                                        $statusClass = 'in-stock';
                                        $statusLabel = 'In Stock';

                                        if ($quantity <= 0) {
                                            $statusClass = 'out-of-stock';
                                            $statusLabel = 'Out of Stock';
                                        } elseif ($quantity <= $threshold) {
                                            $statusClass = 'low-stock';
                                            $statusLabel = 'Low Stock';
                                        }
                                    ?>
                                    <tr>
                                        <td>
                                            <div class="product-name"><?= htmlspecialchars($product['name']) ?></div>
                                            <div class="product-desc"><?= htmlspecialchars($product['description'] ?: 'No description') ?></div>
                                        </td>
                                        <td><?= htmlspecialchars($product['category_name'] ?? 'Uncategorized') ?></td>
                                        <td><?= htmlspecialchars($product['supplier_name'] ?? 'N/A') ?></td>
                                        <td class="price">Rs. <?= number_format((float)$product['price'], 2) ?></td>
                                        <td>
                                            <span class="stock-box <?= $quantity <= $threshold ? 'low' : '' ?>"><?= $quantity ?></span>
                                        </td>
                                        <td>
                                            <span class="status-badge <?= $statusClass ?>"><?= $statusLabel ?></span>
                                        </td>
                                        <td>
                                            <div class="action-group">
                                                <a href="./edit.php?id=<?= (int)$product['id'] ?>" class="action-btn edit-btn">Edit</a>
                                                <a href="./delete.php?id=<?= (int)$product['id'] ?>" class="action-btn delete-btn" onclick="return confirm('Are you sure you want to delete this product?');">Delete</a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>

    <script src="/Project_IMS/assests/js/dashboard.js"></script>
</body>
</html>


