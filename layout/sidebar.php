<div class="sidebar">
	<div class="logo-box">📦</div>
	<h2 class="system-name">Inventory System</h2>

	<a href="admin_dashboard.php"><i class="bi bi-speedometer2"></i>Dashboard</a>
	<a href="categories.php"><i class="bi bi-tags"></i>Categories</a>
	<a href="product.php"><i class="bi bi-box"></i> Products</a>
	<a href="sales.php"><i class="bi bi-cart-check"></i> Sales</a>
	<a href="stock_movements.php"><i class="bi bi-arrow-up"></i> Stock Movements</a>
	<a href="users.php"><i class="bi bi-people"></i> Users</a>
	<a href="report.php"><i class="bi bi-graph-up"></i> Reports</a>
</div>

<style>
.root-sidebar {
    --sidebar-width: 210px;
}
.sidebar{
    position:fixed;
    top:0;
    left:0;
    width:var(--sidebar-width);
    height:100vh;
    background:#0f0f0f;
    padding-top:90px;
    box-shadow:4px 0 20px rgba(0,0,0,0.6);
    z-index:1000;
    transition: transform 0.28s ease, visibility 0.28s ease;
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
/* responsive collapsing */
@media(max-width:720px){
    .sidebar{
        transform: translateX(-100%);
        visibility: hidden;
    }
}

@media (max-width: 1024px) {
    body.sidebar-open .sidebar { transform: translateX(0); visibility: visible; }
}
</style>

<div class="sidebar-toggle-overlay" onclick="document.body.classList.remove('sidebar-open')"></div>
