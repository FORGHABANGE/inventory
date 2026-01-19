<?php
require_once 'includes/db.php';
if(session_status()===PHP_SESSION_NONE) session_start();

if(!isset($_GET['item_id'], $_GET['sale_id']) || !is_numeric($_GET['item_id']) || !is_numeric($_GET['sale_id'])) die("Invalid request.");
$item_id = (int)$_GET['item_id'];
$sale_id = (int)$_GET['sale_id'];

// Delete item
$del = $pdo->prepare("DELETE FROM sale_items WHERE id=?");
$del->execute([$item_id]);

// Update total in sales table
$total = $pdo->prepare("SELECT SUM(line_total) FROM sale_items WHERE sale_id=?");
$total->execute([$sale_id]);
$total_amount = (float)$total->fetchColumn();
$updateSale = $pdo->prepare("UPDATE sales SET total_amount=:total_amount WHERE id=:id");
$updateSale->execute([':total_amount'=>$total_amount, ':id'=>$sale_id]);

header("Location: sale_items.php?sale_id=$sale_id");
exit;
?>
