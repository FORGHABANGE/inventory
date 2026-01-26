<?php
include 'auth_admin.php';

require_once 'includes/db.php';

$errors = [];

/* Generate invoice number (preview only) */
function generateInvoiceNo($pdo) {
    $stmt = $pdo->query("SELECT invoice_no FROM sales ORDER BY id DESC LIMIT 1");
    $last = $stmt->fetchColumn();

    if ($last && preg_match('/INV-(\d+)/', $last, $matches)) {
        $seq = (int)$matches[1] + 1;
    } else {
        $seq = 1;
    }
    return 'INV-' . str_pad($seq, 3, '0', STR_PAD_LEFT);
}

$invoice_no = generateInvoiceNo($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_name = trim($_POST['customer_name'] ?? '');

    if ($customer_name === '') {
        $errors[] = "Customer name is required.";
    }

    if (empty($errors)) {
        /* Store temporarily — DO NOT INSERT YET */
        $_SESSION['pending_sale'] = [
            'invoice_no'   => $invoice_no,
            'customer_name'=> $customer_name
        ];

        /* Move to items page */
        header("Location: add_sale_items.php");
        exit;
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Add Sale</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<style>
:root{
    --bg:#121212; --panel:#1a1a1a; --muted:#bdbdbd;
    --accent:#00ff9d; --danger:#ff4d4d; --card-shadow: rgba(0,0,0,0.6);
}
body{margin:0;font-family:Poppins,sans-serif;background:var(--bg);color:#fff;}
.page-container{margin-left:210px;margin-top:90px;padding:30px;}
.card{background:var(--panel);border-radius:12px;padding:18px;box-shadow:0 8px 30px var(--card-shadow);}
h2{color:var(--accent);margin-bottom:18px;}
input{width:100%;padding:10px;border-radius:8px;border:1px solid #222;background:#0f0f0f;color:#fff;margin-top:6px;}
label{color:var(--muted);margin-top:12px;display:block;}
.btn{display:inline-flex;align-items:center;gap:6px;background:var(--accent);color:#000;padding:8px 14px;border-radius:8px;border:none;cursor:pointer;font-weight:600;margin-top:12px;text-decoration:none;}
.btn.secondary{background:#2a2a2a;color:#bdbdbd;}
.messages .error{background:rgba(255,77,77,0.12);color:var(--danger);padding:10px;border-radius:8px;margin-bottom:6px;}
@media(max-width:720px){.page-container{margin-left:0;margin-top:20px;padding:20px}}
</style>
</head>

<body>
<?php include 'layout/sidebar.php'; ?>
<?php include 'layout/header.php'; ?>

<div class="page-container">
<div class="card">

<h2><i class="bi bi-plus-circle"></i> Add Sale</h2>

<div class="messages">
<?php foreach($errors as $e): ?>
    <div class="error"><?= htmlspecialchars($e) ?></div>
<?php endforeach; ?>
</div>

<form method="POST">

<label>Invoice Number (Preview)</label>
<input type="text" value="<?= htmlspecialchars($invoice_no) ?>" readonly>

<label>Customer Name</label>
<input type="text" name="customer_name" required>

<div style="display:flex; gap:12px; margin-top:14px;">
<button type="submit" class="btn">
    <i class="bi bi-arrow-right-circle"></i> Continue to Items
</button>
<a href="sales.php" class="btn secondary">
    <i class="bi bi-arrow-left"></i> Back
</a>
</div>

</form>
</div>
</div>

<?php include 'layout/footer.php'; ?>
</body>
</html>
