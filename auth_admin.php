<?php
session_start();

// Check if user is logged in and has admin role (role_id = 1)
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    // Not authorized, redirect to login
    header("Location: auth/login.php");
    exit;
}
?>