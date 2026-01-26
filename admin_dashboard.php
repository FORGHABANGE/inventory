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
        body {
            background: #121212;
            font-family: "Poppins", sans-serif;
            margin: 0;
            color: #fff;
        }

        /* DASHBOARD CARDS */
        .dashboard-container {
            margin-left: 240px; /* Sidebar width */
            margin-top: 90px;   /* Fixed header height */
            padding: 25px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 25px;
        }

        .card {
            background: #1a1a1a;
            color: #00ff9d;
            padding: 30px;
            border-radius: 12px;
            font-size: 20px;
            font-weight: 600;
            box-shadow: 0 0 10px rgba(0,255,157,0.2);
            transition: 0.3s ease;
            text-align: center;
            cursor: pointer;
        }

        .card h3 {
            margin: 10px 0 0 0;
            font-size: 28px;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0 20px rgba(0,255,157,0.4);
        }
    </style>
</head>

<body>

<?php include "layout/sidebar.php"; ?>
<?php include "layout/header.php"; ?>
<?php include "layout/footer.php"; ?>

<div class="dashboard-container">
    <div class="card" onclick="location.href='product.php';">
        Total Products
        <h3><?= $totalProducts ?></h3>
    </div>
    <div class="card" onclick="location.href='sales.php?filter=today';">
        Sales Today
        <h3>$<?= number_format($salesToday, 2) ?></h3>
    </div>
    <div class="card" onclick="location.href='users.php';">
        Total Users
        <h3><?= $totalUsers ?></h3>
    </div>
    <div class="card" onclick="location.href='stock_movements.php';">
        Low Stock Items
        <h3><?= $lowStock ?></h3>
    </div>
    <div class="card" onclick="location.href='sales.php';">
        New Orders Today
        <h3><?= $newOrders ?></h3>
    </div>
    
</div>

</body>
</html>
