<?php
require 'includes/auth_check.php';
require 'includes/db.php';

// Stats from database
$total_products = $pdo->query("SELECT COUNT(*) as total FROM products")->fetch()['total'];

$total_units = $pdo->query("SELECT SUM(quantity) as total FROM products")->fetch()['total'] ?? 0;

$low_stock = $pdo->query("
    SELECT COUNT(*) as total FROM products 
    WHERE quantity <= low_stock_threshold AND quantity > 0
")->fetch()['total'];

$units_sold = $pdo->query("
    SELECT SUM(quantity) as total FROM txns WHERE type = 'out'
")->fetch()['total'] ?? 0;

// Recent transactions
$recent_txns = $pdo->query("
    SELECT t.*, p.name as product_name, u.full_name
    FROM txns t
    JOIN products p ON t.product_id = p.id
    JOIN users u ON t.user_id = u.id
    ORDER BY t.txn_date DESC
    LIMIT 5
")->fetchAll();

// Products list
$products = $pdo->query("
    SELECT p.*, c.name as category_name 
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    ORDER BY p.id DESC
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
                <a href="#overview">Overview</a>
                <a href="#products">Products</a>
                <button id="addEditBtn" onclick="showAddEditForm()">Add / Edit</button>
                <button id="saleBtn" onclick="showSaleForm()">Sell Product</button>
                <a href="#activity">Activity</a>
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
                <h1>Bhandarify</h1>
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