<?php
session_start();
require $_SERVER['DOCUMENT_ROOT'] . '/Project_IMS/includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /Project_IMS/index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = trim((string)($_POST['product_id'] ?? ''));
    $quantity = (int)($_POST['quantity'] ?? 0);
    $unit_price = (float)($_POST['unit_price'] ?? 0);
    $notes = trim((string)($_POST['notes'] ?? ''));
    $user_id = (int)$_SESSION['user_id'];

    if ($product_id === '' || $quantity <= 0) {
        $_SESSION['error'] = 'Please select a product and enter a valid quantity.';
        header('Location: /Project_IMS/transactions/stock_out.php');
        exit;
    }

    $productStmt = $pdo->prepare("SELECT name, quantity FROM products WHERE id = ?");
    $productStmt->execute([$product_id]);
    $product = $productStmt->fetch();

    if (!$product) {
        $_SESSION['error'] = 'Selected product was not found.';
        header('Location: /Project_IMS/transactions/stock_out.php');
        exit;
    }

    if ((int)$product['quantity'] < $quantity) {
        $_SESSION['error'] = 'Insufficient stock! Available quantity: ' . (int)$product['quantity'] . ' units.';
        header('Location: /Project_IMS/transactions/stock_out.php');
        exit;
    }

    $total_price = $quantity * $unit_price;

    $insert = $pdo->prepare("
        INSERT INTO txns (product_id, type, quantity, unit_price, total_price, notes, user_id, txn_date)
        VALUES (?, 'out', ?, ?, ?, ?, ?, NOW())
    ");
    $insert->execute([$product_id, $quantity, $unit_price, $total_price, $notes, $user_id]);

    $update = $pdo->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ?");
    $update->execute([$quantity, $product_id]);

    $_SESSION['success'] = 'Stock out recorded successfully.';
    header('Location: /Project_IMS/transactions/stock_out.php');
    exit;
}

$products = $pdo->query("SELECT id, name, quantity, price FROM products ORDER BY name")->fetchAll();
$successMessage = $_SESSION['success'] ?? '';
$errorMessage = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Out | IMS</title>
    <link rel="stylesheet" href="/Project_IMS/assests/css/dashboard.css">
    <link rel="stylesheet" href="/Project_IMS/assests/css/sidebar-submenu.css">
    <style>
        body { margin: 0; }
        .page-shell { width: 100%; display: grid; gap: 22px; }
        .form-card {
            background: rgba(255,255,255,0.92);
            border-radius: 28px;
            border: 1px solid rgba(255,255,255,0.35);
            box-shadow: 0 28px 70px rgba(15,23,42,0.22);
            padding: 26px;
        }
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 20px;
        }
        .field {
            display: grid;
            gap: 8px;
        }
        .field label {
            font-weight: 700;
            color: var(--ink);
            font-size: 0.9rem;
        }
        .field input,
        .field select,
        .field textarea {
            width: 100%;
            border: 1px solid rgba(15,23,42,0.12);
            border-radius: 12px;
            padding: 12px 14px;
            background: rgba(255,255,255,0.96);
            color: var(--ink);
            font: inherit;
        }
        .field textarea {
            min-height: 120px;
            resize: vertical;
        }
        .field input:focus,
        .field select:focus,
        .field textarea:focus {
            outline: none;
            border-color: rgba(15,118,110,0.45);
            box-shadow: 0 0 0 4px rgba(20,184,166,0.12);
        }
        .submit-row {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 20px;
            flex-wrap: wrap;
        }
        .primary-btn, .secondary-btn {
            border: none;
            border-radius: 12px;
            padding: 12px 18px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .primary-btn {
            color: #fff;
            background: linear-gradient(135deg, #ef4444, #dc2626);
            box-shadow: 0 12px 24px rgba(239,68,68,0.2);
        }
        .secondary-btn {
            color: var(--ink);
            background: rgba(15,23,42,0.06);
        }
        .alert {
            padding: 14px 16px;
            border-radius: 14px;
            font-weight: 600;
            margin-bottom: 16px;
            border: 1px solid transparent;
        }
        .alert.success {
            background: rgba(22,163,74,0.08);
            color: var(--success);
            border-color: rgba(22,163,74,0.2);
        }
        .alert.error {
            background: rgba(239,68,68,0.08);
            color: var(--danger);
            border-color: rgba(239,68,68,0.2);
        }
        @media (max-width: 900px) {
            .form-grid { grid-template-columns: 1fr; }
        }
        @media (max-width: 640px) {
            body { padding: 16px; }
            .dashboard-header { padding: 22px 18px; flex-direction: column; align-items: flex-start; }
            .form-card { padding: 18px; }
            .submit-row { flex-direction: column; }
            .submit-row .primary-btn, .submit-row .secondary-btn { width: 100%; }
        }
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
                <?php if ($_SESSION['role'] === 'admin'): ?><a href="/Project_IMS/users/list.php">Users</a><?php endif; ?>
            </nav>
            <div class="sidebar-footer">
                <span>Live Inventory</span>
                <strong>Track outgoing stock with real-time updates.</strong>
            </div>
            <a class="logout-btn" id="logoutBtn" href="/Project_IMS/auth/logout.php">Logout</a>
        </aside>

        <div class="dashboard-shell page-shell">
            <header class="dashboard-header">
                <div>
                    <p class="eyebrow">Inventory</p>
                    <h1>Stock Out</h1>
                    <p class="subtitle">Record outgoing stock and keep the inventory ledger accurate.</p>
                </div>
                <div class="header-metrics">
                    <div class="metric-card">
                        <span>Available</span>
                        <strong><?= count($products) ?></strong>
                    </div>
                </div>
            </header>

            <?php if ($successMessage): ?>
                <div class="alert success"><?= htmlspecialchars($successMessage) ?></div>
            <?php endif; ?>
            <?php if ($errorMessage): ?>
                <div class="alert error"><?= htmlspecialchars($errorMessage) ?></div>
            <?php endif; ?>

            <section class="form-card">
                <div class="panel-head">
                    <div>
                        <p class="panel-tag">Stock Movement</p>
                        <h2>Record Stock Out</h2>
                    </div>
                </div>

                <form method="POST">
                    <div class="form-grid">
                        <div class="field">
                            <label for="product_id">Product</label>
                            <select id="product_id" name="product_id" required>
                                <option value="">Select a product</option>
                                <?php foreach ($products as $product): ?>
                                    <option value="<?= (int)$product['id'] ?>"><?= htmlspecialchars($product['name']) ?> (Available: <?= (int)$product['quantity'] ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="field">
                            <label for="quantity">Quantity</label>
                            <input type="number" id="quantity" name="quantity" min="1" step="1" placeholder="e.g. 12" required>
                        </div>

                        <div class="field">
                            <label for="unit_price">Unit Price</label>
                            <input type="number" id="unit_price" name="unit_price" min="0" step="0.01" placeholder="0.00" required>
                        </div>

                        <div class="field">
                            <label for="notes">Notes</label>
                            <input type="text" id="notes" name="notes" placeholder="Optional remarks">
                        </div>
                    </div>

                    <div class="submit-row">
                        <a href="/Project_IMS/dashboard.php" class="secondary-btn">Cancel</a>
                        <button type="submit" class="primary-btn">Save Stock Out</button>
                    </div>
                </form>
            </section>
        </div>
    </div>

    <script src="/Project_IMS/assests/js/dashboard.js"></script>
</body>
</html>
