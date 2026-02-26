<?php

$username = $_SESSION['username'] ?? 'Staff';
?>

<header class="topbar">
    <span>Welcome, <?= htmlspecialchars($username) ?></span>
</header>

<div class="main-header">
    <div class="welcome-left">
        <button id="sidebarToggle" class="sidebar-toggle" aria-label="Toggle sidebar">☰</button>
        <div class="welcome-text">
            Welcome, <?= htmlspecialchars($username); ?> (Staff)
        </div>
    </div>

    <div class="header-right">
        <i class="bi bi-bell notification"></i>
        <a href="logout.php" class="logout">Logout</a>
    </div>
</div>

<style>
/* Staff pages: use staff sidebar width variable */
:root { --sidebar-width: 210px; }
@media (max-width: 1024px) {
    :root { --sidebar-width: 0px; }
    body.sidebar-open { --sidebar-width: 210px; }
}

.page-container { margin-left: var(--sidebar-width) !important; transition: margin-left 0.28s ease; }

.main-header {
    position: fixed;
    top: 0;
    left: calc(var(--sidebar-width, 210px));
    right: 0;
    height: 65px;

    background: #1f1f1f;
    color: #fff;

    display: flex;
    justify-content: space-between;
    align-items: center;

    padding: 0 30px;
    box-shadow: 0 2px 10px rgba(0,255,157,0.15);
    z-index: 9999;
}

.header-right {
    display: flex;
    align-items: center;
    gap: 25px;
    white-space: nowrap;
}

.notification {
    font-size: 20px;
    cursor: pointer;
    color: #00ff9d;
}

.logout {
    color: #ff4d4d;
    text-decoration: none;
    font-weight: 600;
    padding: 6px 12px;
    border-radius: 5px;
    background: rgba(255,77,77,0.1);
    transition: 0.3s;
}

.logout:hover {
    background: #ff4d4d;
    color: #fff;
}

/* Small screens: header spans full width when sidebar hidden */
@media (max-width: 1024px) {
    .main-header { left: 0; }
    .sidebar-toggle { display: inline-flex; }
}

/* Sidebar toggle button */
.sidebar-toggle {
    background: transparent;
    border: none;
    color: #00ff9d;
    font-size: 20px;
    margin-right: 12px;
    cursor: pointer;
    z-index: 10001;
}

/* Overlay shown when sidebar is open */
.sidebar-toggle-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.4);
    z-index: 999;
    display: none;
}
body.sidebar-open .sidebar-toggle-overlay { display: block; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function(){
    var btn = document.getElementById('sidebarToggle');
    if(btn){
        btn.addEventListener('click', function(e){
            e.preventDefault();
            document.body.classList.toggle('sidebar-open');
            adjustPageContainers();
        });
    }

    var overlays = document.getElementsByClassName('sidebar-toggle-overlay');
    Array.prototype.forEach.call(overlays, function(o){
        o.addEventListener('click', function(){ document.body.classList.remove('sidebar-open'); adjustPageContainers(); });
    });

    function adjustPageContainers(){
        var side = document.querySelector('.sidebar');
        var pcs = document.querySelectorAll('.page-container');
        var w = window.innerWidth;
        var sidebarVisible = side && window.getComputedStyle(side).visibility !== 'hidden' && side.getBoundingClientRect().width>0 && !document.body.classList.contains('sidebar-open') ? true : (document.body.classList.contains('sidebar-open'));
        var sidebarWidth = sidebarVisible ? Math.round(side.getBoundingClientRect().width) : 0;
        pcs.forEach(function(p){
            if(w > 1024){
                p.style.marginLeft = sidebarWidth + 'px';
            } else {
                p.style.marginLeft = '0';
            }
        });
    }

    adjustPageContainers();
    window.addEventListener('resize', adjustPageContainers);
});
</script>
