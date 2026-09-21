<?php

session_start();
require $_SERVER['DOCUMENT_ROOT'] . '/Project_IMS/includes/db.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: /Project_IMS/index.php");
  exit;
}

$type = $_GET['type'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$product_id = $_GET['product_id'] ?? '';
$supplier_id = $_GET['supplier_id'] ?? '';

$query = "
    SELECT t.*, p.name AS product_name, p.buy_price, p.sale_price, u.full_name
    FROM txns t
    LEFT JOIN products p ON t.product_id = p.id
    LEFT JOIN users u ON t.user_id = u.id
    WHERE 1 = 1
";
$params = [];

if ($type !== '') {
    $query .= " AND t.type = ?";
    $params[] = strtolower($type);
}

if (!empty($date_from)) {
    $query .= " AND DATE(t.txn_date) >= ?";
    $params[] = $date_from;
}

if (!empty($date_to)) {
    $query .= " AND DATE(t.txn_date) <= ?";
    $params[] = $date_to;
}

if (!empty($product_id)) {
    $query .= " AND t.product_id = ?";
    $params[] = $product_id;
}

if (!empty($supplier_id)) {
    $query .= " AND p.supplier_id = ?";
    $params[] = $supplier_id;
}

$query .= " ORDER BY t.txn_date DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$transactions = $stmt->fetchAll();

$total_in = 0;
$total_out = 0;
$total_in_value = 0;
$total_out_value = 0;
$grand_total_quantity = 0;
$grand_total_amount = 0;
$profit_detail_rows = [];

foreach ($transactions as $transaction) {
    $quantity = (int)($transaction['quantity'] ?? 0);
    $value = (float)($transaction['total_price'] ?? 0);
    $grand_total_quantity += $quantity;
    $grand_total_amount += $value;

    if (strtolower((string)($transaction['type'] ?? '')) === 'in') {
        $total_in += $quantity;
        $total_in_value += $value;
    } elseif (strtolower((string)($transaction['type'] ?? '')) === 'out') {
        $total_out += $quantity;
        $total_out_value += $value;
        $buyPrice = (float)($transaction['buy_price'] ?? 0);
        $profit_detail_rows[] = [
            'date' => date('Y-m-d', strtotime($transaction['txn_date'])),
            'product_name' => $transaction['product_name'] ?? 'Unknown Product',
            'quantity' => $quantity,
            'buy_price' => $buyPrice,
            'sale_price' => (float)($transaction['unit_price'] ?? 0),
            'cost' => $quantity * $buyPrice,
            'revenue' => $value,
            'profit' => $value - ($quantity * $buyPrice)
        ];
    }
}

$profitDetailTotalCost = array_sum(array_column($profit_detail_rows, 'cost'));
$profitDetailTotalRevenue = array_sum(array_column($profit_detail_rows, 'revenue'));
$profitDetailTotal = array_sum(array_column($profit_detail_rows, 'profit'));

$profitLoss = $total_out_value - $total_in_value;
$profitLossLabel = $profitLoss > 0 ? 'Profit' : ($profitLoss < 0 ? 'Loss' : 'Break-even');
$profitLossClass = $profitLoss > 0 ? 'profit' : ($profitLoss < 0 ? 'loss' : 'break-even');

$products = $pdo->query("SELECT id, name FROM products ORDER BY name")->fetchAll();
$suppliers = $pdo->query("SELECT id, name FROM suppliers ORDER BY name")->fetchAll();

function formatCurrency($amount) {
    return 'NPR ' . number_format((float)$amount, 2);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports | IMS</title>
    <link rel="stylesheet" href="/Project_IMS/assests/css/dashboard.css">
    <link rel="stylesheet" href="/Project_IMS/assests/css/sidebar-submenu.css">
    <style>
        .report-page-shell {
            width: 100%;
            display: grid;
            gap: 22px;
        }

        .report-page-shell .header-metrics {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
        }

        .report-page-shell .header-metrics .metric-card {
            width: 100%;
            min-width: 0;
        }

        .report-summary {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 16px;
        }

        .report-summary .stat-card {
            min-width: 0;
        }

        .report-summary .profit-loss-card.profit strong { color: var(--success); }
        .report-summary .profit-loss-card.loss strong { color: var(--danger); }
        .report-summary .profit-loss-card.break-even strong { color: var(--muted); }

        .profit-detail-btn { margin-bottom: 16px; }
        .profit-details {
            display: none;
            background: rgba(255,255,255,0.92);
            border: 1px solid rgba(255,255,255,0.35);
            border-radius: 24px;
            padding: 22px;
            box-shadow: var(--shadow);
        }
        .profit-details.show { display: block; }
        .profit-details table { width: 100%; border-collapse: collapse; background: #fff; }
        .profit-details th, .profit-details td { padding: 11px 12px; border-bottom: 1px solid var(--line); text-align: left; }
        .profit-details th { color: #fff; background: var(--primary); font-size: 0.78rem; text-transform: uppercase; }
        .profit-details tfoot td { background: #ccfbf1; border-top: 2px solid var(--primary); font-weight: 800; }
        .profit-positive { color: var(--success); font-weight: 800; }
        .profit-negative { color: var(--danger); font-weight: 800; }

        .print-report-btn {
            border: none;
            border-radius: 12px;
            padding: 11px 16px;
            color: #fff;
            background: linear-gradient(135deg, var(--primary), var(--primary-2));
            box-shadow: 0 10px 20px rgba(15, 118, 110, 0.2);
            font: inherit;
            font-weight: 700;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .print-report-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 14px 24px rgba(15, 118, 110, 0.25);
        }

        .print-report-header {
            display: none;
        }

        .print-total-row {
            display: none;
        }

        @media (max-width: 700px) {
            .report-page-shell .header-metrics {
                grid-template-columns: 1fr;
            }
        }

        .filter-panel {
            background: rgba(255, 255, 255, 0.14);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 24px;
            padding: 18px;
        }

        .filter-form {
            display: grid;
            grid-template-columns: repeat(6, minmax(0, 1fr));
            gap: 14px;
            align-items: end;
        }

        .filter-form label {
            display: grid;
            gap: 8px;
            color: #fff;
            font-size: 0.82rem;
            font-weight: 700;
        }

        .filter-form input,
        .filter-form select {
            width: 100%;
            border: 1px solid rgba(15, 23, 42, 0.12);
            border-radius: 12px;
            padding: 12px 14px;
            background: rgba(255, 255, 255, 0.96);
            color: var(--ink);
            outline: none;
        }

        .filter-form input:focus,
        .filter-form select:focus {
            border-color: rgba(15, 118, 110, 0.4);
            box-shadow: 0 0 0 4px rgba(20, 184, 166, 0.15);
        }

        .table-wrap {
            overflow-x: auto;
            border-radius: 18px;
            border: 1px solid var(--line);
            background: white;
        }

        .report-table {
            width: 100%;
            min-width: 900px;
            border-collapse: collapse;
        }

        .report-table th,
        .report-table td {
            padding: 14px 16px;
            border-bottom: 1px solid var(--line);
            text-align: left;
        }

        .report-table thead th {
            background: rgba(15, 118, 110, 0.06);
            color: var(--primary);
            font-size: 0.8rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .report-table tbody tr:hover {
            background: rgba(20, 184, 166, 0.03);
        }
        .report-table tfoot td {
            padding: 14px 16px;
            border-top: 2px solid var(--primary);
            background: rgba(20, 184, 166, 0.1);
            color: var(--ink);
            font-weight: 800;
        }

        .type-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .type-badge.in {
            background: rgba(22, 163, 74, 0.12);
            color: var(--success);
        }

        .type-badge.out {
            background: rgba(239, 68, 68, 0.11);
            color: var(--danger);
        }

        .empty-state {
            text-align: center;
            padding: 24px 16px;
            color: var(--muted);
            font-weight: 600;
        }

        @media (max-width: 1100px) {
            .report-summary {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .filter-form {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 640px) {
            .report-summary {
                grid-template-columns: 1fr;
            }

            .filter-form {
                grid-template-columns: 1fr;
            }

            .dashboard-header {
                padding: 22px 18px;
                flex-direction: column;
                align-items: flex-start;
            }

            .header-metrics {
                width: 100%;
                grid-template-columns: 1fr 1fr;
            }

            .panel {
                border-radius: 20px;
                padding: 18px;
            }
        }

        @media print {
            @page {
                size: A4 landscape;
                margin: 14mm;
            }

            body {
                background: #fff;
                padding: 0;
                color: #0f172a;
            }

            .sidebar,
            .mobile-sidebar-toggle,
            .sidebar-overlay,
            .filter-panel,
            .print-report-btn,
            .profit-detail-btn,
            .profit-details,
            .report-page-shell > .dashboard-header,
            .report-summary,
            .report-page-shell .panel-head {
                display: none !important;
            }

            .dashboard-layout,
            .report-page-shell {
                display: block;
                width: 100%;
            }

            .report-page-shell {
                margin: 0;
            }

            .dashboard-header,
            .stat-card,
            .panel {
                background: #fff;
                border: 1px solid #d1d5db;
                box-shadow: none;
                break-inside: avoid;
            }

            .dashboard-header {
                margin-bottom: 14px;
            }

            .report-summary {
                margin-bottom: 14px;
            }

            .table-wrap {
                overflow: visible;
                border-color: #d1d5db;
            }

            .report-table {
                width: 100%;
                min-width: 0;
                font-size: 10px;
                table-layout: fixed;
            }

            .report-table thead th:nth-child(1),
            .report-table tbody td:nth-child(1) { width: 18%; }
            .report-table thead th:nth-child(2),
            .report-table tbody td:nth-child(2) { width: 20%; }
            .report-table thead th:nth-child(4),
            .report-table tbody td:nth-child(4) { width: 12%; }
            .report-table thead th:nth-child(5),
            .report-table tbody td:nth-child(5) { width: 25%; }
            .report-table thead th:nth-child(6),
            .report-table tbody td:nth-child(6) { width: 25%; }

            .report-table th,
            .report-table td {
                padding: 8px 9px;
            }
            .report-table tfoot td {
                background: #ccfbf1 !important;
                border-top: 2px solid #0f766e;
                color: #0f172a;
            }

            .report-table .screen-total-row {
                display: none;
            }

            .print-total-row {
                display: grid;
                grid-template-columns: 18% 20% 12% 25% 25%;
                width: 100%;
                border: 1px solid #b7e4dd;
                border-top: 2px solid #0f766e;
                background: #ccfbf1 !important;
            }

            .print-total-row span {
                display: block;
                padding: 12px 10px;
                font-size: 12px;
                font-weight: 800;
                white-space: nowrap;
                color: #0f172a;
            }

            .report-table thead th {
                background: #0f766e !important;
                color: #fff !important;
            }

            .report-table thead th:nth-child(3),
            .report-table tbody td:nth-child(3),
            .report-table thead th:nth-child(7),
            .report-table tbody td:nth-child(7),
            .report-table thead th:nth-child(8),
            .report-table tbody td:nth-child(8) {
                display: none;
            }

            .type-badge {
                border: 1px solid #cbd5e1;
                background: #fff !important;
                color: #0f172a !important;
            }

            .panel {
                border: 2px solid #0f766e;
                border-radius: 18px;
                padding: 20px;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .print-report-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 16px;
                margin-bottom: 18px;
                padding-bottom: 14px;
                border-bottom: 2px solid #14b8a6;
                color: #0f172a;
            }

            .print-report-brand {
                display: grid;
                gap: 3px;
            }

            .print-report-brand span {
                color: #0f766e;
                font-size: 11px;
                font-weight: 800;
                letter-spacing: 0.22em;
                text-transform: uppercase;
            }

            .print-report-brand strong {
                font-size: 23px;
                letter-spacing: 0.02em;
            }

            .print-report-title {
                color: #0f766e;
                font-size: 18px;
                font-weight: 800;
                letter-spacing: 0.08em;
                text-transform: uppercase;
            }
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
                    <button type="button" class="nav-parent" aria-expanded="false">
                        <span>Products</span>
                        <svg class="chevron" width="14" height="14" viewBox="0 0 24 24" fill="none">
                            <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                    <ul class="submenu">
                        <li><a href="/Project_IMS/products/add.php">Add Product</a></li>
                        <li><a href="/Project_IMS/products/list.php">List Products</a></li>
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
                        <li><a href="/Project_IMS/categories/add.php">Add Categories</a></li>
                        <li><a href="/Project_IMS/categories/list.php">List Categories</a></li>
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
                <strong>Reports generated from recent stock movements.</strong>
            </div>

            <a class="logout-btn" id="logoutBtn" href="/Project_IMS/auth/logout.php">Logout</a>
        </aside>

        <div class="dashboard-shell report-page-shell">
            <header class="dashboard-header">
                <div>
                    <p class="eyebrow">Reports</p>
                    <h1>Inventory Report</h1>
                    <p class="subtitle">Review stock movement, product activity, and transaction history across your inventory.</p>
                </div>
                <div class="header-metrics">
                    <div class="metric-card">
                        <span>Transactions</span>
                        <strong><?= count($transactions) ?></strong>
                    </div>
                    <div class="metric-card">
                        <span>Generated</span>
                        <strong><?= date('d M Y') ?></strong>
                    </div>
                </div>
            </header>

            <section class="report-summary">
                <article class="stat-card">
                    <span>Total Stock In</span>
                    <strong><?= $total_in ?></strong>
                </article>
                <article class="stat-card">
                    <span>Total Stock Out</span>
                    <strong><?= $total_out ?></strong>
                </article>
                <article class="stat-card">
                    <span>Net Movement</span>
                    <strong><?= $total_in - $total_out ?></strong>
                </article>
                <article class="stat-card">
                    <span>Report Value</span>
                    <strong><?= formatCurrency($total_in_value + $total_out_value) ?></strong>
                </article>
            </section>

            <?php if ($_SESSION['role'] === 'admin'): ?>
            <button type="button" class="primary-btn profit-detail-btn" id="profitDetailBtn">Check Profit / Loss</button>

            <section class="profit-details" id="profitDetails">
                <div class="panel-head">
                    <div>
                        <p class="panel-tag">Financial Details</p>
                        <h2>Profit / Loss Breakdown</h2>
                    </div>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr><th>Date</th><th>Product</th><th>Qty</th><th>Buy Price</th><th>Sale Price</th><th>Cost</th><th>Revenue</th><th>Profit / Loss</th></tr>
                        </thead>
                        <tbody>
                            <?php if (!$profit_detail_rows): ?>
                                <tr><td colspan="8">No Stock Out transactions available for profit/loss.</td></tr>
                            <?php else: ?>
                                <?php foreach ($profit_detail_rows as $profitRow): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($profitRow['date']) ?></td>
                                        <td><?= htmlspecialchars($profitRow['product_name']) ?></td>
                                        <td><?= $profitRow['quantity'] ?></td>
                                        <td><?= formatCurrency($profitRow['buy_price']) ?></td>
                                        <td><?= formatCurrency($profitRow['sale_price']) ?></td>
                                        <td><?= formatCurrency($profitRow['cost']) ?></td>
                                        <td><?= formatCurrency($profitRow['revenue']) ?></td>
                                        <td class="<?= $profitRow['profit'] >= 0 ? 'profit-positive' : 'profit-negative' ?>"><?= formatCurrency($profitRow['profit']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                        <tfoot>
                            <tr><td colspan="5">Grand Total</td><td><?= formatCurrency($profitDetailTotalCost) ?></td><td><?= formatCurrency($profitDetailTotalRevenue) ?></td><td><?= formatCurrency($profitDetailTotal) ?></td></tr>
                        </tfoot>
                    </table>
                </div>
            </section>
            <?php endif; ?>

            <section class="filter-panel">
                <form method="GET" class="filter-form">
                    <label>
                        Type
                        <select name="type">
                            <option value="">All</option>
                            <option value="in" <?= $type === 'in' ? 'selected' : '' ?>>Stock In</option>
                            <option value="out" <?= $type === 'out' ? 'selected' : '' ?>>Stock Out</option>
                        </select>
                    </label>

                    <label>
                        Product
                        <select name="product_id">
                            <option value="">All Products</option>
                            <?php foreach ($products as $product): ?>
                                <option value="<?= (int)$product['id'] ?>" <?= $product_id == $product['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($product['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>

                    <label>
                        Supplier
                        <select name="supplier_id">
                            <option value="">All Suppliers</option>
                            <?php foreach ($suppliers as $supplier): ?>
                                <option value="<?= (int)$supplier['id'] ?>" <?= $supplier_id == $supplier['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($supplier['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>

                    <label>
                        From
                        <input type="date" name="date_from" value="<?= htmlspecialchars($date_from) ?>">
                    </label>

                    <label>
                        To
                        <input type="date" name="date_to" value="<?= htmlspecialchars($date_to) ?>">
                    </label>

                    <button type="submit" class="primary-btn">Generate Report</button>
                </form>
            </section>

            <section class="panel">
                <div class="print-report-header">
                    <div class="print-report-brand">
                        <span>IMS</span>
                        <strong>StockSync</strong>
                    </div>
                    <span class="print-report-title">Report</span>
                </div>
                <div class="panel-head">
                    <div>
                        <p class="panel-tag">Report</p>
                        <h2>Report</h2>
                    </div>
                    <button type="button" class="print-report-btn" id="printReportBtn">Print / Save PDF</button>
                </div>

                <div class="table-wrap">
                    <table class="report-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Product</th>
                                <th>Type</th>
                                <th>Qty</th>
                                <th>Unit Price</th>
                                <th>Total</th>
                                <th>By</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($transactions)): ?>
                                <tr>
                                    <td colspan="8" class="empty-state">No report data found for the selected filters.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($transactions as $transaction): ?>
                                    <tr>
                                        <td><?= htmlspecialchars(date('Y-m-d', strtotime($transaction['txn_date']))) ?></td>
                                        <td><?= htmlspecialchars($transaction['product_name'] ?? 'Unknown Product') ?></td>
                                        <td><span class="type-badge <?= strtolower($transaction['type']) ?>"><?= htmlspecialchars(strtoupper($transaction['type'])) ?></span></td>
                                        <td><?= (int)$transaction['quantity'] ?></td>
                                        <td><?= formatCurrency($transaction['unit_price'] ?? 0) ?></td>
                                        <td><?= formatCurrency($transaction['total_price'] ?? 0) ?></td>
                                        <td><?= htmlspecialchars($transaction['full_name'] ?? 'System') ?></td>
                                        <td><?= htmlspecialchars($transaction['notes'] ?: '—') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                        <tfoot class="screen-total-row">
                            <tr>
                                <td colspan="2">Grand Total</td>
                                <td></td>
                                <td class="grand-total-quantity"><?= $grand_total_quantity ?></td>
                                <td></td>
                                <td><?= formatCurrency($grand_total_amount) ?></td>
                                <td></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                    <div class="print-total-row">
                        <span>Grand Total</span>
                        <span></span>
                        <span><?= $grand_total_quantity ?></span>
                        <span></span>
                        <span><?= formatCurrency($grand_total_amount) ?></span>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <script src="/Project_IMS/assests/js/dashboard.js"></script>
    <script>
        document.getElementById('printReportBtn').addEventListener('click', () => {
            window.print();
        });
        const profitDetailBtn = document.getElementById('profitDetailBtn');
        if (profitDetailBtn) {
            profitDetailBtn.addEventListener('click', () => {
                document.getElementById('profitDetails').classList.toggle('show');
            });
        }
    </script>
</body>
</html>
