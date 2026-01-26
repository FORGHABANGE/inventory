<?php
include 'auth_admin.php';

require_once 'includes/db.php';
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$admin_id = 1;

if(!isset($_GET['id'])) die('Category ID required');
$id = (int)$_GET['id'];
if($id<=0) die('Invalid ID');

// Check if products exist
$stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM products WHERE category_id=:id");
$stmtCheck->execute([':id'=>$id]);
if($stmtCheck->fetchColumn() > 0){
    die('Cannot delete. Products exist in this category.');
}

// Delete
$stmtDel = $pdo->prepare("DELETE FROM categories WHERE id=:id");
$stmtDel->execute([':id'=>$id]);
header("Location: categories.php");
exit;
?>
