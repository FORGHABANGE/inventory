<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

/* ===============================
   AUTH & ROLE CHECK
================================ */
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2) {
    header("Location: ../login.php");
    exit;
}

$staff_id = $_SESSION['user_id'];
$staff_name = $_SESSION['full_name'] ?? 'Staff';

/* ===============================
   DATABASE CONNECTION
================================ */
require_once '../includes/db.php';

/* ===============================
   FETCH SALES FOR STAFF
================================ */
$stmt = $pdo->prepare("
    SELECT s.*, COALESCE(SUM(si.line_total),0) as total_amount_calc
    FROM sales s
    LEFT JOIN sale_items si ON s.id = si.sale_id
    WHERE s.user_id = ?
    GROUP BY s.id
    ORDER BY s.created_at DESC
");
$stmt->execute([$staff_id]);
$sales = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Staff Sales</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

<style>
:root{
    --bg:#121212;
    --panel:#1a1a1a;
    --muted:#bdbdbd;
    --accent:#00ff9d;
    --danger:#ff4d4d;
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

h2{
    color:var(--accent);
    margin-bottom:20px;
    display:flex;
    align-items:center;
    gap:10px;
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

.table{
    width:100%;
    border-collapse:collapse;
    table-layout:fixed;
}

.table th, .table td{
    padding:12px;
    border-bottom:1px solid #222;
    text-align:center;
}

.table th{
    color:var(--muted);
}

.action-links{
    display:flex;
    justify-content:center;
    gap:8px;
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
<?php include '../layout/header.php'; ?>

<div class="page-container">

<h2><i class="bi bi-receipt"></i> My Sales</h2>

<!-- Add Sale button -->
<a href="add_sale_items.php?sale_id=new" class="btn" style="margin-bottom:12px;">
    <i class="bi bi-plus-circle"></i> Add Sale
</a>

<!-- Sales Table -->
<table class="table">
    <thead>
        <tr>
            <th>Invoice</th>
            <th>Customer</th>
            <th>Total Amount</th>
            <th>Paid Amount</th>
            <th>Balance</th>
            <th>Date</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    <?php if(empty($sales)): ?>
        <tr><td colspan="7">No sales recorded yet.</td></tr>
    <?php else: ?>
        <?php foreach($sales as $s): 
            $balance = $s['total_amount_calc'] - $s['paid_amount'];
        ?>
        <tr>
            <td><?= htmlspecialchars($s['invoice_no']) ?></td>
            <td><?= htmlspecialchars($s['customer_name']) ?></td>
            <td><?= number_format($s['total_amount_calc'],2) ?></td>
            <td><?= number_format($s['paid_amount'],2) ?></td>
            <td><?= number_format($balance,2) ?></td>
            <td><?= date('d M Y', strtotime($s['created_at'])) ?></td>
            <td class="action-links">
                <!-- Staff can only view sale items -->
                <a href="view_sale_items.php?sale_id=<?= $s['id'] ?>" class="btn">
                    <i class="bi bi-eye"></i>
                </a>
            </td>
        </tr>
        <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
</table>

</div>

<?php include '../layout/footer.php'; ?> 
</body>
</html>
