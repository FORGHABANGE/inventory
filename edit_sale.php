<?php
require_once 'includes/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_GET['sale_id']) || !is_numeric($_GET['sale_id'])) die("Invalid sale selected.");
$sale_id = (int)$_GET['sale_id'];

$stmt = $pdo->prepare("SELECT * FROM sales WHERE id=?");
$stmt->execute([$sale_id]);
$sale = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$sale) die("Sale not found.");

$errors = [];
$success = '';

// Function to generate next invoice if needed
function generateInvoiceNo($pdo, $current = null) {
    if ($current) return $current; // Keep existing invoice

    $stmt = $pdo->query("SELECT invoice_no FROM sales ORDER BY id DESC LIMIT 1");
    $last = $stmt->fetchColumn();

    if ($last && preg_match('/INV-(\d+)/', $last, $matches)) {
        $seq = (int)$matches[1] + 1;
    } else {
        $seq = 1;
    }
    return 'INV-' . str_pad($seq, 3, '0', STR_PAD_LEFT);
}

$invoice_no = generateInvoiceNo($pdo, $sale['invoice_no']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_name = trim($_POST['customer_name'] ?? '');
    $total_amount = isset($_POST['total_amount']) ? (float)$_POST['total_amount'] : 0;
    $paid_amount = isset($_POST['paid_amount']) ? (float)$_POST['paid_amount'] : 0;

    if ($customer_name === '') $errors[] = "Customer name is required.";

    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE sales SET customer_name=:customer_name, total_amount=:total_amount, paid_amount=:paid_amount WHERE id=:id");
        $stmt->execute([
            ':customer_name'=>$customer_name,
            ':total_amount'=>$total_amount,
            ':paid_amount'=>$paid_amount,
            ':id'=>$sale_id
        ]);
        $success = "Sale updated successfully.";

        // Refresh sale data
        $stmt = $pdo->prepare("SELECT * FROM sales WHERE id=?");
        $stmt->execute([$sale_id]);
        $sale = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>

<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Edit Sale</title>
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
input, textarea{width:100%;padding:10px;border-radius:8px;border:1px solid #222;background:#0f0f0f;color:#fff;margin-top:6px;box-sizing:border-box;}
label{color:var(--muted);margin-top:12px;display:block;}
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
<h2><i class="bi bi-pencil-square"></i> Edit Sale</h2>

<div class="messages">
<?php foreach($errors as $e): ?><div class="error"><?= htmlspecialchars($e) ?></div><?php endforeach; ?>
<?php if($success): ?><div class="success"><?= $success ?></div><?php endif; ?>
</div>

<form method="POST">
<label>Invoice Number (Auto-generated)</label>
<input type="text" name="invoice_no" value="<?= htmlspecialchars($invoice_no) ?>" readonly>

<label>Customer Name</label>
<input type="text" name="customer_name" value="<?= htmlspecialchars($sale['customer_name']) ?>" required>

<label>Total Amount</label>
<input type="number" step="0.01" min="0" name="total_amount" value="<?= htmlspecialchars($sale['total_amount']) ?>" required>

<label>Paid Amount</label>
<input type="number" step="0.01" min="0" name="paid_amount" value="<?= htmlspecialchars($sale['paid_amount']) ?>" required>

<div style="display:flex; gap:12px; margin-top:14px;">
<button type="submit" class="btn"><i class="bi bi-save"></i> Update Sale</button>
<a href="sales.php" class="btn secondary"><i class="bi bi-arrow-left"></i> Back</a>
</div>
</form>
</div>
</div>

<?php include 'layout/footer.php'; ?>
</body>
</html>
