<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'auth_staff.php';

$staff_id = $_SESSION['user_id'];
$staff_name = $_SESSION['full_name'] ?? 'Staff';

/* ===============================
   DATABASE CONNECTION
================================ */
require_once '../includes/db.php';

/* ===============================
   DASHBOARD METRICS
================================ */

// Total products
$stmt = $pdo->query("SELECT COUNT(*) FROM products WHERE is_active = 1");
$totalProducts = $stmt->fetchColumn();

// Total sales by this staff
$stmt = $pdo->prepare("
    SELECT COALESCE(SUM(total_amount),0)
    FROM sales
    WHERE user_id = ?
");
$stmt->execute([$staff_id]);
$totalSalesAmount = $stmt->fetchColumn();

// Today sales by staff
$stmt = $pdo->prepare("
    SELECT COALESCE(SUM(total_amount),0)
    FROM sales
    WHERE user_id = ?
    AND DATE(created_at) = CURDATE()
");
$stmt->execute([$staff_id]);
$todaySales = $stmt->fetchColumn();

// Low stock products (using varchar quantity, no numeric conversion)
$lowStockStmt = $pdo->query("
    SELECT name, quantity, reorder_level
    FROM products
    WHERE is_active = 1
    AND quantity <= reorder_level
    ORDER BY quantity ASC
    LIMIT 5
");
$lowStockProducts = $lowStockStmt->fetchAll(PDO::FETCH_ASSOC);

// Staff daily sales for chart (last 14 days)
$salesChartStmt = $pdo->prepare("SELECT DATE(created_at) AS sale_date, SUM(total_amount) AS total_amount FROM sales WHERE user_id = ? GROUP BY DATE(created_at) ORDER BY sale_date DESC LIMIT 14");
$salesChartStmt->execute([$staff_id]);
$salesChart = array_reverse($salesChartStmt->fetchAll(PDO::FETCH_ASSOC));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Staff Dashboard</title>
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
    margin-bottom:10px;
    display:flex;
    align-items:center;
    gap:10px;
}

.welcome{
    font-size:16px;
    color:#bdbdbd;
    margin-bottom:20px;
}

.cards{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
    gap:18px;
}

.card{
    background:var(--panel);
    padding:18px;
    border-radius:12px;
    box-shadow:0 8px 30px var(--card-shadow);
    cursor:pointer;
    transition:all .2s ease;
}
.card:hover{
    transform:translateY(-3px);
    
}

.card span{
    color:var(--muted);
    font-size:13px;
}

.card strong{
    font-size:26px;
    display:block;
    margin-top:6px;
}

.table{
    width:100%;
    margin-top:18px;
    border-collapse:collapse;
}

.table th,.table td{
    padding:12px;
    border-bottom:1px solid #222;
    text-align:left;
}

.table th{
    color:var(--muted);
}

/* Mobile Responsive */
@media (max-width: 1024px) {
    .page-container {
        margin-left: 0;
        margin-top: 100px;
        padding: 15px;
    }

    .cards {
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 12px;
    }

    .card {
        padding: 15px;
    }

    .card strong {
        font-size: 22px;
    }

    .table th, .table td {
        padding: 8px;
        font-size: 13px;
    }
}

@media (max-width: 768px) {
    .page-container {
        margin-left: 0;
        margin-top: 100px;
        padding: 12px;
    }

    .cards {
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 10px;
    }

    .card {
        padding: 12px;
        font-size: 14px;
    }

    .card strong {
        font-size: 20px;
    }

    .table th, .table td {
        padding: 6px;
        font-size: 12px;
    }
}

@media (max-width: 480px) {
    .page-container {
        margin-left: 0;
        margin-top: 100px;
        padding: 10px;
    }

    .cards {
        grid-template-columns: 1fr;
        gap: 8px;
    }

    .card {
        padding: 10px;
        font-size: 12px;
    }

    .card strong {
        font-size: 18px;
    }

    .table {
        font-size: 11px;
    }

    .table th, .table td {
        padding: 5px;
        font-size: 11px;
    }

    h2 {
        font-size: 18px;
    }
}
</style>
</head>

<body>

<?php include 'layout/sidebar_staff.php'; ?>  
<?php include 'layout/header.php'; ?>       

<div class="page-container">

<h2><i class="bi bi-speedometer2"></i> Staff Dashboard</h2>

<!-- DASHBOARD CARDS -->
<div class="cards">

    <div class="card" onclick="location.href='products.php';">
        <span>Total Products</span>
        <strong><?= $totalProducts ?></strong>
    </div>

    <div class="card" onclick="location.href='sales.php?filter=today';">
        <span>Today’s Sales</span>
        <strong><?= number_format($todaySales,2) ?></strong>
    </div>

    <div class="card" onclick="location.href='sales.php';">
        <span>Your Total Sales</span>
        <strong><?= number_format($totalSalesAmount,2) ?></strong>
    </div>

</div>

<!-- LOW STOCK ALERT -->
<?php if(!empty($lowStockProducts)): ?>
<div class="card" style="margin-top:30px;" onclick="location.href='stock_levels.php';">
    <h3 style="margin-top:0;color:var(--danger)">
        <i class="bi bi-exclamation-triangle"></i> Low Stock Alert
    </h3>

    <table class="table">
        <thead>
        <tr>
            <th>Product</th>
            <th>Quantity</th>
            <th>Reorder Level</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach($lowStockProducts as $p): ?>
        <tr>
            <td><?= htmlspecialchars($p['name']) ?></td>
            <td><?= $p['quantity'] ?></td>
            <td><?= $p['reorder_level'] ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<!-- STAFF DAILY SALES CHART -->
<div class="card" style="margin-top:20px; margin-bottom:30px;">
    <h3 style="margin-top:0;color:var(--accent)"><i class="bi bi-graph-up"></i> My Daily Sales</h3>
    <canvas id="staffSalesChart" style="width:100%; height:300px;"></canvas>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const staffLabels = <?= json_encode(array_column($salesChart, 'sale_date')) ?>;
const staffTotals = <?= json_encode(array_map('floatval', array_column($salesChart, 'total_amount'))) ?>;
const staffCtx = document.getElementById('staffSalesChart').getContext('2d');
new Chart(staffCtx, {
    type: 'line',
    data: {
        labels: staffLabels,
        datasets: [{
            label: 'My Sales (XAF)',
            data: staffTotals,
            borderColor: '#00ff9d',
            backgroundColor: 'rgba(0,255,157,0.12)',
            fill: true,
            tension: 0.3
        }]
    },
    options: { responsive: true }
});
</script>

</div>

<?php include '../layout/footer.php'; ?> 
</body>
</html>
