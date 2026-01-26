<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'auth_staff.php';

$staff_id = $_SESSION['user_id'];

/* ===============================
   DATABASE
================================ */
require_once '../includes/db.php';

/*
  Staff sees ONLY their own sales
  Total is computed from sale_items
*/
$stmt = $pdo->prepare("
    SELECT 
        s.id,
        s.invoice_no,
        s.customer_name,
        COALESCE(SUM(si.line_total), 0) AS total_amount,
        s.created_at
    FROM sales s
    LEFT JOIN sale_items si ON si.sale_id = s.id
    WHERE s.user_id = ?
    GROUP BY s.id
    ORDER BY s.created_at DESC
");
$stmt->execute([$staff_id]);

$sales = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>My Sales</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

<style>
:root{
    --bg:#121212;
    --panel:#1a1a1a;
    --muted:#bdbdbd;
    --accent:#00ff9d;
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
}
.header-row{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:15px;
}
h2{
    color:var(--accent);
}
.table{
    width:100%;
    border-collapse:collapse;
}
.table th,
.table td{
    padding:12px;
    border-bottom:1px solid #222;
    text-align:left;
}
.table th{
    color:var(--muted);
}
.btn{
    padding:6px 10px;
    border-radius:6px;
    text-decoration:none;
    font-size:14px;
    display:inline-flex;
    align-items:center;
    gap:6px;
}
.btn-view{
    background:var(--accent);
    color:#000;
}
.btn-add{
    background:var(--accent);
    color:#000;
    padding:8px 14px;
}
.empty{
    text-align:center;
    color:var(--muted);
    padding:20px;
}

@media(max-width:720px){
    .page-container{
        margin-left:0;
        margin-top:20px;
        padding:20px;
    }
    .header-row{
        flex-direction:column;
        align-items:flex-start;
        gap:10px;
    }
}
</style>
</head>

<body>

<?php include 'layout/sidebar_staff.php'; ?>
<?php include 'layout/header.php'; ?>

<div class="page-container">
<div class="card">

<div class="header-row">
    <h2><i class="bi bi-receipt"></i> My Sales</h2>
    <a href="add_sale.php" class="btn btn-add">
        <i class="bi bi-plus-circle"></i> New Sale
    </a>
</div>

<table class="table">
<thead>
<tr>
    <th>Invoice</th>
    <th>Customer</th>
    <th>Total</th>
    <th>Date</th>
    <th>Action</th>
</tr>
</thead>
<tbody>

<?php if (empty($sales)): ?>
<tr>
    <td colspan="5" class="empty">No sales recorded yet.</td>
</tr>
<?php else: ?>
<?php foreach ($sales as $s): ?>
<tr>
    <td><?= htmlspecialchars($s['invoice_no']) ?></td>
    <td><?= htmlspecialchars($s['customer_name']) ?></td>
    <td><?= number_format($s['total_amount'], 2) ?></td>
    <td><?= date('d M Y', strtotime($s['created_at'])) ?></td>
    <td>
        <a class="btn btn-view"
           href="sale_items.php?sale_id=<?= $s['id'] ?>"
           title="View Sale">
            <i class="bi bi-eye"></i> View
        </a>
    </td>
</tr>
<?php endforeach; ?>
<?php endif; ?>

</tbody>
</table>

</div>
</div>

<?php include '../layout/footer.php'; ?>
</body>
</html>
