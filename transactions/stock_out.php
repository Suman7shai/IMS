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
    $user_id = (int)$_SESSION['user_id'];

    if (!is_array($productIds)) {
        $productIds = [$productIds];
        $quantities = [$quantities];
        $unitPrices = [$unitPrices];
        $notesList = [$notesList];
    }

    if (!$productIds) {
        $_SESSION['error'] = 'Please add at least one stock-out item.';
        header('Location: /Project_IMS/transactions/stock_out.php');
        exit;
    }

    $invoiceItems = [];
    try {
        $pdo->beginTransaction();
        $productStmt = $pdo->prepare("SELECT name, quantity FROM products WHERE id = ? FOR UPDATE");
        $insert = $pdo->prepare("INSERT INTO txns (product_id, type, quantity, unit_price, total_price, notes, user_id, txn_date) VALUES (?, 'out', ?, ?, ?, ?, ?, NOW())");
        $update = $pdo->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ?");

        foreach ($productIds as $index => $rawProductId) {
            $productId = trim((string)$rawProductId);
            $quantity = (int)($quantities[$index] ?? 0);
            $unitPrice = (float)($unitPrices[$index] ?? 0);
            $notes = trim((string)($notesList[$index] ?? ''));

            if ($productId === '' || $quantity <= 0) {
                throw new RuntimeException('Please complete every stock-out item with a product and valid quantity.');
            }

            $productStmt->execute([$productId]);
            $product = $productStmt->fetch();
            if (!$product) {
                throw new RuntimeException('One of the selected products was not found.');
            }
            if ((int)$product['quantity'] < $quantity) {
                throw new RuntimeException('Insufficient stock for ' . $product['name'] . '. Available quantity: ' . (int)$product['quantity'] . ' units.');
            }

            $totalPrice = $quantity * $unitPrice;
            $insert->execute([$productId, $quantity, $unitPrice, $totalPrice, $notes, $user_id]);
            $update->execute([$quantity, $productId]);
            $invoiceItems[] = [
                'product_name' => $product['name'],
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total_price' => $totalPrice,
                'notes' => $notes
            ];
        }

        $pdo->commit();
        $_SESSION['last_stock_out_invoice'] = [
            'customer_name' => $customerName,
            'items' => $invoiceItems
        ];
        $_SESSION['success'] = 'Stock out recorded successfully.';
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $_SESSION['error'] = $exception->getMessage();
    }

    header('Location: /Project_IMS/transactions/stock_out.php');
    exit;
}

$products = $pdo->query("SELECT id, name, quantity, price, sale_price FROM products ORDER BY name")->fetchAll();
$stockOutTransactions = $pdo->query("SELECT t.*, p.name AS product_name, u.full_name FROM txns t LEFT JOIN products p ON t.product_id = p.id LEFT JOIN users u ON t.user_id = u.id WHERE t.type = 'out' ORDER BY t.txn_date DESC")->fetchAll();
$totalStockOutQuantity = 0;
$totalStockOutAmount = 0;
foreach ($stockOutTransactions as $stockOutTransaction) {
    $totalStockOutQuantity += (int)$stockOutTransaction['quantity'];
    $totalStockOutAmount += (float)($stockOutTransaction['total_price'] ?? 0);
}
$successMessage = $_SESSION['success'] ?? '';
$errorMessage = $_SESSION['error'] ?? '';
$lastStockOutInvoice = $_SESSION['last_stock_out_invoice'] ?? [];
$lastInvoiceCustomer = is_array($lastStockOutInvoice) ? ($lastStockOutInvoice['customer_name'] ?? '') : '';
$lastInvoiceItems = is_array($lastStockOutInvoice) ? ($lastStockOutInvoice['items'] ?? []) : [];
$lastInvoiceQuantity = 0;
$lastInvoiceAmount = 0;
foreach ($lastInvoiceItems as $invoiceItem) {
    $lastInvoiceQuantity += (int)$invoiceItem['quantity'];
    $lastInvoiceAmount += (float)$invoiceItem['total_price'];
}
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
            color: #000;
            border-color: rgba(22,163,74,0.2);
        }
        .alert.error {
            background: rgba(239,68,68,0.08);
            color: var(--danger);
            border-color: rgba(239,68,68,0.2);
        }
        .stock-out-history {
            background: rgba(255,255,255,0.92);
            border-radius: 28px;
            border: 1px solid rgba(255,255,255,0.35);
            box-shadow: 0 28px 70px rgba(15,23,42,0.22);
            padding: 26px;
        }
        .stock-out-history .panel-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 18px;
        }
        .stock-out-history table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
        }
        .stock-out-history th,
        .stock-out-history td {
            padding: 12px 14px;
            text-align: left;
            border-bottom: 1px solid rgba(15,23,42,0.08);
        }
        .stock-out-history th {
            color: #fff;
            background: var(--primary);
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }
        .stock-out-history tfoot td {
            background: rgba(20,184,166,0.12);
            border-top: 2px solid var(--primary);
            font-weight: 800;
        }
        .stock-out-items {
            display: grid;
            gap: 14px;
        }
        .stock-out-item {
            display: grid;
            grid-template-columns: 1.4fr 0.7fr 0.8fr 1.2fr auto;
            gap: 14px;
            align-items: end;
            padding: 16px;
            border: 1px solid var(--line);
            border-radius: 18px;
            background: rgba(255,255,255,0.65);
        }
        .add-stock-out { margin-top: 14px; }
        .remove-stock-out { color: var(--danger); white-space: nowrap; }
        .customer-field { margin-bottom: 18px; }
        .stock-out-invoice {
            background: #fff;
            border: 2px solid var(--primary);
            border-radius: 24px;
            padding: 24px;
            box-shadow: var(--shadow);
        }
        .stock-out-invoice-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            border-bottom: 2px solid var(--primary-2);
            padding-bottom: 14px;
            margin-bottom: 16px;
        }
        .invoice-customer {
            margin-left: auto;
            color: var(--ink);
            font-size: 0.95rem;
        }
        .stock-out-invoice table { width: 100%; border-collapse: collapse; }
        .stock-out-invoice th,
        .stock-out-invoice td { padding: 10px; text-align: left; border-bottom: 1px solid var(--line); }
        .stock-out-invoice th { color: #fff; background: var(--primary); text-transform: uppercase; font-size: 0.78rem; }
        .stock-out-invoice tfoot td { background: #ccfbf1; border-top: 2px solid var(--primary); font-weight: 800; }
        .stock-out-table-wrap {
            overflow-x: auto;
            border: 1px solid var(--line);
            border-radius: 16px;
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
            .stock-out-history { padding: 18px; }
            .stock-out-history .panel-head { align-items: flex-start; flex-direction: column; }
            .stock-out-item { grid-template-columns: 1fr; }
        }

        @media print {
            @page { size: A4 landscape; margin: 14mm; }
            body { background: #fff; padding: 0; color: #0f172a; }
            .sidebar,
            .mobile-sidebar-toggle,
            .sidebar-overlay,
            .dashboard-header,
            .alert,
            .form-card,
            .stock-out-history,
            .print-stock-out-btn { display: none !important; }
            .dashboard-layout,
            .dashboard-shell { display: block; width: 100%; }
            .stock-out-history {
                display: block;
                margin: 0;
                padding: 18px;
                border: 2px solid #0f766e;
                border-radius: 18px;
                box-shadow: none;
                background: #fff;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .stock-out-history .panel-head { margin-bottom: 14px; }
            .stock-out-history .panel-tag { color: #0f766e; }
            .stock-out-history h2::before { content: 'StockSync - '; }
            .stock-out-table-wrap { overflow: visible; border-color: #b7e4dd; }
            .stock-out-history table { font-size: 11px; }
            .stock-out-history th { background: #0f766e !important; }
            .stock-out-history th,
            .stock-out-history td { padding: 9px 10px; }
            .stock-out-history tfoot td { background: #ccfbf1 !important; }
            .stock-out-invoice {
                display: block;
                border: 2px solid #0f766e;
                border-radius: 18px;
                box-shadow: none;
                padding: 18px;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .stock-out-invoice th { background: #0f766e !important; }
            .stock-out-invoice tfoot td { background: #ccfbf1 !important; }
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

        <div class="dashboard-shell page-shell transaction-page-shell">
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
                    <div class="field customer-field">
                        <label for="customer_name">Customer Name (Optional)</label>
                        <input type="text" id="customer_name" name="customer_name" placeholder="Enter customer name if available">
                    </div>
                    
                    <div class="stock-out-items" id="stockOutItems">
                        <div class="stock-out-item">
                            <div class="field">
                                <label>Product</label>
                                <select name="product_id[]" required>
                                    <option value="">Select a product</option>
                                    <?php foreach ($products as $product): ?>
                                        <option value="<?= (int)$product['id'] ?>" data-price="<?= htmlspecialchars($product['sale_price']) ?>"><?= htmlspecialchars($product['name']) ?> (Available: <?= (int)$product['quantity'] ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="field">
                                <label>Quantity</label>
                                <input type="number" name="quantity[]" min="1" step="1" placeholder="e.g. 12" required>
                            </div>
                            <div class="field">
                                <label>Unit Price</label>
                                <input type="number" name="unit_price[]" min="0" step="0.01" placeholder="0.00" required>
                            </div>
                            <div class="field">
                                <label>Notes</label>
                                <input type="text" name="notes[]" placeholder="Optional remarks">
                            </div>
                            <button type="button" class="remove-stock-out secondary-btn">Remove</button>
                        </div>
                    </div>
                    <button type="button" class="secondary-btn add-stock-out" id="addStockOut">+ Add Another Item</button>

                    <div class="submit-row">
                        <a href="/Project_IMS/dashboard.php" class="secondary-btn">Cancel</a>
                        <button type="submit" class="primary-btn">Save Stock Out</button>
                    </div>
                </form>
            </section>

            <?php if ($lastInvoiceItems): ?>
            <section class="stock-out-invoice">
                <div class="stock-out-invoice-head">
                    <div>
                        <p class="panel-tag">IMS</p>
                        <h2>StockSync Stock Out Invoice</h2>
                        <span><?= date('Y-m-d H:i') ?></span>
                    </div>
                    <div class="invoice-customer"><strong>Customer:</strong> <?= htmlspecialchars($lastInvoiceCustomer ?: 'Walk-in Customer') ?></div>
                    <button type="button" class="secondary-btn print-stock-out-btn" id="printInvoiceBtn">Print / Save PDF</button>
                </div>
                <table>
                    <thead>
                        <tr><th>Product</th><th>Qty</th><th>Unit Price</th><th>Total</th><th>Notes</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($lastInvoiceItems as $invoiceItem): ?>
                            <tr>
                                <td><?= htmlspecialchars($invoiceItem['product_name']) ?></td>
                                <td><?= (int)$invoiceItem['quantity'] ?></td>
                                <td>NPR <?= number_format($invoiceItem['unit_price'], 2) ?></td>
                                <td>NPR <?= number_format($invoiceItem['total_price'], 2) ?></td>
                                <td><?= htmlspecialchars($invoiceItem['notes'] ?: '-') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr><td>Grand Total</td><td><?= $lastInvoiceQuantity ?></td><td></td><td>NPR <?= number_format($lastInvoiceAmount, 2) ?></td><td></td></tr>
                    </tfoot>
                </table>
            </section>
            <?php endif; ?>

            <section class="stock-out-history">
                <div class="panel-head">
                    <div>
                        <p class="panel-tag">Stock Out</p>
                        <h2>History</h2>
                    </div>
                </div>

                <div class="stock-out-table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Product</th>
                                <th>Qty</th>
                                <th>Unit Price</th>
                                <th>Total</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!$stockOutTransactions): ?>
                                <tr><td colspan="6">No stock-out records found.</td></tr>
                            <?php else: ?>
                                <?php foreach ($stockOutTransactions as $stockOutTransaction): ?>
                                    <tr>
                                        <td><?= htmlspecialchars(date('Y-m-d', strtotime($stockOutTransaction['txn_date']))) ?></td>
                                        <td><?= htmlspecialchars($stockOutTransaction['product_name'] ?? 'Unknown Product') ?></td>
                                        <td><?= (int)$stockOutTransaction['quantity'] ?></td>
                                        <td>NPR <?= number_format((float)$stockOutTransaction['unit_price'], 2) ?></td>
                                        <td>NPR <?= number_format((float)$stockOutTransaction['total_price'], 2) ?></td>
                                        <td><?= htmlspecialchars($stockOutTransaction['notes'] ?: '-') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="2">Grand Total</td>
                                <td><?= $totalStockOutQuantity ?></td>
                                <td></td>
                                <td>NPR <?= number_format($totalStockOutAmount, 2) ?></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </section>
        </div>
    </div>

    <script src="/Project_IMS/assests/js/dashboard.js"></script>
    <script>
        const stockOutItems = document.getElementById('stockOutItems');
        const addStockOut = document.getElementById('addStockOut');
        const firstStockOutItem = stockOutItems.querySelector('.stock-out-item');

        addStockOut.addEventListener('click', () => {
            const newItem = firstStockOutItem.cloneNode(true);
            newItem.querySelectorAll('input').forEach((input) => { input.value = ''; });
            newItem.querySelector('select').selectedIndex = 0;
            stockOutItems.appendChild(newItem);
        });

        stockOutItems.addEventListener('click', (event) => {
            if (!event.target.classList.contains('remove-stock-out')) {
                return;
            }
            const items = stockOutItems.querySelectorAll('.stock-out-item');
            if (items.length > 1) {
                event.target.closest('.stock-out-item').remove();
            }
        });

        stockOutItems.addEventListener('change', (event) => {
            if (event.target.tagName !== 'SELECT') {
                return;
            }
            const selectedOption = event.target.options[event.target.selectedIndex];
            const priceInput = event.target.closest('.stock-out-item').querySelector('input[name="unit_price[]"]');
            priceInput.value = selectedOption.dataset.price || '';
        });

        const printInvoiceBtn = document.getElementById('printInvoiceBtn');
        if (printInvoiceBtn) {
            printInvoiceBtn.addEventListener('click', () => window.print());
        }
    </script>
</body>
</html>
