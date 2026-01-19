<div class="sidebar">
    <div class="logo-box">📦</div>
    <h2 class="system-name">Inventory System</h2>

    <a href="admin_dashboard.php"><i class="bi bi-speedometer2"></i>Dashboard</a>
        <a href="categories.php"><i class="bi bi-tags"></i>Categories</a>
    <a href="product.php"><i class="bi bi-box"></i> Products</a>
    <a href="sales.php"><i class="bi bi-cart-check"></i> Sales</a>
     <a href="stock_movements.php"><i class="bi bi-arrow-up"></i>stock_movements</a>
    <a href="users.php"><i class="bi bi-people"></i> Users</a>
    <a href="report.php"><i class="bi bi-graph-up"></i> Reports</a>
</div>

<style>
.sidebar {
    position: fixed;
    top: 0;
    left: 0;
    width: 240px;
    height: 100vh;
    background: #1b1b1b;
    padding-top: 20px;
    color: #fff;
    box-shadow: 2px 0 8px rgba(0,255,157,0.1);
    z-index: 1000;
}

.logo-box {
    width: 65px;
    height: 65px;
    margin: 0 auto;
    border-radius: 50%;
    background: rgba(0,255,157,0.15);
    display: flex;
    justify-content: center;
    align-items: center;
    font-size: 28px;
    color: #00ff9d;
    margin-bottom: 10px;
}

.system-name {
    text-align: center;
    color: #00ff9d;
    font-weight: 600;
    margin-bottom: 30px;
}

.sidebar a {
    display: block;
    padding: 12px 25px;
    font-size: 16px;
    color: #ccc;
    text-decoration: none;
    transition: 0.3s;
}

.sidebar a:hover {
    background: #00ff9d;
    color: #111;
}
</style>
