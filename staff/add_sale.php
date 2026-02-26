<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'auth_staff.php';

$staff_id = $_SESSION['user_id'];

/* ===============================
   DATABASE
================================ */
require_once '../includes/db.php';

$errors = [];

/* ===============================
   GENERATE INVOICE NUMBER
================================ */
function generateInvoiceNo($pdo) {
    $stmt = $pdo->query("
        SELECT invoice_no 
        FROM sales 
        WHERE invoice_no LIKE 'INV-%'
        ORDER BY CAST(SUBSTRING(invoice_no, 5) AS UNSIGNED) DESC
        LIMIT 1
    ");

    $lastInvoice = $stmt->fetchColumn();

    $next = $lastInvoice
        ? ((int) str_replace('INV-', '', $lastInvoice)) + 1
        : 1;

    return 'INV-' . str_pad($next, 3, '0', STR_PAD_LEFT);
}

$invoice_no = generateInvoiceNo($pdo);

/* ===============================
   FORM SUBMISSION
================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $customer_name = trim($_POST['customer_name'] ?? '');

    if ($customer_name === '') {
        $errors[] = "Customer name is required.";
    }

    if (empty($errors)) {
        // Keep sale as a pending session object so add_sale_items.php can finalize it
        $_SESSION['pending_sale'] = [
            'invoice_no' => $invoice_no,
            'customer_name' => $customer_name
        ];

        // Redirect to the shared add_sale_items flow
        header("Location: ../add_sale_items.php");
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
    --bg:#121212;
    --panel:#1a1a1a;
    --muted:#bdbdbd;
    --accent:#00ff9d;
    --danger:#ff4d4d;
    --card-shadow: rgba(0,0,0,0.6);
}
body{
    margin:0;
    font-family:Poppins,sans-serif;
    background:var(--bg);
    color:#fff;
}
.page-container{
    margin-left:210px;
    margin-top:90px;
    padding:30px;
}
.card{
    background:var(--panel);
    border-radius:12px;
    padding:18px;
    box-shadow:0 8px 30px var(--card-shadow);
}
h2{
    color:var(--accent);
    margin-bottom:18px;
}
label{
    color:var(--muted);
    margin-top:12px;
    display:block;
}
input{
    width:100%;
    padding:10px;
    border-radius:8px;
    border:1px solid #222;
    background:#0f0f0f;
    color:#fff;
    margin-top:6px;
}
.btn{
    display:inline-flex;
    align-items:center;
    gap:6px;
    background:var(--accent);
    color:#000;
    padding:8px 14px;
    border-radius:8px;
    border:none;
    cursor:pointer;
    font-weight:600;
    text-decoration:none;
}
.btn.secondary{
    background:#2a2a2a;
    color:#bdbdbd;
}
.messages .error{
    background:rgba(255,77,77,0.12);
    color:var(--danger);
    padding:10px;
    border-radius:8px;
    margin-bottom:6px;
}

/* Mobile Responsive */
@media (max-width: 1024px) {
    .page-container { margin-left: 0; margin-top: 100px; padding: 15px; }
    input { padding: 8px; font-size: 14px; }
}

@media (max-width: 768px) {
    .page-container { margin-left: 0; padding: 12px; }
    input { padding: 8px; font-size: 13px; }
    .btn { width: 100%; }
}

@media (max-width: 480px) {
    .page-container { padding: 10px; }
    .card { padding: 10px; }
    h2 { font-size: 18px; }
    label { font-size: 13px; margin-top: 8px; }
    input { padding: 6px; font-size: 12px; }
    .btn { width: 100%; padding: 10px; font-size: 12px; }
}
</style>
</head>

<body>

<?php include 'layout/sidebar_staff.php'; ?>
<?php include 'layout/header.php'; ?>

<div class="page-container">
<div class="card">

<h2><i class="bi bi-plus-circle"></i> New Sale</h2>

<div class="messages">
<?php foreach($errors as $e): ?>
    <div class="error"><?= htmlspecialchars($e) ?></div>
<?php endforeach; ?>
</div>

<form method="POST">

<label>Invoice Number (Auto-generated)</label>
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

<?php include '../layout/footer.php'; ?>
</body>
</html>
