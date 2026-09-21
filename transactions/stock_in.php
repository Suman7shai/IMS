<?php
session_start();
require $_SERVER['DOCUMENT_ROOT'] . '/Project_IMS/includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /Project_IMS/index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productIds = $_POST['product_id'] ?? [];
    $quantities = $_POST['quantity'] ?? [];
    $unitPrices = $_POST['unit_price'] ?? [];
    $notesList = $_POST['notes'] ?? [];
    $customerName = trim((string)($_POST['customer_name'] ?? ''));
    $supplierName = trim((string)($_POST['supplier_name'] ?? ''));
    $user_id = (int)$_SESSION['user_id'];

    if (!is_array($productIds)) {
        $productIds = [$productIds];
        $quantities = [$quantities];
        $unitPrices = [$unitPrices];
        $notesList = [$notesList];
    }

    if (!$productIds) {
        $_SESSION['error'] = 'Please add at least one stock-in item.';
        header('Location: /Project_IMS/transactions/stock_in.php');
        exit;
    }

    try {
        $pdo->beginTransaction();
        $insert = $pdo->prepare("INSERT INTO txns (product_id, type, quantity, unit_price, total_price, notes, user_id, txn_date) VALUES (?, 'in', ?, ?, ?, ?, ?, NOW())");
        $update = $pdo->prepare("UPDATE products SET quantity = quantity + ? WHERE id = ?");

        foreach ($productIds as $index => $rawProductId) {
            $productId = trim((string)$rawProductId);
            $quantity = (int)($quantities[$index] ?? 0);
            $unitPrice = (float)($unitPrices[$index] ?? 0);
            $notes = trim((string)($notesList[$index] ?? ''));

            $batchDetails = [];
            if ($customerName !== '') {
                $batchDetails[] = 'Customer: ' . $customerName;
            }
            if ($supplierName !== '') {
                $batchDetails[] = 'Supplier: ' . $supplierName;
            }
            if ($batchDetails) {
                $notes = trim($notes . ($notes !== '' ? ' | ' : '') . implode(' | ', $batchDetails));
            }

            if ($productId === '' || $quantity <= 0) {
                throw new RuntimeException('Please complete every stock-in item with a product and valid quantity.');
            }

            $totalPrice = $quantity * $unitPrice;
            $insert->execute([$productId, $quantity, $unitPrice, $totalPrice, $notes, $user_id]);
            $update->execute([$quantity, $productId]);
        }

        $pdo->commit();
        $_SESSION['success'] = 'Stock in recorded successfully.';
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $_SESSION['error'] = $exception->getMessage();
    }

    header('Location: /Project_IMS/transactions/stock_in.php');
    exit;
}

$products = $pdo->query("SELECT id, name, quantity, price FROM products ORDER BY name")->fetchAll();
$suppliers = $pdo->query("SELECT name FROM suppliers ORDER BY name")->fetchAll();
$successMessage = $_SESSION['success'] ?? '';
$errorMessage = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock In | IMS</title>
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
            background: linear-gradient(135deg, var(--primary), var(--primary-2));
            box-shadow: 0 12px 24px rgba(15,118,110,0.2);
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
        .stock-in-items { display: grid; gap: 14px; }
        .stock-in-party-fields { margin-bottom: 18px; }
        .stock-in-item {
            display: grid;
            grid-template-columns: 1.4fr 0.7fr 0.8fr 1.2fr auto;
            gap: 14px;
            align-items: end;
            padding: 16px;
            border: 1px solid var(--line);
            border-radius: 18px;
            background: rgba(255,255,255,0.65);
        }
        .add-stock-in { margin-top: 14px; }
        @media (max-width: 900px) {
            .form-grid { grid-template-columns: 1fr; }
        }
        @media (max-width: 640px) {
            body { padding: 16px; }
            .dashboard-header { padding: 22px 18px; flex-direction: column; align-items: flex-start; }
            .form-card { padding: 18px; }
            .submit-row { flex-direction: column; }
            .submit-row .primary-btn, .submit-row .secondary-btn { width: 100%; }
            .stock-in-item { grid-template-columns: 1fr; }
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
                <strong>Track incoming stock with instant updates.</strong>
            </div>
            <a class="logout-btn" id="logoutBtn" href="/Project_IMS/auth/logout.php">Logout</a>
        </aside>

        <div class="dashboard-shell page-shell transaction-page-shell">
            <header class="dashboard-header">
                <div>
                    <p class="eyebrow">Inventory</p>
                    <h1>Stock In</h1>
                    <p class="subtitle">Record incoming inventory and update warehouse stock in one step.</p>
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
                        <h2>Record Stock In</h2>
                    </div>
                </div>

                <form method="POST">
                    <div class="form-grid stock-in-party-fields">
                        <div class="field">
                            <label for="customer_name">Customer Name (Optional)</label>
                            <input type="text" id="customer_name" name="customer_name" placeholder="Enter customer name">
                        </div>
                        <div class="field">
                            <label for="supplier_name">Supplier Name (Optional)</label>
                            <select id="supplier_name" name="supplier_name">
                                <option value="">Select supplier</option>
                                <?php foreach ($suppliers as $supplier): ?>
                                    <option value="<?= htmlspecialchars($supplier['name']) ?>"><?= htmlspecialchars($supplier['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="stock-in-items" id="stockInItems">
                        <div class="stock-in-item">
                        <div class="field">
                            <label>Product</label>
                            <select name="product_id[]" required>
                                <option value="">Select a product</option>
                                <?php foreach ($products as $product): ?>
                                    <option value="<?= (int)$product['id'] ?>" data-price="<?= htmlspecialchars($product['price']) ?>"><?= htmlspecialchars($product['name']) ?> (Current: <?= (int)$product['quantity'] ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="field">
                            <label>Quantity</label>
                            <input type="number" name="quantity[]" min="1" step="1" placeholder="e.g. 25" required>
                        </div>

                        <div class="field">
                            <label>Unit Price</label>
                            <input type="number" name="unit_price[]" min="0" step="0.01" placeholder="0.00" required>
                        </div>

                        <div class="field">
                            <label>Notes</label>
                            <input type="text" name="notes[]" placeholder="Optional remarks">
                        </div>
                        <button type="button" class="remove-stock-in secondary-btn">Remove</button>
                        </div>
                    </div>
                    <button type="button" class="secondary-btn add-stock-in" id="addStockIn">+ Add Another Item</button>

                    <div class="submit-row">
                        <a href="/Project_IMS/dashboard.php" class="secondary-btn">Cancel</a>
                        <button type="submit" class="primary-btn">Save Stock In</button>
                    </div>
                </form>
            </section>
        </div>
    </div>

    <script src="/Project_IMS/assests/js/dashboard.js"></script>
    <script>
        const stockInItems = document.getElementById('stockInItems');
        const addStockIn = document.getElementById('addStockIn');
        const firstStockInItem = stockInItems.querySelector('.stock-in-item');

        addStockIn.addEventListener('click', () => {
            const newItem = firstStockInItem.cloneNode(true);
            newItem.querySelectorAll('input').forEach((input) => { input.value = ''; });
            newItem.querySelector('select').selectedIndex = 0;
            stockInItems.appendChild(newItem);
        });

        stockInItems.addEventListener('click', (event) => {
            if (event.target.classList.contains('remove-stock-in')) {
                const items = stockInItems.querySelectorAll('.stock-in-item');
                if (items.length > 1) {
                    event.target.closest('.stock-in-item').remove();
                }
            }
        });
    </script>
</body>
</html>
