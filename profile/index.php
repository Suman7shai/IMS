<?php
require $_SERVER['DOCUMENT_ROOT'] . '/Project_IMS/includes/auth_check.php';
require $_SERVER['DOCUMENT_ROOT'] . '/Project_IMS/includes/db.php';

$stmt = $pdo->prepare("SELECT full_name, username, email, role, created_at FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    header('Location: /Project_IMS/auth/logout.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | IMS</title>
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

            <a class="sidebar-profile" href="/Project_IMS/profile/index.php" aria-current="page">
                <span class="sidebar-profile-avatar"><?= strtoupper(substr($user['full_name'], 0, 1)) ?></span>
                <span class="sidebar-profile-info">
                    <strong><?= htmlspecialchars($user['full_name']) ?></strong>
                    <span><?= htmlspecialchars(ucfirst($user['role'])) ?></span>
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

        <main class="dashboard-shell profile-page-shell">
            <header class="dashboard-header">
                <div>
                    <p class="eyebrow">Account</p>
                    <h1>My Profile</h1>
                    <p class="subtitle">View your account details and access your account settings.</p>
                </div>
            </header>

            <section class="profile-layout">
                <article class="panel profile-hero-card">
                    <div class="profile-large-avatar"><?= strtoupper(substr($user['full_name'], 0, 1)) ?></div>
                    <h2><?= htmlspecialchars($user['full_name']) ?></h2>
                    <span class="profile-role-badge"><?= htmlspecialchars(ucfirst($user['role'])) ?></span>
                    <p>Inventory Management System user</p>
                </article>

                <article class="panel profile-details-card">
                    <div class="panel-head">
                        <div>
                            <p class="panel-tag">Account Details</p>
                            <h2>Profile Information</h2>
                        </div>
                    </div>
                    <dl class="profile-details-list">
                        <div>
                            <dt>Full Name</dt>
                            <dd><?= htmlspecialchars($user['full_name']) ?></dd>
                        </div>
                        <div>
                            <dt>Username</dt>
                            <dd><?= htmlspecialchars($user['username']) ?></dd>
                        </div>
                        <div>
                            <dt>Email</dt>
                            <dd><?= htmlspecialchars($user['email'] ?: 'Not provided') ?></dd>
                        </div>
                        <div>
                            <dt>Access Role</dt>
                            <dd><?= htmlspecialchars(ucfirst($user['role'])) ?></dd>
                        </div>
                        <div>
                            <dt>Account Created</dt>
                            <dd><?= htmlspecialchars(date('M d, Y', strtotime($user['created_at']))) ?></dd>
                        </div>
                    </dl>
                    <a class="primary-btn profile-password-btn" href="/Project_IMS/profile/change_password.php">Change Password</a>
                </article>
            </section>
        </main>
    </div>
    <script src="/Project_IMS/assests/js/dashboard.js"></script>
</body>
</html>
