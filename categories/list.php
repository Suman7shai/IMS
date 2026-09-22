<?php
session_start();
require $_SERVER['DOCUMENT_ROOT'] . '/Project_IMS/includes/db.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: /Project_IMS/index.php");
  exit;
}

if ($_SESSION['role'] !== 'admin') {
    header("Location: /Project_IMS/dashboard.php");
    exit;
}

$categories = $pdo->query("SELECT * FROM categories ORDER BY created_at DESC")->fetchAll();

$successMessage = $_SESSION['success'] ?? '';
$errorMessage = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories | IMS</title>
    <link rel="stylesheet" href="/Project_IMS/assests/css/dashboard.css">
    <link rel="stylesheet" href="/Project_IMS/assests/css/sidebar-submenu.css">
    <link rel="stylesheet" href="/Project_IMS/assests/css/categories.css">
</head>
<body>
    <div class="dashboard-layout">
        <aside class="sidebar">
            <div class="sidebar-brand">
                <p class="sidebar-kicker">IMS</p>
                <h2>Menu</h2>
                <p>Navigate the dashboard sections faster.</p>
            </div>
            <?php require $_SERVER['DOCUMENT_ROOT'] . '/Project_IMS/includes/sidebar_profile.php'; ?>
            <nav class="sidebar-nav" aria-label="Dashboard menu">
                <a href="/Project_IMS/dashboard.php">Dashboard</a>
                <div class="nav-item has-submenu">
                    <button type="button" class="nav-parent" aria-expanded="false"><span>Products</span><svg class="chevron" width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></button>
                    <ul class="submenu">
                        <li><a href="/Project_IMS/products/add.php">Add Product</a></li>
                        <li><a href="/Project_IMS/products/list.php">List Products</a></li>
                    </ul>
                </div>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                <div class="nav-item has-submenu">
                    <button type="button" class="nav-parent" aria-expanded="false"><span>Categories</span><svg class="chevron" width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></button>
                    <ul class="submenu">
                        <li><a href="/Project_IMS/categories/add.php">Add Categories</a></li>
                        <li><a href="/Project_IMS/categories/list.php">List Categories</a></li>
                    </ul>
                </div>
                <div class="nav-item has-submenu">
                    <button type="button" class="nav-parent" aria-expanded="false"><span>Suppliers</span><svg class="chevron" width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></button>
                    <ul class="submenu">
                        <li><a href="/Project_IMS/suppliers/add.php">Add Suppliers</a></li>
                        <li><a href="/Project_IMS/suppliers/list.php">List Suppliers</a></li>
                    </ul>
                </div>
                <?php endif; ?>
                <a href="/Project_IMS/transactions/stock_in.php">Stock In</a>
                <a href="/Project_IMS/transactions/stock_out.php">Stock Out</a>
                <a href="/Project_IMS/reports/index.php">Reports</a>
                <?php if ($_SESSION['role'] === 'admin'): ?><a href="/Project_IMS/users/list.php">Users</a><?php endif; ?>
            </nav>
            <div class="sidebar-footer"><span>Live Inventory</span><strong>Stock updates instantly after sales.</strong></div>
            <a class="logout-btn" id="logoutBtn" href="/Project_IMS/auth/logout.php">Logout</a>
        </aside>

        <div class="dashboard-shell page-shell">
            <header class="dashboard-header">
                <div>
                    <p class="eyebrow">Inventory</p>
                    <h1>Categories</h1>
                    <p class="subtitle">Manage all product categories from one dashboard view.</p>
                </div>
                <div class="header-metrics">
                    <div class="metric-card"><span>Total Categories</span><strong><?= count($categories) ?></strong></div>
                </div>
            </header>

            <?php if ($successMessage): ?><div class="alert success"><?= htmlspecialchars($successMessage) ?></div><?php endif; ?>
            <?php if ($errorMessage): ?><div class="alert error"><?= htmlspecialchars($errorMessage) ?></div><?php endif; ?>

            <section class="panel-table">
                <div class="panel-head">
                    <div>
                        <p class="panel-tag">Categories</p>
                        <h2>All Categories</h2>
                    </div>
                    <a href="./add.php" class="primary-btn">Add Category</a>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($categories)): ?>
                                <tr><td colspan="5" class="empty-state">No categories found.</td></tr>
                            <?php else: ?>
                                <?php foreach ($categories as $category): ?>
                                    <tr>
                                        <td>#<?= (int)$category['id'] ?></td>
                                        <td><strong><?= htmlspecialchars($category['name']) ?></strong></td>
                                        <td><?= htmlspecialchars($category['description'] ?: 'No description') ?></td>
                                        <td><?= htmlspecialchars(date('Y-m-d', strtotime($category['created_at']))) ?></td>
                                        <td>
                                            <div class="action-group">
                                                <a href="./edit.php?id=<?= (int)$category['id'] ?>" class="action-btn edit-btn">Edit</a>
                                                <a href="./delete.php?id=<?= (int)$category['id'] ?>" class="action-btn delete-btn" data-confirm="Delete this category?">Delete</a>
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
