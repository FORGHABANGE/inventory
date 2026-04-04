<?php
require_once 'includes/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

/* ------------------ DETERMINE SALE CONTEXT ------------------ */
$sale_id = null;
$sale = null;

if (isset($_GET['sale_id']) && is_numeric($_GET['sale_id'])) {
    $sale_id = (int)$_GET['sale_id'];
    $saleStmt = $pdo->prepare("SELECT * FROM sales WHERE id = ?");
    $saleStmt->execute([$sale_id]);
    $sale = $saleStmt->fetch(PDO::FETCH_ASSOC);
    if (!$sale) {
        die("Sale not found.");
    }
} elseif (isset($_SESSION['pending_sale'])) {
    $pendingSale = $_SESSION['pending_sale'];
} else {
    die("No pending sale found. Please start a new sale.");
}

$saleInvoice = $sale['invoice_no'] ?? $pendingSale['invoice_no'];
$user_id = $_SESSION['user_id'] ?? 1;

/* Fetch products */
$products = $pdo->query("
    SELECT id, name, selling_price, quantity, reorder_level
    FROM products
    ORDER BY name ASC
")->fetchAll(PDO::FETCH_ASSOC);

$productOptionsHtml = '';
foreach ($products as $p) {
    $status = 'In Stock';
    $disabled = '';
    if ((int)$p['quantity'] <= (int)$p['reorder_level']) {
        if ((int)$p['quantity'] === 0) {
            $status = 'Out of Stock';
            $disabled = 'disabled';
        } else {
            $status = 'Low Stock';
            $disabled = 'disabled';
        }
    }
    $productOptionsHtml .= sprintf(
        '<option value="%d" data-price="%s" %s>%s (Qty: %d, Status: %s)</option>',
        $p['id'],
        htmlspecialchars($p['selling_price'], ENT_QUOTES),
        $disabled,
        htmlspecialchars($p['name']),
        $p['quantity'],
        $status
    );
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['items'])) {

    try {
        $pdo->beginTransaction();

        /* ------------------ CREATE SALE WHEN NEEDED ------------------ */
        if (!$sale_id) {
            $insertSale = $pdo->prepare("
                INSERT INTO sales (invoice_no, customer_name, total_amount, paid_amount, user_id)
                VALUES (?, ?, 0, 0, ?)
            ");
            $insertSale->execute([
                $pendingSale['invoice_no'],
                $pendingSale['customer_name'],
                $user_id
            ]);

            $sale_id = $pdo->lastInsertId();
            $sale = [
                'id' => $sale_id,
                'invoice_no' => $pendingSale['invoice_no'],
                'customer_name' => $pendingSale['customer_name'],
                'paid_amount' => 0,
            ];
        }

        /* ------------------ PROCESS SALE ITEMS ------------------ */
        foreach ($_POST['items'] as $item) {

            $product_id  = (int)($item['product_id'] ?? 0);
            $quantityRaw = trim($item['quantity'] ?? '');
            $unit_price  = (float)($item['unit_price'] ?? 0);

            if ($product_id <= 0 || $quantityRaw === '' || $unit_price <= 0) {
                throw new Exception("Invalid product entry.");
            }

            if (!preg_match('/^\d+/', $quantityRaw, $matches)) {
                throw new Exception("Quantity must start with a number (e.g. 16sachets).");
            }
            $quantity = (int)$matches[0];

            /* Lock product and validate availability */
            $stockStmt = $pdo->prepare("
                SELECT name, quantity, reorder_level
                FROM products
                WHERE id = ?
                FOR UPDATE
            ");
            $stockStmt->execute([$product_id]);
            $product = $stockStmt->fetch(PDO::FETCH_ASSOC);

            if (!$product) {
                throw new Exception("Product not found.");
            }

            if ((int)$product['quantity'] <= (int)$product['reorder_level']) {
                $statusWord = ((int)$product['quantity'] === 0) ? 'out of stock' : 'low stock';
                throw new Exception("Product '{$product['name']}' is currently {$statusWord} and cannot be sold.");
            }

            if ((int)$product['quantity'] < $quantity) {
                throw new Exception("Insufficient stock for '{$product['name']}'.");
            }

            $line_total = $quantity * $unit_price;

            /* Insert sale item */
            $pdo->prepare("
                INSERT INTO sale_items
                (sale_id, product_id, quantity, unit_price, line_total)
                VALUES (?,?,?,?,?)
            ")->execute([
                $sale_id,
                $product_id,
                $quantity,
                $unit_price,
                $line_total
            ]);

            /* Log stock movement */
            $pdo->prepare("
                INSERT INTO stock_movements
                (product_id, type, quantity, reference, note, user_id)
                VALUES (?,?,?,?,?,?)
            ")->execute([
                $product_id,
                'OUT',
                $quantity,
                'SALE-' . $saleInvoice,
                'Sale deduction',
                $user_id
            ]);

            /* Deduct stock */
            $pdo->prepare("
                UPDATE products
                SET quantity = quantity - ?
                WHERE id = ?
            ")->execute([$quantity, $product_id]);
        }

        /* ------------------ UPDATE SALE TOTAL ------------------ */
        $totalStmt = $pdo->prepare("
            SELECT SUM(line_total)
            FROM sale_items
            WHERE sale_id = ?
        ");
        $totalStmt->execute([$sale_id]);
        $total = (float)($totalStmt->fetchColumn() ?? 0);

        $pdo->prepare("
            UPDATE sales
            SET total_amount = ?, paid_amount = ?
            WHERE id = ?
        ")->execute([$total, $total, $sale_id]);

        /* ------------------ CLEANUP ------------------ */
        if (isset($_SESSION['pending_sale'])) {
            unset($_SESSION['pending_sale']);
        }

        $pdo->commit();

        // Redirect based on user role: staff users go to staff sale view
        $redirectUrl = 'sale_items.php?sale_id=' . $sale_id;
        if (isset($_SESSION['role_id']) && $_SESSION['role_id'] == 2) {
            $redirectUrl = 'staff/sale_items.php?sale_id=' . $sale_id;
        }

        header("Location: " . $redirectUrl);
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        $errors[] = $e->getMessage();
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Add Sale Items</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<style>
:root{
  --bg:#121212; --panel:#1a1a1a; --muted:#bdbdbd;
  --accent:#00ff9d; --danger:#ff4d4d;
}
body{margin:0;font-family:Poppins,sans-serif;background:var(--bg);color:#fff;}
.page-container{margin-left:210px;margin-top:90px;padding:20px;}
.card{background:var(--panel);border-radius:12px;padding:18px;}
h2{color:var(--accent);}
.table{width:100%;border-collapse:collapse;}
.table th,.table td{padding:10px;border-bottom:1px solid #222;text-align:center;}
select,input{width:100%;padding:8px;border-radius:8px;background:#0f0f0f;color:#fff;border:1px solid #222;}
.btn{background:var(--accent);color:#000;padding:8px 14px;border-radius:8px;border:none;font-weight:600;cursor:pointer;}
.btn.danger{background:var(--danger);color:#fff;}
.alert{background:rgba(255,77,77,.15);padding:10px;border-radius:8px;margin-bottom:10px;}
</style>
</head>
<body>
<?php include 'layout/sidebar.php'; ?>
<?php include 'layout/header.php'; ?>

<div class="page-container">
<div class="card">
<h2>Add Sale Items (<?= htmlspecialchars($sale['invoice_no'] ?? $pendingSale['invoice_no']) ?>)</h2>

<?php foreach($errors as $e): ?>
  <div class="alert"><?= htmlspecialchars($e) ?></div>
<?php endforeach; ?>

<form method="POST">
<table class="table">
<thead>
<tr>
  <th>Product</th><th>Qty</th><th>Unit Price</th><th></th>
</tr>
</thead>
<tbody id="items-container">
<tr>
<td>
<select name="items[0][product_id]" onchange="setPrice(this)" required>
<option value="">-- Select Product --</option>
<?= $productOptionsHtml ?>
</select>
</td>
<td><input type="text" name="items[0][quantity]" required></td>
<td><input type="number" step="0.01" name="items[0][unit_price]" required></td>
<td><button type="button" class="btn danger" onclick="removeRow(this)"><i class="bi bi-trash"></i></button></td>
</tr>
</tbody>
</table>

<br>
<button type="button" class="btn" onclick="addRow()">Add Item</button>
<button type="submit" class="btn">Save Sale</button>
</form>
</div>
</div>

<script>
let rowCount = 1;
function addRow(){
  const c = document.getElementById('items-container');
  const r = document.createElement('tr');
  r.innerHTML = c.children[0].innerHTML.replace(/\[0\]/g, `[${rowCount}]`);
  c.appendChild(r);
  rowCount++;
}
function removeRow(b){ b.closest('tr').remove(); }
function setPrice(s){
  const p = s.selectedOptions[0]?.dataset.price;
  if(p) s.closest('tr').querySelector('input[type=number]').value = p;
}
</script>
<?php include 'layout/footer.php'; ?>
</body>
</html>
