<?php

session_start();
require $_SERVER['DOCUMENT_ROOT'] . '/Project_IMS/includes/db.php';

if(!isset($_SESSION['user_id'])) {
  header("Location: /Project_IMS/index.php");
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
    <style>
        body { margin: 0; }
        .page-shell { width: 100%; display: grid; gap: 22px; }
        .panel-table {
            background: rgba(255,255,255,0.92);
            border-radius: 28px;
            border: 1px solid rgba(255,255,255,0.35);
            box-shadow: 0 28px 70px rgba(15,23,42,0.22);
            padding: 24px;
        }
        .panel-head { display: flex; justify-content: space-between; align-items: center; gap: 12px; margin-bottom: 18px; flex-wrap: wrap; }
        .panel-head h2 { margin-top: 8px; font-size: clamp(1.5rem, 2vw, 2.2rem); }
        .table-wrap { overflow-x: auto; border-radius: 18px; border: 1px solid rgba(15,23,42,0.08); }
        table { width: 100%; border-collapse: collapse; min-width: 900px; background: #fff; }
        th, td { padding: 16px 18px; text-align: left; border-bottom: 1px solid rgba(15,23,42,0.08); }
        thead th {
            background: rgba(15,118,110,0.08);
            color: var(--primary);
            text-transform: uppercase;
            letter-spacing: 0.08em;
            font-size: 0.8rem;
        }
        tbody tr:hover { background: rgba(20,184,166,0.04); }
        .empty-state { text-align: center; color: var(--muted); font-weight: 600; padding: 28px 20px; }
        .action-group { display: flex; gap: 8px; flex-wrap: wrap; }
        .action-btn {
            display: inline-flex; align-items: center; justify-content: center;
            padding: 10px 14px; border-radius: 12px; text-decoration: none; font-weight: 700; font-size: 0.88rem; transition: transform 0.2s ease;
        }
        .action-btn:hover { transform: translateY(-1px); }
        .edit-btn { color: #fff; background: linear-gradient(135deg, var(--primary), var(--primary-2)); }
        .delete-btn { color: #fff; background: linear-gradient(135deg, #ef4444, #dc2626); }
        .primary-btn, .secondary-btn { border: none; border-radius: 12px; padding: 12px 18px; font-weight: 700; cursor: pointer; text-decoration: none; transition: transform 0.2s ease; }
        .primary-btn { color: #fff; background: linear-gradient(135deg, var(--primary), var(--primary-2)); }
        .secondary-btn { color: var(--ink); background: rgba(15,23,42,0.06); }
        .primary-btn:hover, .secondary-btn:hover { transform: translateY(-1px); }
        .alert { padding: 14px 16px; border-radius: 14px; font-weight: 600; margin-bottom: 16px; border: 1px solid transparent; }
        .alert.success { background: rgba(22,163,74,0.08); color: var(--success); border-color: rgba(22,163,74,0.2); }
        .alert.error { background: rgba(239,68,68,0.08); color: var(--danger); border-color: rgba(239,68,68,0.2); }
        @media (max-width: 640px) { body { padding: 16px; } .dashboard-header { padding: 22px 18px; flex-direction: column; align-items: flex-start; } .panel-table { padding: 16px; } }
    </style>
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
                <a href="/Project_IMS/transactions/stock_in.php">Stock In</a>
                <a href="/Project_IMS/transactions/stock_out.php">Stock Out</a>
                <a href="/Project_IMS/reports/index.php">Reports</a>
                <a href="/Project_IMS/users/list.php">Users</a>
            </nav>
            <div class="sidebar-footer"><span>Live Inventory</span><strong>Stock updates instantly after sales.</strong></div>
            <button type="button" class="logout-btn" id="logoutBtn">Logout</button>
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
                                                <a href="./delete.php?id=<?= (int)$supplier['id'] ?>" class="action-btn delete-btn" onclick="return confirm('Delete this supplier?');">Delete</a>
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