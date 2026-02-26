<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$username = $_SESSION['username'] ?? 'Admin';
?>
<header class="topbar">
    <span>Welcome, <?= htmlspecialchars($username) ?></span>
</header>

<div class="main-header">
    <div class="welcome-left">
        <button id="sidebarToggle" class="sidebar-toggle" aria-label="Toggle sidebar">☰</button>
        <div class="welcome-text">Welcome, <?= $_SESSION['username']; ?></div>
    </div>

    <div class="header-right">
        <i class="bi bi-bell notification"></i>
        <a href="auth/logout.php" class="logout">Logout</a>
    </div>
</div>

<style>
/* Make page containers follow the sidebar width via a CSS variable.
   Default: sidebar visible on large screens (240px). On smaller screens
   the variable becomes 0 and body.sidebar-open restores it. Using a
   variable avoids inline JS margin adjustments and prevents content
   from being hidden after resize. */
:root { --sidebar-width: 210px; }
@media (max-width: 1024px) {
    :root { --sidebar-width: 0px; }
    body.sidebar-open { --sidebar-width: 210px; }
}

.page-container { margin-left: var(--sidebar-width) !important; transition: margin-left 0.28s ease; }

.main-header {
    position: fixed;
    top: 0;
    left: calc(var(--sidebar-width, 240px)); /* Sidebar width via CSS variable */
    right: 0;    /* Ensure header stretches fully to right */
    height: 65px;

    background: #1f1f1f;
    color: #fff;

    display: flex;
    justify-content: space-between;
    align-items: center;

    padding: 0 30px;  /* Horizontal padding inside flex */
    box-shadow: 0 2px 10px rgba(0,255,157,0.15);
    z-index: 9999; /* Always on top */
}

.header-right {
    display: flex;
    align-items: center;
    gap: 25px;
    white-space: nowrap; /* Prevent wrapping */
    overflow: visible;   /* Ensure items are fully visible */
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

    // close sidebar when overlay clicked (overlay element added in sidebar files)
    var overlays = document.getElementsByClassName('sidebar-toggle-overlay');
    Array.prototype.forEach.call(overlays, function(o){
        o.addEventListener('click', function(){ document.body.classList.remove('sidebar-open'); adjustPageContainers(); });
    });

    function adjustPageContainers(){
        var side = document.querySelector('.sidebar');
        var pcs = document.querySelectorAll('.page-container');
        var w = window.innerWidth;
        var sidebarVisible = side && window.getComputedStyle(side).visibility !== 'hidden' && side.getBoundingClientRect().width>0;
        var sidebarWidth = sidebarVisible ? Math.round(side.getBoundingClientRect().width) : 0;
        pcs.forEach(function(p){
            if(w > 1024){
                p.style.marginLeft = sidebarWidth + 'px';
            } else {
                p.style.marginLeft = '0';
            }
        });
    }

    // adjust on load and on resize
    adjustPageContainers();
    window.addEventListener('resize', adjustPageContainers);
});
</script>
