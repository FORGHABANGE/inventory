<?php
require_once 'includes/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_GET['sale_id']) || !is_numeric($_GET['sale_id'])) die("Invalid sale selected.");
$sale_id = (int)$_GET['sale_id'];

// Delete sale items first (foreign key dependency)
$pdo->prepare("DELETE FROM sale_items WHERE sale_id=?")->execute([$sale_id]);

// Delete sale
$pdo->prepare("DELETE FROM sales WHERE id=?")->execute([$sale_id]);

header("Location: sales.php");
exit;
?>