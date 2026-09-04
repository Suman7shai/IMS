<?php
require 'includes/auth_check.php';
require 'includes/db.php';

// Stats from database
$total_products = $pdo->query("
    SELECT COUNT(*) as total 
    FROM products
")->fetch()['total'];

$total_units = $pdo->query("
    SELECT SUM(quantity) as total 
    FROM products
")->fetch()['total'] ?? 0;

$low_stock = $pdo->query("
    SELECT COUNT(*) as total FROM products 
    WHERE quantity <= low_stock_threshold AND quantity > 0
")->fetch()['total'];

$units_sold = $pdo->query("
    SELECT SUM(quantity) as total
    FROM txns WHERE type = 'out'
")->fetch()['total'] ?? 0;


$low_stock_products = $pdo->query("
    SELECT name, quantity, low_stock_threshold
    FROM products 
    WHERE quantity <= low_stock_threshold 
    ORDER BY quantity ASC
")->fetchAll();

// Recent 5 transactions
$recent_txns = $pdo->query("
    SELECT t.*, p.name as product_name, u.full_name
    FROM txns t
    JOIN products p ON t.product_id = p.id
    JOIN users u ON t.user_id = u.id
    ORDER BY t.txn_date DESC
    LIMIT 5
")->fetchAll();

// Last transaction time
$last_updated = $pdo->query("
    SELECT txn_date 
    FROM txns 
    ORDER BY txn_date DESC 
    LIMIT 1
")->fetch()['txn_date'] ?? null;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IMS</title>
    <link rel="stylesheet" href="/Project_IMS/assests/css/dashboard.css">
    <link rel="stylesheet" href="/Project_IMS/assests/css/modal.css">
    <link rel="stylesheet" href="/Project_IMS/assests/css/sale-modal.css">
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
            <a href="#overview">Dashboard</a>
            <div class="nav-item has-submenu">
                <button type="button" class="nav-parent" aria-expanded="false">
                    <span>Products</span>
                    <svg class="chevron" width="14" height="14" viewBox="0 0 24 24" fill="none">
                        <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
                <ul class="submenu">
                    <li><a href="./products/add.php">Add Product</a></li>
                    <!-- <li><a href="./products/edit.php">Edit Product</a></li>
                    <li><a href="./products/delete.php">Delete Product</a></li> -->
                    <li><a href="./products/list.php">List Products</a></li>
                </ul>
            </div>
            <?php if ($_SESSION['role'] === 'admin'): ?>
            <div class="nav-item has-submenu">
                <button type="button" class="nav-parent" aria-expanded="false">
                    <span>Categories</span>
                    <svg class="chevron" width="14" height="14" viewBox="0 0 24 24" fill="none">
                        <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
                <ul class="submenu">
                    <li><a href="./categories/add.php">Add Categories</a></li>
                    <!-- <li><a href="./categories/edit.php">Edit Categories</a></li>
                    <li><a href="./categories/delete.php">Delete Categories</a></li> -->
                    <li><a href="./categories/list.php">List Categories</a></li>
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
                    <!-- <li><a href="./suppliers/edit.php">Edit Suppliers</a></li>
                    <li><a href="./suppliers/delete.php">Delete Suppliers</a></li> -->
                    <li><a href="./suppliers/add.php">Add Suppliers</a></li>
                    <li><a href="./suppliers/list.php">List Suppliers</a></li>
                </ul>
            </div>
            <?php endif; ?>
            <a href="./transactions/stock_in.php">Stock In</a>
            <a href="./transactions/stock_out.php">Stock Out</a>
            <a href="./reports/index.php">Reports</a>
            <?php if ($_SESSION['role'] === 'admin'): ?>
            <a href="./users/list.php">Users</a>
            <?php endif; ?>
        </nav>

            <div class="sidebar-footer">
                <span>Live Inventory</span>
                <strong>Stock updates instantly after sales.</strong>
            </div>

            <a class="logout-btn" id="logoutBtn" href="/Project_IMS/auth/logout.php">Logout</a>
        </aside>

        <div class="dashboard-shell">
        <header class="dashboard-header">
            <div>
                <p class="eyebrow">Inventory Management System</p>
                <h1>StockSync</h1>
                <p class="subtitle">Manage products, update stock, and record sales in one place.</p>
            </div>

            <div class="header-metrics">
                <!-- <div class="metric-card">
                    <span>Today</span>
                    <strong id="currentDate">--</strong>
                </div> -->
                <div class="metric-card">
                    <span>Last Updated</span>
                    <strong>
                        <?php if ($last_updated): ?>
                            <?= date('M d Y, h:i A', strtotime($last_updated)) ?>
                        <?php else: ?>
                            No transactions yet
                        <?php endif; ?>
                    </strong>
                </div>
            </div>
        </header>

        <section class="stats-grid" id="overview">
            <article class="stat-card">
                <span>Total Products</span>
                <strong id="totalProducts" data-server-rendered="true"><?= (int) $total_products ?></strong>
            </article>
            <article class="stat-card">
                <span>Total Units</span>
                <strong id="totalUnits" data-server-rendered="true"><?= (int) $total_units ?></strong>
            </article>
            <article class="stat-card">
                <span>Low Stock</span>
                <strong id="lowStock" data-server-rendered="true"><?= (int) $low_stock ?></strong>
            </article>
            <article class="stat-card">
                <span>Units Sold</span>
                <strong id="unitsSold" data-server-rendered="true"><?= (int) $units_sold ?></strong>
            </article>
        </section>

        <section class="dashboard-grid">
            <article class="panel activity-panel" id="activity">
                <div class="panel-head">
                    <div>
                        <p class="panel-tag">Activity</p>
                        <h2>Recent Transactions</h2>
                    </div>
                </div>
                <div id="activityList" class="activity-list" data-server-rendered="true">
                    <?php if (!$recent_txns): ?>
                        <div class="empty-state">No transactions recorded yet.</div>
                    <?php else: ?>
                        <?php foreach ($recent_txns as $txn): ?>
                            <div class="activity-item">
                                <div>
                                    <strong><?= htmlspecialchars($txn['product_name']) ?></strong>
                                    <span><?= (int) $txn['quantity'] ?> unit(s) <?= $txn['type'] === 'in' ? 'bought' : 'sold' ?> by <?= htmlspecialchars($txn['full_name'] ?? 'Unknown user') ?> on <?= date('M d, Y h:i A', strtotime($txn['txn_date'])) ?></span>
                                </div>
                                <span class="badge <?= $txn['type'] === 'in' ? 'ok' : 'low' ?>"><?= $txn['type'] === 'in' ? 'Bought' : 'Sold' ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </article>
            <aside class="panel low-stock-panel">
                <div class="panel-head">
                    <div>
                        <p class="panel-tag">Inventory Alert</p>
                        <h2>Low Stock Products</h2>
                    </div>
                </div>
                <div class="low-stock-list">
                    <?php if (!$low_stock_products): ?>
                        <div class="empty-state">All products have sufficient stock.</div>
                    <?php else: ?>
                        <?php foreach ($low_stock_products as $product): ?>
                            <div class="low-stock-item">
                                <div>
                                    <strong><?= htmlspecialchars($product['name']) ?></strong>
                                    <span>Alert at <?= (int) $product['low_stock_threshold'] ?> units</span>
                                </div>
                                <span class="stock-count"> <?= (int) $product['quantity'] ?> left</span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </aside>
        </section>
        </div>
    </div>
<!-- Add edit form section -->

<dialog class="panel" id="manage-product">
    <div class="modal-head">
        <h3>Product Form</h3>
        <button type="button" onclick="closeAddEditForm()">&times;</button>
    </div>

    <form id="productForm" method="dialog">
        <label>
            Product Name
            <input type="text" id="productName" required>
        </label>
        <label>
            Category
            <input type="text" id="productCategory" required>
        </label>
        <div class="form-row">
            <label>
                Price
                <input type="number" id="productPrice" min="0" step="0.01" required>
            </label>
            <label>
                Stock
                <input type="number" id="productStock" min="0" step="1" required>
            </label>
        </div>
        <div class="form-actions">
            <button type="submit" class="primary-btn">Save Product</button>
            <button type="button" class="secondary-btn" onclick="closeAddEditForm()">Cancel</button>
        </div>
    </form>
    <script>
        const dialog = document.getElementById('manage-product');

        function showAddEditForm() {
            if (!dialog.open) {
            dialog.showModal();
            }
        }
        function closeAddEditForm() {
            dialog.close();
        }
    </script>
</dialog>

 <!-- sell_products dialog section -->
<dialog class="panel" id="sale-product">
    <div class="panel-head">
         <div>
            <p class="panel-tag">Sale</p>
            <h2>Sell Product</h2>
            <button type="button" onclick="closeSaleForm()">&times;</button>
        </div>
    </div>
    <form id="saleForm" class="stack-form">
        <label>
          Product
            <input type="search" id="saleProduct" required>           
        </label>
        <label>
            Rate per Unit
                <input type="number" id="saleRate" min="0" step="0.01" value="0.00" required>
        </label>
        <label>
            Quantity Sold
                <input type="number" id="saleQuantity" min="1" step="1" value="1" required>
        </label>
        <label>
            Total Amount
                <input type="number" id="saleTotal" min="0" step="0.01" value="0.00" readonly>
        </label>
            <button type="submit" class="primary-btn">Sale</button>
            <button type="button" class="secondary-btn" onclick="closeSaleForm()">Cancel</button>
    </form>
    <script>
        const saleDialog = document.getElementById('sale-product');

        function showSaleForm() {
            if (!saleDialog.open) {
            saleDialog.showModal();
            }
        }
        function closeSaleForm() {
            saleDialog.close();
        }
    </script>
</dialog>
    <script src="/Project_IMS/assests/js/dashboard.js"></script>
</body>
</html>