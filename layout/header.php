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
    <div class="welcome-text">Welcome, <?= $_SESSION['username']; ?></div>

    <div class="header-right">
        <i class="bi bi-bell notification"></i>
        <a href="logout.php" class="logout">Logout</a>
    </div>
</div>

<style>
.main-header {
    position: fixed;
    top: 0;
    left: 240px; /* Sidebar width */
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
</style>
