<?php
require_once 'includes/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

/*
 We compute total_amount from sale_items to support
 single & multiple product purchases correctly
*/
$stmt = $pdo->query("
    SELECT 
        s.id,
        s.invoice_no,
        s.customer_name,
        COALESCE(SUM(si.line_total), 0) AS total_amount,
        s.paid_amount,
        s.created_at,
        u.username
    FROM sales s
    LEFT JOIN sale_items si ON si.sale_id = s.id
    LEFT JOIN users u ON s.user_id = u.id
    GROUP BY s.id
    ORDER BY s.created_at DESC
");

$sales = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Sales</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

<style>
:root{
    --bg:#121212; --panel:#1a1a1a; --muted:#bdbdbd;
    --accent:#00ff9d; --danger:#ff4d4d; --warn:#ffaa00;
}
body{margin:0;font-family:Poppins,sans-serif;background:var(--bg);color:#fff}
.page-container{margin-left:210px;margin-top:90px;padding:30px}
.card{background:var(--panel);border-radius:12px;padding:18px}
.header-row{display:flex;justify-content:space-between;align-items:center;margin-bottom:15px}
h2{color:var(--accent)}
.table{width:100%;border-collapse:collapse}
.table th,.table td{padding:12px;border-bottom:1px solid #222;text-align:left}
.table th{color:var(--muted)}
.btn{
    padding:6px 10px;
    border-radius:6px;
    text-decoration:none;
    font-size:14px;
    margin-right:4px;
    display:inline-block;
}
.btn-view{background:var(--accent);color:#000}
.btn-edit{background:var(--warn);color:#000}
.btn-del{background:var(--danger);color:#fff}
.btn-add{background:var(--accent);color:#000;padding:8px 14px}
.empty{text-align:center;color:var(--muted);padding:20px}

@media(max-width:720px){
    .page-container{margin-left:0;margin-top:20px;padding:20px}
    .header-row{flex-direction:column;align-items:flex-start;gap:10px}
}
</style>
</head>

<body>
<?php include 'layout/sidebar.php'; ?>
<?php include 'layout/header.php'; ?>

<div class="page-container">
<div class="card">

<div class="header-row">
    <h2><i class="bi bi-receipt"></i> Sales</h2>
    <a href="add_sale.php" class="btn btn-add">
        <i class="bi bi-plus-circle"></i> Add Sale
    </a>
</div>

<table class="table">
<thead>
<tr>
<th>Invoice</th>
<th>Customer</th>
<th>Total</th>
<th>Paid</th>
<th>Sold By</th>
<th>Date</th>
<th>Actions</th>
</tr>
</thead>
<tbody>

<?php if(empty($sales)): ?>
<tr>
    <td colspan="7" class="empty">No sales recorded yet.</td>
</tr>
<?php else: ?>
<?php foreach($sales as $s): ?>
<tr>
<td><?= htmlspecialchars($s['invoice_no']) ?></td>
<td><?= htmlspecialchars($s['customer_name']) ?></td>
<td><?= number_format($s['total_amount'],2) ?></td>
<td><?= number_format($s['paid_amount'],2) ?></td>
<td><?= htmlspecialchars($s['username'] ?? 'Admin') ?></td>
<td><?= date('d M Y',strtotime($s['created_at'])) ?></td>
<td>
    <a class="btn btn-view" href="sale_items.php?sale_id=<?= $s['id'] ?>" title="View">
        <i class="bi bi-eye"></i>
    </a>
    <a class="btn btn-edit" href="edit_sale.php?sale_id=<?= $s['id'] ?>" title="Edit">
        <i class="bi bi-pencil-square"></i>
    </a>
    <a class="btn btn-del" href="delete_sale.php?sale_id=<?= $s['id'] ?>"
       onclick="return confirm('Delete this sale?')" title="Delete">
        <i class="bi bi-trash"></i>
    </a>
</td>
</tr>
<?php endforeach; ?>
<?php endif; ?>

</tbody>
</table>

</div>
</div>

<?php include 'layout/footer.php'; ?>
</body>
</html>
