<?php                                                                                                                                          
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit;
}
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
    <div class="card">
        Total Products
        <h3>0</h3>
    </div>
    <div class="card">
        Sales Today
        <h3>0</h3>
    </div>
    <div class="card">
        Total Users
        <h3>0</h3>
    </div>
    <div class="card">
        Low Stock
        <h3>0</h3>
    </div>
    <div class="card">
        New Orders
        <h3>0</h3>
    </div>
    
</div>

</body>
</html>
