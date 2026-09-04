<?php

session_start();
require $_SERVER['DOCUMENT_ROOT'] . '/Project_IMS/includes/db.php';

if (!isset($_SESSION['user_id'])) {
  header('Location: /Project_IMS/index.php');
  exit;
}

if ($_SESSION['role'] !== 'admin') {
  $_SESSION['error'] = "You do not have permission to access this page.";
  header("Location: /Project_IMS/dashboard.php");
  exit;
}

$users = $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();

$successMessage = $_SESSION['success'] ?? '';
$errorMessage = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users | IMS</title>
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
        table { width: 100%; border-collapse: collapse; min-width: 860px; background: #fff; }
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
        .badge {
            display: inline-flex; align-items: center; justify-content: center; padding: 7px 10px; border-radius: 999px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em;
        }
        .badge.admin { background: rgba(15,118,110,0.14); color: var(--primary); }
        .badge.staff { background: rgba(245,158,11,0.12); color: var(--warning); }
        .action-group { display: flex; gap: 8px; flex-wrap: wrap; }
        .delete-btn {
            display: inline-flex; align-items: center; justify-content: center;
            padding: 10px 14px; border-radius: 12px; text-decoration: none; font-weight: 700; font-size: 0.88rem; color: #fff; background: linear-gradient(135deg, #ef4444, #dc2626); transition: transform 0.2s ease;
        }
        .delete-btn:hover { transform: translateY(-1px); }
        .primary-btn { border: none; border-radius: 12px; padding: 12px 18px; font-weight: 700; cursor: pointer; text-decoration: none; color: #fff; background: linear-gradient(135deg, var(--primary), var(--primary-2)); }
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
                <?php if ($_SESSION['role'] === 'admin'): ?>
                <a href="/Project_IMS/users/list.php">Users</a>
                <?php endif; ?>
            </nav>
            <div class="sidebar-footer"><span>Live Inventory</span><strong>Stock updates instantly after sales.</strong></div>
            <a class="logout-btn" id="logoutBtn" href="/Project_IMS/auth/logout.php">Logout</a>
        </aside>

        <div class="dashboard-shell page-shell">
            <header class="dashboard-header">
                <div>
                    <p class="eyebrow">Access</p>
                    <h1>Users</h1>
                    <p class="subtitle">Manage team access and permissions inside the inventory system.</p>
                </div>
                <div class="header-metrics">
                    <div class="metric-card"><span>Total Users</span><strong><?= count($users) ?></strong></div>
                </div>
            </header>

            <?php if ($successMessage): ?><div class="alert success"><?= htmlspecialchars($successMessage) ?></div><?php endif; ?>
            <?php if ($errorMessage): ?><div class="alert error"><?= htmlspecialchars($errorMessage) ?></div><?php endif; ?>

            <section class="panel-table">
                <div class="panel-head">
                    <div>
                        <p class="panel-tag">Users</p>
                        <h2>All Users</h2>
                    </div>
                    <a href="./add.php" class="primary-btn">Add User</a>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Full Name</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($users)): ?>
                                <tr><td colspan="7" class="empty-state">No users found.</td></tr>
                            <?php else: ?>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td>#<?= (int)$user['id'] ?></td>
                                        <td><strong><?= htmlspecialchars($user['full_name']) ?></strong></td>
                                        <td><?= htmlspecialchars($user['username']) ?></td>
                                        <td><?= htmlspecialchars($user['email']) ?></td>
                                        <td><span class="badge <?= htmlspecialchars($user['role']) ?>"><?= htmlspecialchars($user['role']) ?></span></td>
                                        <td><?= htmlspecialchars(date('Y-m-d', strtotime($user['created_at']))) ?></td>
                                        <td>
                                            <div class="action-group">
                                                <?php if ((int)$user['id'] !== (int)$_SESSION['user_id']): ?>
                                                    <a href="./delete.php?id=<?= (int)$user['id'] ?>" class="delete-btn" data-confirm="Delete this user?">Delete</a>
                                                <?php else: ?>
                                                    <span class="badge staff">You</span>
                                                <?php endif; ?>
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