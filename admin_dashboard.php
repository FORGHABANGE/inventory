<?php
include 'auth_admin.php';

// Database connection
require_once 'includes/db.php';

// Fetch metrics
$totalProducts = $pdo->query("SELECT COUNT(*) FROM products WHERE is_active = 1")->fetchColumn();
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$lowStock = $pdo->query("SELECT COUNT(*) FROM products WHERE quantity <= reorder_level AND is_active = 1")->fetchColumn();
$salesToday = $pdo->query("SELECT COALESCE(SUM(total_amount), 0) FROM sales WHERE DATE(created_at) = CURDATE()")->fetchColumn();
$newOrders = $pdo->query("SELECT COUNT(*) FROM sales WHERE DATE(created_at) = CURDATE()")->fetchColumn();

// Low stock detailed list (for admin dashboard)
$lowStockStmt = $pdo->query("SELECT name, quantity, reorder_level FROM products WHERE is_active = 1 AND quantity <= reorder_level ORDER BY quantity ASC LIMIT 8");
$lowStockProducts = $lowStockStmt->fetchAll(PDO::FETCH_ASSOC);

// Daily sales for chart (last 14 days)
$salesStmt = $pdo->query("SELECT DATE(created_at) AS sale_date, SUM(total_amount) AS total_amount FROM sales GROUP BY DATE(created_at) ORDER BY sale_date DESC LIMIT 14");
$sales = array_reverse($salesStmt->fetchAll(PDO::FETCH_ASSOC));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Inventory System</title>

    <!-- Bootstrap Icons -->
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
        padding:25px;
    }

    h2{ color:var(--accent); margin-bottom:10px; display:flex; align-items:center; gap:10px; }
    .welcome{ font-size:16px; color:var(--muted); margin-bottom:20px; }

    .cards{ display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:18px; }

    .card{ background:var(--panel); padding:18px; border-radius:12px; box-shadow:0 8px 30px var(--card-shadow); cursor:pointer; transition:all .2s ease; }
    .card:hover{ transform:translateY(-3px); }

    .card span{ color:var(--muted); font-size:13px; }
    .card strong{ font-size:26px; display:block; margin-top:6px; color:var(--accent); }

    .table{ width:100%; margin-top:12px; border-collapse:collapse; }
    .table th,.table td{ padding:12px; border-bottom:1px solid #222; text-align:left; }
    .table th{ color:var(--muted); }

    @media (max-width: 1024px) {
        .page-container { margin-left: 0; margin-top: 100px; padding: 15px; }
        .cards { grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px; }
        .card { padding: 15px; }
        .card strong { font-size: 22px; }
    }
    @media (max-width: 768px) {
        .page-container { margin-left: 0; margin-top: 100px; padding: 12px; }
        .cards { grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 10px; }
        .card { padding: 12px; font-size: 14px; }
        .card strong { font-size: 20px; }
    }
    @media (max-width: 480px) {
        .page-container { margin-left: 0; margin-top: 100px; padding: 10px; }
        .cards { grid-template-columns: 1fr; gap: 8px; }
        .card { padding: 10px; font-size: 12px; }
        .card strong { font-size: 18px; }
        .table th, .table td { padding: 5px; font-size: 11px; }
        h2 { font-size: 18px; }
    }
    </style>
</head>

<body>

<?php include "layout/sidebar.php"; ?>
<?php include "layout/header.php"; ?>
<?php include "layout/footer.php"; ?>

<div class="page-container">

<h2><i class="bi bi-speedometer2"></i> Admin Dashboard</h2>

<div class="cards">

    <div class="card" onclick="location.href='product.php';">
        <span>Total Products</span>
        <strong><?= $totalProducts ?></strong>
    </div>

    <div class="card" onclick="location.href='sales.php?filter=today';">
        <span>Sales Today</span>
        <strong>$<?= number_format($salesToday, 2) ?></strong>
    </div>

    <div class="card" onclick="location.href='users.php';">
        <span>Total Users</span>
        <strong><?= $totalUsers ?></strong>
    </div>

    <div class="card" onclick="location.href='stock_movements.php';">
        <span>Low Stock Items</span>
        <strong><?= $lowStock ?></strong>
    </div>

    <div class="card" onclick="location.href='sales.php';">
        <span>New Orders Today</span>
        <strong><?= $newOrders ?></strong>
    </div>

</div>

<!-- Daily Sales Chart -->
<div class="card" style="margin-top:18px;">
    <h3 style="margin-top:0;color:var(--accent)"><i class="bi bi-graph-up"></i> Daily Sales Trend</h3>
    <canvas id="adminSalesChart" style="width:100%; height:300px;"></canvas>
</div>


<!-- Low stock detailed list (admin view) -->
<?php if(!empty($lowStockProducts)): ?>
<div class="card" style="margin-top:18px; text-align:left;">
    <h3 style="margin-top:0;color:var(--danger)"><i class="bi bi-exclamation-triangle"></i> Low Stock Alert</h3>
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

<!-- Chart.js for admin sales -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const adminLabels = <?= json_encode(array_column($sales, 'sale_date')) ?>;
const adminTotals = <?= json_encode(array_map('floatval', array_column($sales, 'total_amount'))) ?>;
const adminCtx = document.getElementById('adminSalesChart').getContext('2d');
new Chart(adminCtx, {
    type: 'line',
    data: {
        labels: adminLabels,
        datasets: [{
            label: 'Total Amount',
            data: adminTotals,
            borderColor: '#00ff9d',
            backgroundColor: 'rgba(0,255,157,0.15)',
            fill: true,
            tension: 0.3
        }]
    },
    options: { responsive: true }
});
</script>

</body>
</html>
