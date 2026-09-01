<?php

session_start();
require $_SERVER['DOCUMENT_ROOT'] . '/Project_IMS/includes/db.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: /Project_IMS/index.php");
  exit;
}

if ($_SESSION['role'] !== 'admin') {
  $_SESSION['error'] = "You do not have permission to access this page.";
  header("Location: /Project_IMS/dashboard.php");
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['name']);
  $contact_person = trim($_POST['contact_person']);
  $email = trim($_POST['email']);
  $phone = trim($_POST['phone']);
  $address = trim($_POST['address']);

  if (empty($name)) {
    $_SESSION['error'] = "Supplier name is required.";
    header("Location: add.php");
    exit;
  }

  $stmt = $pdo->prepare("INSERT INTO suppliers (name, contact_person, email, phone, address) VALUES (?, ?, ?, ?, ?)");
  $stmt->execute([$name, $contact_person, $email, $phone, $address]);

  $_SESSION['success'] = "Supplier added successfully";
  header("Location: list.php");
  exit;
}

$successMessage = $_SESSION['success'] ?? '';
$errorMessage = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Supplier | IMS</title>
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
                        <li><a href="./add.php">Add Suppliers</a></li>
                        <li><a href="./list.php">List Suppliers</a></li>
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
                    <h1>Add Supplier</h1>
                    <p class="subtitle">Register a new supplier with the relevant business and contact details.</p>
                </div>
            </header>
            <?php if ($successMessage): ?><div class="alert success"><?= htmlspecialchars($successMessage) ?></div><?php endif; ?>
            <?php if ($errorMessage): ?><div class="alert error"><?= htmlspecialchars($errorMessage) ?></div><?php endif; ?>

            <section class="form-card">
                <div class="form-header">
                    <div>
                        <p class="panel-tag">Supplier</p>
                        <h2>New Supplier</h2>
                    </div>
                    <a href="./list.php" class="secondary-btn">Back to List</a>
                </div>
                <form method="POST" action="add.php">
                    <div class="form-grid">
                        <div class="form-group full">
                            <label for="name">Supplier Name</label>
                            <input type="text" id="name" name="name" placeholder="Enter supplier name" required>
                        </div>
                        <div class="form-group">
                            <label for="contact_person">Contact Person</label>
                            <input type="text" id="contact_person" name="contact_person" placeholder="Contact person name">
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" placeholder="supplier@example.com">
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone</label>
                            <input type="tel" id="phone" name="phone" placeholder="Phone number">
                        </div>
                        <div class="form-group full">
                            <label for="address">Address</label>
                            <textarea id="address" name="address" placeholder="Supplier address"></textarea>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="reset" class="secondary-btn">Clear</button>
                        <button type="submit" class="primary-btn">Save Supplier</button>
                    </div>
                </form>
            </section>
        </div>
    </div>
    <script src="/Project_IMS/assests/js/dashboard.js"></script>
</body>
</html>