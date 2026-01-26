<?php
include 'auth_staff.php';

/* ===============================
   DATABASE
================================ */
require_once '../includes/db.php';

/* ===============================
   VALIDATE SALE
================================ */
if (!isset($_GET['sale_id']) || !is_numeric($_GET['sale_id'])) {
    die("Invalid sale selected.");
}
$sale_id = (int)$_GET['sale_id'];

/* ===============================
   FETCH SALE
================================ */
$saleStmt = $pdo->prepare("SELECT * FROM sales WHERE id=?");
$saleStmt->execute([$sale_id]);
$sale = $saleStmt->fetch(PDO::FETCH_ASSOC);

if (!$sale) {
    die("Sale not found.");
}

/* ===============================
   FETCH SALE ITEMS
================================ */
$itemsStmt = $pdo->prepare("
    SELECT si.id, p.name, si.quantity, si.unit_price, si.line_total
    FROM sale_items si
    JOIN products p ON si.product_id = p.id
    WHERE si.sale_id = ?
");
$itemsStmt->execute([$sale_id]);
$items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

/* ===============================
   RECALCULATE TOTAL
================================ */
$totalAmount = 0;
foreach ($items as $item) {
    $totalAmount += $item['line_total'];
}

/* Update sales total */
$updateTotal = $pdo->prepare("UPDATE sales SET total_amount=? WHERE id=?");
$updateTotal->execute([$totalAmount, $sale_id]);

$paidAmount = (float)$sale['paid_amount'];
?>

<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Sale Items</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

<style>
:root{
    --bg:#121212;
    --panel:#1a1a1a;
    --muted:#bdbdbd;
    --accent:#00ff9d;
    --card-shadow: rgba(0,0,0,0.6);
}
body{
    margin:0;
    font-family:Poppins,sans-serif;
    background:var(--bg);
    color:#fff;
}
.page-container{
    margin-left:210px;
    margin-top:90px;
    padding:30px;
}
.card{
    background:var(--panel);
    border-radius:12px;
    padding:18px;
    box-shadow:0 8px 30px var(--card-shadow);
}
h2{
    color:var(--accent);
    margin-bottom:18px;
    display:flex;
    align-items:center;
    gap:10px;
}
.table{
    width:100%;
    border-collapse:collapse;
}
.table th,
.table td{
    padding:12px;
    border-bottom:1px solid #222;
    text-align:center;
}
.table th{
    color:var(--muted);
}
.btn{
    display:inline-flex;
    align-items:center;
    gap:6px;
    background:var(--accent);
    color:#000;
    padding:8px 14px;
    border-radius:8px;
    border:none;
    cursor:pointer;
    font-weight:600;
    text-decoration:none;
}
.summary{
    margin-top:16px;
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(180px,1fr));
    gap:12px;
}
.summary div{
    background:#0f0f0f;
    padding:12px;
    border-radius:8px;
    text-align:center;
}
.summary span{
    color:var(--muted);
    font-size:13px;
    display:block;
}
@media(max-width:720px){
    .page-container{
        margin-left:0;
        margin-top:20px;
        padding:20px;
    }
}
</style>
</head>

<body>

<?php include 'layout/sidebar_staff.php'; ?>
<?php include 'layout/header.php'; ?>

<div class="page-container">
<div class="card">

<h2>
    <i class="bi bi-receipt"></i>
    Invoice <?= htmlspecialchars($sale['invoice_no']) ?>
</h2>

<a href="add_sale_items.php?sale_id=<?= $sale_id ?>" class="btn" style="margin-bottom:12px;">
    <i class="bi bi-plus-circle"></i> Add Item
</a>

<table class="table">
<thead>
<tr>
    <th>Product</th>
    <th>Quantity</th>
    <th>Unit Price</th>
    <th>Line Total</th>
</tr>
</thead>
<tbody>

<?php if (empty($items)): ?>
<tr>
    <td colspan="4">No items added yet.</td>
</tr>
<?php else: ?>
<?php foreach ($items as $item): ?>
<tr>
    <td><?= htmlspecialchars($item['name']) ?></td>
    <td><?= htmlspecialchars($item['quantity']) ?></td>
    <td><?= number_format($item['unit_price'], 2) ?></td>
    <td><?= number_format($item['line_total'], 2) ?></td>
</tr>
<?php endforeach; ?>
<?php endif; ?>

</tbody>
</table>

<div class="summary">
    <div>
        <span>Total Amount</span>
        <strong><?= number_format($totalAmount, 2) ?></strong>
    </div>
    <div>
        <span>Paid Amount</span>
        <strong><?= number_format($paidAmount, 2) ?></strong>
    </div>
</div>

<div style="margin-top:18px;">
    <a href="sales.php" class="btn" style="background:#2a2a2a;color:#bdbdbd;">
        <i class="bi bi-arrow-left"></i> Back to Sales
    </a>
</div>

</div>
</div>

<?php include '../layout/footer.php'; ?>
</body>
</html>
