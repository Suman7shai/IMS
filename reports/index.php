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
SELECT 
  t.*,
  p.name as product_name,
  u.full_name
FROM transactions t
JOIN products p ON t.product_id = p.id
JOIN users u ON t.user_id = u.id
WHERE 1 = 1
";
$params = [];


if(!empty($type)) {
  $query .= " AND t.type = ?";
  $params[] = $type;
}

if (!empty($date_from)) {
  $query .= " AND t.txn_date >= ?";
  $params[] = $date_from;
}

if (!empty($date_to)) {
  $query .= " AND t.txn_date <= ?";
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
  if ($transaction['type'] === 'IN') {
    $total_in += $transaction['quantity'];
    $total_in_value += $transaction['total_price'];
  } else {
    $total_out += $transaction['quantity'];
    $total_out_value += $transaction['total_price'];
  }
}

$products = $pdo->query("
  SELECT id, name FROM products ORDER BY name
")->fetchAll();

?>