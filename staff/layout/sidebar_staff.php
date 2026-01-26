<?php
?>
<style>
.sidebar{
    position:fixed;
    top:0;
    left:0;
    width:210px;
    height:100vh;
    background:#0f0f0f;
    padding-top:90px;
    box-shadow:4px 0 20px rgba(0,0,0,0.6);
    z-index:1000;
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
    margin-top: -80px;
}
.sidebar a{
    display:flex;
    align-items:center;
    gap:10px;
    padding:12px 18px;
    color:#bdbdbd;
    text-decoration:none;
    font-weight:500;
    transition:all .2s ease;
}
.sidebar a i{
    font-size:18px;
}
.sidebar a:hover,
.sidebar a.active{
    background:#1a1a1a;
    color:#00ff9d;
}
.system-name{
    text-align:center;
    color:#00ff9d;
    font-weight:600;
    margin-bottom:30px;
    margin-left:20px;
    font-size:20px;
}
.sidebar .title{
    padding:0 18px 12px;
    color:#00ff9d;
    font-size:13px;
    text-transform:uppercase;
    letter-spacing:1px;
}
@media(max-width:720px){
    .sidebar{
        position:relative;
        width:100%;
        height:auto;
        padding-top:20px;
    }
}
</style>

<div class="sidebar">

    <div class="logo-box">📦</div>
    <h2 class="system-name">Inventory System</h2>

    <a href="user_dashboard.php" class="active">
        <i class="bi bi-speedometer2"></i>
        Dashboard
    </a>

    <a href="sales.php">
        <i class="bi bi-receipt"></i>
        Sales
    </a>

    <a href="products.php">
        <i class="bi bi-box-seam"></i>
        Products
    </a>

    <a href="stock_levels.php">
        <i class="bi bi-bar-chart-steps"></i>
        Stock Levels
    </a>

    <a href="stock_movements.php">
        <i class="bi bi-arrow-up"></i>
        Stock Movements
    </a>

    <a href="report.php">
        <i class="bi bi-graph-up"></i>
        Reports
    </a>

</div>
