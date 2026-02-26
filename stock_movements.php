<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

/* ===============================
   AUTH: ADMIN ONLY
================================ */
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit;
}

require_once "includes/db.php";

/* ===============================
   FETCH STOCK MOVEMENTS
================================ */
$query = "
    SELECT 
        sm.id,
        sm.type,
        sm.quantity,
        sm.reference,
        sm.note,
        sm.created_at,
        p.name AS product_name,
        u.username AS user_name
    FROM stock_movements sm
    LEFT JOIN products p ON sm.product_id = p.id
    LEFT JOIN users u ON sm.user_id = u.id
    ORDER BY sm.id DESC
";

$stmt = $pdo->prepare($query);
$stmt->execute();
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
.page-header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:20px;
}
.page-header h2{
    color:#00ff9d;
    margin:0;
}
.actions-group button{
    background:#00ff9d;
    color:#000;
    border:none;
    padding:10px 16px;
    border-radius:8px;
    font-weight:600;
    cursor:pointer;
    margin-left:10px;
}
.actions-group button:hover{
    opacity:0.9;
}
.table{
    width:100%;
    border-collapse:collapse;
    background:#1a1a1a;
    border-radius:12px;
    overflow:hidden;
}
.table thead{
    background:#00ff9d;
    color:#000;
}
.table th,.table td{
    padding:12px;
    border-bottom:1px solid #333;
    text-align:center;
}
.type-in{color:#00ff9d;font-weight:600;}
.type-out{color:#ff4d4d;font-weight:600;}
.type-adjust{color:#ffa500;}

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

<?php include "layout/sidebar.php"; ?>
<?php include "layout/header.php"; ?>

<div class="page-container">

<div class="page-header">
    <h2><i class="bi bi-arrow-left-right"></i> Stock Movements</h2>

    <div class="actions-group">
        <button onclick="location.href='stock_in.php'">
            <i class="bi bi-plus-circle"></i> Stock In
        </button>
        <button onclick="location.href='stock_out.php'">
            <i class="bi bi-dash-circle"></i> Stock Out
        </button>
        <button onclick="location.href='stock_adjust.php'">
            <i class="bi bi-sliders"></i> Adjust
        </button>
    </div>
</div>

<table class="table">
<thead>
<tr>
    <th>Date</th>
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
    <?php foreach ($movements as $m): 
        $type = strtoupper($m['type']);
        $typeClass = ($type === 'IN') ? 'type-in' :
                     (($type === 'OUT') ? 'type-out' : 'type-adjust');
        $typeLabel = ($type === 'IN') ? 'Stock In' :
                     (($type === 'OUT') ? 'Stock Out' : 'Adjustment');
    ?>
    <tr>
        <td><?= date("Y-m-d H:i", strtotime($m['created_at'])) ?></td>
        <td><?= htmlspecialchars($m['product_name'] ?? '—') ?></td>
        <td class="<?= $typeClass ?>"><?= $typeLabel ?></td>
        <td><?= htmlspecialchars($m['quantity']) ?></td>
        <td><?= htmlspecialchars($m['reference'] ?? '-') ?></td>
        <td><?= htmlspecialchars($m['note'] ?? '-') ?></td>
        <td><?= htmlspecialchars($m['user_name'] ?? 'System') ?></td>
    </tr>
    <?php endforeach; ?>
<?php else: ?>
<tr>
    <td colspan="7" style="padding:20px;color:#aaa;">No stock movements recorded</td>
</tr>
<?php endif; ?>

</tbody>
</table>

</div>

<?php include 'layout/footer.php'; ?>
</body>
</html>
