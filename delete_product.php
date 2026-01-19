<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit;
}

include "includes/db.php";

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid Product ID");
}

$id = $_GET['id'];

$stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
$stmt->execute([$id]);

header("Location: product.php?deleted=1");
exit;
