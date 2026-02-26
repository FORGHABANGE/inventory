<?php
include 'auth_staff.php';

/* ===============================
   DB CONNECTION
================================ */
require_once '../includes/db.php';

/* ======================================================
   AUTO-LOG STOCK OUT FROM SALES
   ------------------------------------------------------
   - Deduct stock for sale items
   - Insert OUT movement if not exists
====================================================== */
$saleItems = $pdo->query("
    SELECT 
        si.id AS sale_item_id,
        si.product_id,
        si.quantity,
        s.invoice_no,
        s.user_id
    FROM sale_items si
    JOIN sales s ON s.id = si.sale_id
")->fetchAll(PDO::FETCH_ASSOC);

$checkStmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM stock_movements 
    WHERE reference = ?
      AND product_id = ?
      AND type = 'OUT'
");

$insertStmt = $pdo->prepare("
    INSERT INTO stock_movements (product_id, type, quantity, reference, note, user_id)
    VALUES (?,?,?,?,?,?)
");

$updateStockStmt = $pdo->prepare("
    UPDATE products SET quantity = quantity - ? WHERE id = ?
");

foreach ($saleItems as $item) {
    $reference = 'SALE-' . $item['invoice_no'];

    $checkStmt->execute([$reference, $item['product_id']]);
    $exists = $checkStmt->fetchColumn();

    if (!$exists) {
        // Insert stock OUT
        $insertStmt->execute([
            $item['product_id'],
            'OUT',
            $item['quantity'],
            $reference,
            'Auto stock deduction from sale',
            $item['user_id']
        ]);
        // Deduct from products table
        $updateStockStmt->execute([$item['quantity'], $item['product_id']]);
    }
}

/* ===============================
   FETCH STOCK MOVEMENTS
================================ */
$stmt = $pdo->query("
    SELECT 
        sm.id,
        p.name AS product_name,
        sm.type,
        sm.quantity,
        sm.reference,
        sm.note,
        u.username
    FROM stock_movements sm
    LEFT JOIN products p ON sm.product_id = p.id
    LEFT JOIN users u ON sm.user_id = u.id
    ORDER BY sm.id DESC
");

$movements = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Stock Movements</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

<style>
body{
    background:#121212;
    font-family:Poppins,sans-serif;
    color:#fff;
    margin:0;
}
.page-container{
    margin-left:240px;
    margin-top:90px;
    padding:25px;
}
.card{
    background:#1a1a1a;
    padding:20px;
    border-radius:12px;
}
h2{
    color:#00ff9d;
    margin-bottom:15px;
}
.table{
    width:100%;
    border-collapse:collapse;
}
.table th,.table td{
    padding:12px;
    border-bottom:1px solid #333;
    text-align:center;
}
.table th{
    color:#bdbdbd;
}
.badge-in{
    color:#00ff9d;
    font-weight:600;
}
.badge-out{
    color:#ff4d4d;
    font-weight:600;
}

/* Mobile Responsive */
@media (max-width: 1024px) {
    .page-container { margin-left: 0; margin-top: 100px; padding: 15px; }
    .table { font-size: 13px; }
    th, td { padding: 8px; }
}

@media (max-width: 768px) {
    .page-container { margin-left: 0; padding: 12px; }
    .table { font-size: 12px; }
    th, td { padding: 6px; }
}

@media (max-width: 480px) {
    .page-container { padding: 10px; }
    .table, .table thead, .table tbody, .table th, .table td, .table tr { display: block; }
    .table thead { display: none; }
    .table tr { margin-bottom: 10px; border: 1px solid #222; padding: 8px; }
    .table td { padding: 5px 0; font-size: 11px; }
}
</style>
</head>

<body>

<?php include "layout/sidebar_staff.php"; ?>
<?php include "layout/header.php"; ?>

<div class="page-container">

<div class="card">
<h2><i class="bi bi-arrow-left-right"></i> Stock Movements Log</h2>

<table class="table">
<thead>
<tr>
    <th>Product</th>
    <th>Type</th>
    <th>Quantity</th>
    <th>Reference</th>
    <th>Note</th>
    <th>User</th>
</tr>
</thead>
<tbody>

<?php if ($movements): ?>
    <?php foreach ($movements as $m): ?>
    <tr>
        <td><?= htmlspecialchars($m['product_name']) ?></td>
        <td>
            <?php if ($m['type'] === 'IN'): ?>
                <span class="badge-in">IN</span>
            <?php else: ?>
                <span class="badge-out">OUT</span>
            <?php endif; ?>
        </td>
        <td><?= htmlspecialchars($m['quantity']) ?></td>
        <td><?= htmlspecialchars($m['reference']) ?></td>
        <td><?= htmlspecialchars($m['note']) ?></td>
        <td><?= htmlspecialchars($m['username'] ?? 'System') ?></td>
    </tr>
    <?php endforeach; ?>
<?php else: ?>
<tr>
    <td colspan="6" style="padding:20px;color:#bbb;">No stock movements recorded</td>
</tr>
<?php endif; ?>

</tbody>
</table>

</div>
</div>

<?php include '../layout/footer.php'; ?>
</body>
</html>
