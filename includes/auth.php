<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Not logged in, redirect to login
    header("Location: ../auth/login.php");
    exit;
}
?>
