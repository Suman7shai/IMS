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

$query = "
    SELECT t.*, p.name AS product_name, u.full_name
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

$query .= " ORDER BY t.txn_date DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$transactions = $stmt->fetchAll();

$total_in = 0;
$total_out = 0;
$total_in_value = 0;
$total_out_value = 0;

foreach ($transactions as $transaction) {
    $quantity = (int)($transaction['quantity'] ?? 0);
    $value = (float)($transaction['total_price'] ?? 0);

    if (strtolower((string)($transaction['type'] ?? '')) === 'in') {
        $total_in += $quantity;
        $total_in_value += $value;
    } elseif (strtolower((string)($transaction['type'] ?? '')) === 'out') {
        $total_out += $quantity;
        $total_out_value += $value;
    }
}

$products = $pdo->query("SELECT id, name FROM products ORDER BY name")->fetchAll();

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

        .report-summary {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 16px;
        }

        .report-summary .stat-card {
            min-width: 0;
        }

        .filter-panel {
            background: rgba(255, 255, 255, 0.14);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 24px;
            padding: 18px;
        }

        .filter-form {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
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
                <a href="/Project_IMS/transactions/stock_in.php">Stock In</a>
                <a href="/Project_IMS/transactions/stock_out.php">Stock Out</a>
                <a href="/Project_IMS/reports/index.php">Reports</a>
                <a href="/Project_IMS/users/list.php">Users</a>
            </nav>

            <div class="sidebar-footer">
                <span>Live Inventory</span>
                <strong>Reports generated from recent stock movements.</strong>
            </div>

            <button type="button" class="logout-btn" id="logoutBtn">Logout</button>
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
                <div class="panel-head">
                    <div>
                        <p class="panel-tag">Transactions</p>
                        <h2>Report Log</h2>
                    </div>
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
                    </table>
                </div>
            </section>
        </div>
    </div>

    <script src="/Project_IMS/assests/js/dashboard.js"></script>
</body>
</html>
