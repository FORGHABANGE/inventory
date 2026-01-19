<?php
session_start();

/* ===============================
   AUTH & ROLE CHECK
================================ */
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2) {
    header("Location: ../login.php");
    exit;
}

require_once '../includes/db.php';

$staff_id = $_SESSION['user_id'];
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_name = trim($_POST['customer_name']);
    if (empty($customer_name)) {
        $error = "Customer name is required.";
    } else {
        // Generate unique invoice
        $invoice_no = 'INV-' . date('YmdHis') . '-' . rand(100,999);

        // Insert new sale
        $stmt = $pdo->prepare("INSERT INTO sales (invoice_no, user_id, customer_name) VALUES (?, ?, ?)");
        $stmt->execute([$invoice_no, $staff_id, $customer_name]);
        $sale_id = $pdo->lastInsertId();

        // Redirect to add_sale_items.php to add products
        header("Location: add_sale_items.php?sale_id=$sale_id");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add Sale</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

<style>
body{margin:0;font-family:Poppins,sans-serif;background:#121212;color:#fff;}
.page-container{margin-left:210px;margin-top:90px;padding:30px;}
.card{background:#1a1a1a;padding:20px;border-radius:12px;box-shadow:0 8px 30px rgba(0,0,0,0.6);}
input,button{padding:10px 14px;border-radius:8px;border:none;font-size:16px;}
input{width:100%;margin-bottom:12px;}
button{background:#00ff9d;color:#000;font-weight:600;cursor:pointer;}
.error{color:#ff4d4d;margin-bottom:10px;}
</style>
</head>
<body>

<?php include 'layout/sidebar_staff.php'; ?>
<?php include '../layout/header.php'; ?>

<div class="page-container">
<div class="card">
<h2><i class="bi bi-plus-circle"></i> New Sale</h2>

<?php if($error): ?><p class="error"><?= $error ?></p><?php endif; ?>

<form method="POST">
    <label>Customer Name</label>
    <input type="text" name="customer_name" placeholder="Enter customer name" required>

    <button type="submit"><i class="bi bi-arrow-right-circle"></i> Proceed to Add Products</button>
</form>

</div>
</div>

<?php include '../layout/footer.php'; ?>
</body>
</html>
