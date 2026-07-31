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


if ($_SESSION['role'] === 'admin') {
    $units_sold = $pdo->query("
    SELECT SUM(quantity) as total
    FROM txns WHERE type = 'out'
")->fetch()['total'] ?? 0;
}


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
                    <li><a href="./products/edit.php">Edit Product</a></li>
                    <li><a href="./products/delete.php">Delete Product</a></li>
                    <li><a href="./products/list.php">List Products</a></li>
                </ul>
            </div>
            <div class="nav-item has-submenu">
                <button type="button" class="nav-parent" aria-expanded="false">
                    <span>Catagories</span>
                    <svg class="chevron" width="14" height="14" viewBox="0 0 24 24" fill="none">
                        <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
                <ul class="submenu">
                    <li><a href="./products/add.php">Add Catagories</a></li>
                    <li><a href="./products/edit.php">Edit Catagories</a></li>
                    <li><a href="./products/delete.php">Delete Catagories</a></li>
                    <li><a href="./products/list.php">List Catagories</a></li>
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
                    <li><a href="./products/add.php">Add Suppliers</a></li>
                    <li><a href="./products/edit.php">Edit Suppliers</a></li>
                    <li><a href="./products/delete.php">Delete Suppliers</a></li>
                    <li><a href="./products/list.php">List Suppliers</a></li>
                </ul>
            </div>
            <a href="#">Stock In</a>
            <a href="#">Stock Out</a>
            <a href="#sales">Reports</a>
            <a href="#users">Users</a>
        </nav>

            <div class="sidebar-footer">
                <span>Live Inventory</span>
                <strong>Stock updates instantly after sales.</strong>
            </div>

            <button type="button" class="logout-btn" id="logoutBtn">Logout</button>
        </aside>

        <div class="dashboard-shell">
        <header class="dashboard-header">
            <div>
                <p class="eyebrow">Inventory Management System</p>
                <h1>StockSync</h1>
                <p class="subtitle">Manage products, update stock, and record sales in one place.</p>
            </div>

            <div class="header-metrics">
                <div class="metric-card">
                    <span>Today</span>
                    <strong id="currentDate">--</strong>
                </div>
                <div class="metric-card">
                    <span>Last update</span>
                    <strong id="lastUpdated">--</strong>
                </div>
            </div>
        </header>

        <section class="stats-grid" id="overview">
            <article class="stat-card">
                <span>Total Products</span>
                <strong id="totalProducts">0</strong>
            </article>
            <article class="stat-card">
                <span>Total Units</span>
                <strong id="totalUnits">0</strong>
            </article>
            <article class="stat-card">
                <span>Low Stock</span>
                <strong id="lowStock">0</strong>
            </article>
            <article class="stat-card">
                <span>Units Sold</span>
                <strong id="unitsSold">0</strong>
            </article>
        </section>

        <section class="toolbar">
            <label class="search-box">
                <span>Search product</span>
                <input type="search" id="searchInput" placeholder="Type product name or category...">
            </label>
            <div class="toolbar-actions">
                <button type="button" id="resetDemo" class="secondary-btn">Search Products</button>
            </div>
        </section>

        <section class="dashboard-grid">
            <article class="panel panel-table" id="products">
                <div class="panel-head">
                    <div>
                        <p class="panel-tag">Products</p>
                        <h2>Product List</h2>
                    </div>
                    <p class="panel-note">Edit, delete, or sell products from the table.</p>
                </div>

                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="productTableBody"></tbody>
                    </table>
                </div>
            </article>
        </section>

        <section class="panel activity-panel" id="activity">
            <div class="panel-head">
                <div>
                    <p class="panel-tag">Activity</p>
                    <h2>Recent Sales</h2>
                </div>
            </div>
            <div id="activityList" class="activity-list"></div>
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