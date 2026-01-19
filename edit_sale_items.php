<?php
require_once 'includes/db.php';
if(session_status()===PHP_SESSION_NONE) session_start();

if(!isset($_GET['item_id']) || !is_numeric($_GET['item_id'])) die("Invalid sale item selected.");
$item_id = (int)$_GET['item_id'];

// Fetch sale item and sale info
$stmt = $pdo->prepare("
    SELECT si.*, s.invoice_no, p.name AS product_name, p.selling_price
    FROM sale_items si
    JOIN sales s ON si.sale_id=s.id
    JOIN products p ON si.product_id=p.id
    WHERE si.id=?
");
$stmt->execute([$item_id]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);
if(!$item) die("Sale item not found.");

$errors = [];
$success = '';

if($_SERVER['REQUEST_METHOD']==='POST'){
    $quantity = (int)($_POST['quantity'] ?? 1);
    $unit_price = (float)($_POST['unit_price'] ?? $item['selling_price']);

    if($quantity<=0) $errors[] = "Quantity must be at least 1.";
    if($unit_price<=0) $errors[] = "Unit price must be greater than 0.";

    if(empty($errors)){
        $line_total = $quantity * $unit_price;
        $stmt = $pdo->prepare("UPDATE sale_items SET quantity=:quantity, unit_price=:unit_price, line_total=:line_total WHERE id=:id");
        $stmt->execute([
            ':quantity'=>$quantity,
            ':unit_price'=>$unit_price,
            ':line_total'=>$line_total,
            ':id'=>$item_id
        ]);
        $success = "Sale item updated successfully.";
        $item['quantity'] = $quantity;
        $item['unit_price'] = $unit_price;
        $item['line_total'] = $line_total;
    }
}
?>

<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Edit Sale Item</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<style>
:root{
    --bg:#121212; --panel:#1a1a1a; --muted:#bdbdbd; --accent:#00ff9d; --danger:#ff4d4d; --card-shadow: rgba(0,0,0,0.6);
}
body{margin:0;font-family:Poppins,sans-serif;background:var(--bg);color:#fff;}
.page-container{margin-left:210px;margin-top:90px;padding:30px;}
.card{background:var(--panel);border-radius:12px;padding:18px;box-shadow:0 8px 30px var(--card-shadow);}
h2{color:var(--accent);margin-bottom:18px;}
label{color:var(--muted);margin-top:12px;display:block;}
input{width:100%;padding:10px;border-radius:8px;border:1px solid #222;background:#0f0f0f;color:#fff;margin-top:6px;box-sizing:border-box;}
.btn{display:inline-flex;align-items:center;gap:6px;background:var(--accent);color:#000;padding:8px 14px;border-radius:8px;border:none;cursor:pointer;font-weight:600;margin-top:12px;text-decoration:none;}
.btn.secondary{background:#2a2a2a;color:#bdbdbd;}
.messages .error{background:rgba(255,77,77,0.12);color:var(--danger);padding:10px;border-radius:8px;margin-bottom:6px;}
.messages .success{background:rgba(0,255,157,0.12);color:var(--accent);padding:10px;border-radius:8px;margin-bottom:6px;}
@media(max-width:720px){.page-container{margin-left:0;margin-top:20px;padding:20px}}
</style>
</head>
<body>
<?php include 'layout/sidebar.php'; ?>
<?php include 'layout/header.php'; ?>

<div class="page-container">
<div class="card">
<h2><i class="bi bi-pencil-square"></i> Edit Sale Item for Invoice <?= htmlspecialchars($item['invoice_no']) ?></h2>

<div class="messages">
<?php foreach($errors as $e): ?><div class="error"><?= htmlspecialchars($e) ?></div><?php endforeach; ?>
<?php if($success): ?><div class="success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
</div>

<form method="POST">
<label>Product</label>
<input type="text" value="<?= htmlspecialchars($item['product_name']) ?>" disabled>

<label>Quantity</label>
<input type="number" name="quantity" min="1" value="<?= htmlspecialchars($item['quantity']) ?>" required>

<label>Unit Price</label>
<input type="number" step="0.01" name="unit_price" min="0" value="<?= htmlspecialchars($item['unit_price']) ?>" required>

<button type="submit" class="btn"><i class="bi bi-save"></i> Update Item</button>
<a href="sale_items.php?sale_id=<?= $item['sale_id'] ?>" class="btn secondary"><i class="bi bi-arrow-left"></i> Back</a>
</form>
</div>
</div>
</body>
</html>
