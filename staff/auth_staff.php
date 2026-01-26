<?php
session_start();

// Check if user is logged in and has staff role (role_id = 2)
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2) {
    // Not authorized, redirect to login
    header("Location: ../auth/login.php");
    exit;
}
?>