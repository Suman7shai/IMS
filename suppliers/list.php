<?php

session_start();
require $_SERVER['DOCUMENT_ROOT'] . '/Project_IMS/includes/db.php';

if(!isset($_SESSION['user_id'])) {
  header("Location: /Project_IMS/index.php");
  exit;
}

if ($_SESSION['role'] !== 'admin') {
    header("Location: /Project_IMS/dashboard.php");
    exit;
}

$suppliers = $pdo->query("SELECT * FROM suppliers ORDER BY created_at DESC")->fetchAll();

$successMessage = $_SESSION['success'] ?? '';
$errorMessage = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suppliers | IMS</title>
    <link rel="stylesheet" href="/Project_IMS/assests/css/dashboard.css">
    <link rel="stylesheet" href="/Project_IMS/assests/css/sidebar-submenu.css">
    <link rel="stylesheet" href="/Project_IMS/assests/css/suppliers.css">
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
                    <h1>Suppliers</h1>
                    <p class="subtitle">Manage supplier details and contact information.</p>
                </div>
                <div class="header-metrics">
                    <div class="metric-card"><span>Total Suppliers</span><strong><?= count($suppliers) ?></strong></div>
                </div>
            </header>

            <?php if ($successMessage): ?><div class="alert success"><?= htmlspecialchars($successMessage) ?></div><?php endif; ?>
            <?php if ($errorMessage): ?><div class="alert error"><?= htmlspecialchars($errorMessage) ?></div><?php endif; ?>

            <section class="panel-table">
                <div class="panel-head">
                    <div>
                        <p class="panel-tag">Suppliers</p>
                        <h2>All Suppliers</h2>
                    </div>
                    <a href="./add.php" class="primary-btn">Add Supplier</a>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Contact Person</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Address</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($suppliers)): ?>
                                <tr><td colspan="7" class="empty-state">No suppliers found.</td></tr>
                            <?php else: ?>
                                <?php foreach ($suppliers as $supplier): ?>
                                    <tr>
                                        <td>#<?= (int)$supplier['id'] ?></td>
                                        <td><strong><?= htmlspecialchars($supplier['name']) ?></strong></td>
                                        <td><?= htmlspecialchars($supplier['contact_person'] ?: 'N/A') ?></td>
                                        <td><?= htmlspecialchars($supplier['email'] ?: 'N/A') ?></td>
                                        <td><?= htmlspecialchars($supplier['phone'] ?: 'N/A') ?></td>
                                        <td><?= htmlspecialchars($supplier['address'] ?: 'N/A') ?></td>
                                        <td>
                                            <div class="action-group">
                                                <a href="./edit.php?id=<?= (int)$supplier['id'] ?>" class="action-btn edit-btn">Edit</a>
                                                <a href="./delete.php?id=<?= (int)$supplier['id'] ?>" class="action-btn delete-btn" data-confirm="Delete this supplier?">Delete</a>
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